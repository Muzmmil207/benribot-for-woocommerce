<?php
/**
 * Plugin Name: BenriBot for WooCommerce
 * Plugin URI:  https://benribot.com
 * Description: Integrates the BenriBot chat widget into your WooCommerce store.
 * Version:     2.0.0
 * Author:      BenriBot
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: benribot-for-woocommerce
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'BENRIBOT_VERSION', '2.0.0' );
define( 'BENRIBOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Adds the BenriBot admin menu item.
 */
function benribot_add_admin_menu() {
    add_menu_page(
        __( 'BenriBot Settings', 'benribot-for-woocommerce' ),
        'BenriBot',
        'manage_options',
        'benribot-settings',
        'benribot_settings_page_html',
        'dashicons-format-chat'
    );
}
add_action( 'admin_menu', 'benribot_add_admin_menu' );

/**
 * Renders the settings page HTML with React app container.
 */
function benribot_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <div id="benribot-admin-app"></div>
    </div>
    <?php
}

/**
 * Enqueue admin scripts and styles.
 */
function benribot_enqueue_admin_scripts( $hook ) {
    // Only load on BenriBot settings page
    if ( 'toplevel_page_benribot-settings' !== $hook ) {
        return;
    }

    $plugin_url = plugin_dir_url( __FILE__ );
    $plugin_path = plugin_dir_path( __FILE__ );
    
    // Get asset dependencies
    $asset_file = include $plugin_path . 'build/index.asset.php';
    
    // Enqueue React admin app
    wp_enqueue_script(
        'benribot-admin',
        $plugin_url . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Enqueue admin styles
    wp_enqueue_style(
        'benribot-admin',
        $plugin_url . 'build/style-index.css',
        array(),
        $asset_file['version']
    );

    // Localize script with data
    wp_localize_script(
        'benribot-admin',
        'benribotAdmin',
        array(
            'apiNonce' => wp_create_nonce( 'wp_rest' ),
            'apiUrl'   => rest_url( 'benribot/v1/' ),
            'logoUrl'  => $plugin_url . 'assets/logo.svg',
        )
    );
}
add_action( 'admin_enqueue_scripts', 'benribot_enqueue_admin_scripts' );

/**
 * Register REST API routes.
 */
function benribot_register_rest_routes() {
    register_rest_route(
        'benribot/v1',
        '/status',
        array(
            'methods'             => 'GET',
            'callback'            => 'benribot_get_status',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            },
        )
    );

    register_rest_route(
        'benribot/v1',
        '/connect',
        array(
            'methods'             => 'POST',
            'callback'            => 'benribot_connect_account',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            },
        )
    );

    register_rest_route(
        'benribot/v1',
        '/widget-toggle',
        array(
            'methods'             => 'POST',
            'callback'            => 'benribot_toggle_widget',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            },
        )
    );
}
add_action( 'rest_api_init', 'benribot_register_rest_routes' );

/**
 * Get connection status.
 */
function benribot_get_status() {
    $connected = get_option( 'benribot_connected', false );
    $client_key = get_option( 'benribot_client_key', '' );
    $widget_embedded = get_option( 'benribot_widget_embedded', false );

    return array(
        'connected'       => ! empty( $client_key ) && $connected,
        'client_key'      => $client_key,
        'widget_embedded' => $widget_embedded,
    );
}

/**
 * Generate connection URL and redirect.
 */
function benribot_connect_account() {
    // Check if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return new WP_Error(
            'woocommerce_not_active',
            'WooCommerce is required for this plugin.',
            array( 'status' => 400 )
        );
    }

    $store_url = get_site_url();
    $store_name = get_bloginfo( 'name' );
    $user_email = wp_get_current_user()->user_email;
    
    // Generate consumer key and secret
    $consumer_data = benribot_generate_consumer_keys();
    if ( is_wp_error( $consumer_data ) ) {
        return $consumer_data;
    }

    // Generate state and signature
    $state = wp_generate_password( 32, false );
    update_option( 'benribot_oauth_state', $state );
    
    $signature_secret = get_option( 'benribot_secret_key', '' );
    if ( empty( $signature_secret ) ) {
        $signature_secret = wp_generate_password( 64, true, true );
        update_option( 'benribot_secret_key', $signature_secret );
    }

    // Build signature
    $signature_data = array(
        $store_url,
        $consumer_data['consumer_key'],
        $consumer_data['consumer_secret'],
        $user_email,
        $store_name,
        $state,
    );
    $signature_string = implode( '|', $signature_data );
    $signature = hash( 'sha256', $signature_string . $signature_secret );

    // Build redirect URL
    $redirect_url = add_query_arg(
        array(
            'store_url'       => rawurlencode( $store_url ),
            'consumer_key'    => rawurlencode( $consumer_data['consumer_key'] ),
            'consumer_secret' => rawurlencode( $consumer_data['consumer_secret'] ),
            'user_email'      => rawurlencode( $user_email ),
            'store_name'      => rawurlencode( $store_name ),
            'state'           => rawurlencode( $state ),
            'signature'       => $signature,
        ),
        'https://app.benribot.com/onboarding/woocommerce'
    );

    return array(
        'success'      => true,
        'redirect_url' => $redirect_url,
    );
}

/**
 * Generate WooCommerce consumer keys.
 */
function benribot_generate_consumer_keys() {
    // Check if keys already exist
    $existing_key = get_option( 'benribot_consumer_key' );
    $existing_secret = get_option( 'benribot_consumer_secret' );
    
    if ( ! empty( $existing_key ) && ! empty( $existing_secret ) ) {
        return array(
            'consumer_key'    => $existing_key,
            'consumer_secret' => $existing_secret,
        );
    }

    // Generate new keys
    $description = 'BenriBot Integration - ' . get_bloginfo( 'name' );
    $user_id = get_current_user_id();
    
    // Create consumer key using WooCommerce REST API
    $consumer_key    = 'ck_' . wc_rand_hash();
    $consumer_secret = 'cs_' . wc_rand_hash();
    
    // Save keys to options
    update_option( 'benribot_consumer_key', $consumer_key );
    update_option( 'benribot_consumer_secret', $consumer_secret );

    return array(
        'consumer_key'    => $consumer_key,
        'consumer_secret' => $consumer_secret,
    );
}

/**
 * Toggle widget embed status.
 */
function benribot_toggle_widget( $request ) {
    $params = $request->get_json_params();
    $enabled = isset( $params['enabled'] ) ? (bool) $params['enabled'] : false;

    update_option( 'benribot_widget_embedded', $enabled );

    return array(
        'success' => true,
        'enabled' => $enabled,
    );
}

/**
 * Enqueues the BenriBot script on frontend if enabled.
 */
function benribot_inject_script() {
    $client_key = get_option( 'benribot_client_key' );
    $widget_embedded = get_option( 'benribot_widget_embedded', false );

    if ( empty( $client_key ) || ! $widget_embedded ) {
        return;
    }

    // Enqueue the BenriBot widget script with the client key.
    wp_enqueue_script(
        'benribot-widget',
        'https://cdn.benribot.com/v1/widget.js',
        array(),
        BENRIBOT_VERSION,
        true
    );
    
    // Localize the script with the client key data.
    wp_localize_script(
        'benribot-widget',
        'benribotConfig',
        array(
            'clientKey' => $client_key,
        )
    );
    
    // Add the data attribute to the script tag.
    add_filter( 'script_loader_tag', 'benribot_add_client_key_attribute', 10, 3 );
}
add_action( 'wp_footer', 'benribot_inject_script' );

/**
 * Adds the client key data attribute to the BenriBot script tag.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source.
 * @return string Modified script tag.
 */
function benribot_add_client_key_attribute( $tag, $handle, $src ) {
    if ( 'benribot-widget' === $handle ) {
        $client_key = get_option( 'benribot_client_key' );
        if ( ! empty( $client_key ) ) {
            $tag = str_replace( ' src=', ' async data-client-key="' . esc_attr( $client_key ) . '" src=', $tag );
        }
    }
    return $tag;
}
