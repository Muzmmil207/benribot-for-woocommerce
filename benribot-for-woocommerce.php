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

    register_rest_route(
        'benribot/v1',
        '/callback',
        array(
            'methods'             => 'GET',
            'callback'            => 'benribot_callback_handler',
            'permission_callback' => '__return_true', // Public endpoint for redirect
        )
    );
}
add_action( 'rest_api_init', 'benribot_register_rest_routes' );

/**
 * Get connection status from BenriBot API.
 */
function benribot_get_status() {
    $widget_embedded = get_option( 'benribot_widget_embedded', false );
    
    // Get stored client key
    $client_key = get_option( 'benribot_client_key', '' );
    
    if ( empty( $client_key ) ) {
        return array(
            'connected'       => false,
            'widget_embedded' => $widget_embedded,
        );
    }
    
    // Fetch connection status from BenriBot API
    $api_url = 'https://app.benribot.com/api/v1/wordpress/status?client_key=' . $client_key;
    
    $response = wp_remote_get($api_url);
    
    // Handle error or API failure
    if ( is_wp_error( $response ) ) {
        return array(
            'connected'       => false,
            'widget_embedded' => $widget_embedded,
        );
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode( $response_body, true );
    
    if ( $response_code !== 200 || ! $data ) {
        return array(
            'connected'       => false,
            'widget_embedded' => $widget_embedded,
        );
    }
    
    // Update local storage with API response
    if ( isset( $data['connected'] ) && $data['connected'] ) {
        update_option( 'benribot_connected', true );
    } else {
        update_option( 'benribot_connected', false );
    }
    
    return array(
        'connected'       => isset( $data['connected'] ) ? $data['connected'] : false,
        'widget_embedded' => $widget_embedded,
    );
}

/**
 * Handle callback from BenriBot onboarding.
 */
function benribot_callback_handler( $request ) {
    $client_key_raw   = $request->get_param( 'client_key' );
    $returned_state   = $request->get_param( 'state' );

    // Sanitize incoming values
    $client_key   = is_string( $client_key_raw ) ? sanitize_text_field( wp_unslash( $client_key_raw ) ) : '';
    $returned_state = is_string( $returned_state ) ? sanitize_text_field( wp_unslash( $returned_state ) ) : '';

	$expected_state = get_option( 'benribot_oauth_state', '' );
	if ( empty( $returned_state ) || $returned_state !== $expected_state ) {
		return new WP_Error( 'invalid_state', 'State mismatch.', array( 'status' => 400 ) );
	}
	delete_option( 'benribot_oauth_state' );

    if ( empty( $client_key ) ) {
        return new WP_Error(
            'missing_client_key',
            'Client key is required',
            array( 'status' => 400 )
        );
    }
    
    // Store the client key
    update_option( 'benribot_connected', true );
    
    if ( ! empty( $client_key ) ) {
        update_option( 'benribot_client_key', $client_key );
    }
    
    // Enable widget by default after connection
    update_option( 'benribot_widget_embedded', true );
    
    // Redirect to admin page
    wp_redirect( admin_url( 'admin.php?page=benribot-settings&connected=1' ) );
    exit;
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

    // Build callback URL
    $callback_url = rest_url( 'benribot/v1/callback' );
    
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
            'callback_url'    => rawurlencode( $callback_url ),
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
    // If keys already exist, reuse them
    $existing_key    = get_option( 'benribot_consumer_key' );
    $existing_secret = get_option( 'benribot_consumer_secret' );
    if ( ! empty( $existing_key ) && ! empty( $existing_secret ) ) {
        return array(
            'consumer_key'    => $existing_key,
            'consumer_secret' => $existing_secret,
        );
    }

    // Ensure WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return new WP_Error( 'woocommerce_not_active', __( 'WooCommerce is required to create API keys.', 'benribot-for-woocommerce' ), array( 'status' => 400 ) );
    }

    $description = 'BenriBot Integration - ' . get_bloginfo( 'name' );
    $user_id     = get_current_user_id();

    // Try to include WooCommerce API helpers if not already loaded
    if ( ! function_exists( 'wc_create_api_key' ) ) {
        $api_functions = WP_PLUGIN_DIR . '/woocommerce/includes/wc-api-functions.php';
        if ( file_exists( $api_functions ) ) {
            include_once $api_functions;
        }
        $admin_functions = WP_PLUGIN_DIR . '/woocommerce/includes/admin/wc-admin-functions.php';
        if ( file_exists( $admin_functions ) ) {
            include_once $admin_functions;
        }
    }

    // Generate consumer key/secret
    if ( function_exists( 'wc_rand_hash' ) ) {
        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();
    } else {
        $consumer_key    = 'ck_' . wp_generate_password( 12, false );
        $consumer_secret = 'cs_' . wp_generate_password( 32, false );
    }

    // Preferred: use WooCommerce API to create key
    if ( function_exists( 'wc_create_api_key' ) ) {
        $key_id = wc_create_api_key( $user_id, $description, 'read', $consumer_key, $consumer_secret );

        if ( ! $key_id || is_wp_error( $key_id ) ) {
            if ( is_wp_error( $key_id ) ) {
                return $key_id;
            }
            return new WP_Error( 'api-key-error', __( 'Failed to create WooCommerce API key.', 'benribot-for-woocommerce' ), array( 'status' => 500 ) );
        }

        // Persist for reuse
        update_option( 'benribot_wc_api_key_id', $key_id, false );
        update_option( 'benribot_consumer_key', $consumer_key );
        update_option( 'benribot_consumer_secret', $consumer_secret );

        return array(
            'consumer_key'    => $consumer_key,
            'consumer_secret' => $consumer_secret,
        );
    }

    // Fallback: insert directly into WooCommerce API keys table
    global $wpdb;
    $table = $wpdb->prefix . 'woocommerce_api_keys';

    // Hash function for consumer key (matches Woo behavior when available)
    if ( function_exists( 'wc_api_hash' ) ) {
        $hashed_key = wc_api_hash( $consumer_key );
    } else {
        $hashed_key = hash( 'sha256', $consumer_key );
    }

    // Direct database insertion is used here only if wc_create_api_key is unavailable (typical in pre-3.5 WooCommerce or edge cases).
    // All fields here are sanitized and match the schema for the wp_woocommerce_api_keys table.
    // See: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ (Direct DB usage allowed in rare necessary cases for custom/plugin tables).
    $inserted = $wpdb->insert(
        $table,
        array(
            'user_id'         => $user_id,
            'description'     => $description,
            'permissions'     => 'read',
            'consumer_key'    => $hashed_key,
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr( $consumer_key, -7 ),
            'last_access'     => null,
        ),
        array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    if ( ! $inserted ) {
        return new WP_Error( 'api-key-error', __( 'Failed to create WooCommerce API key in database.', 'benribot-for-woocommerce' ), array( 'status' => 500 ) );
    }

    $key_id = $wpdb->insert_id;
    update_option( 'benribot_wc_api_key_id', $key_id, false );
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

/**
 * Add suggested privacy policy text to the site's Privacy Policy guide.
 */
function benribot_add_privacy_policy_content() {
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
        return;
    }

    $content = __(
        'This site uses the BenriBot chat widget to assist visitors. The widget is loaded from BenriBot\'s CDN and may process chat messages entered by visitors. No WordPress user account data is sent by this plugin.\n\n\nData shared with BenriBot:\n- Chat messages entered in the widget\n- Technical details required to operate the widget\n\nProvider details:\n- Service: BenriBot Chat Widget\n- CDN: https://cdn.benribot.com/v1/widget.js\n- Terms: https://benribot.com/terms-of-service\n- Privacy: https://benribot.com/privacy-policy',
        'benribot-for-woocommerce'
    );

    // Allow basic formatting and links.
    $allowed = array(
        'a'      => array( 'href' => array(), 'rel' => array(), 'target' => array() ),
        'strong' => array(),
        'em'     => array(),
        'p'      => array(),
        'ul'     => array(),
        'li'     => array(),
        'br'     => array(),
    );

    wp_add_privacy_policy_content(
        __( 'BenriBot for WooCommerce', 'benribot-for-woocommerce' ),
        wp_kses( nl2br( $content ), $allowed )
    );
}
add_action( 'admin_init', 'benribot_add_privacy_policy_content' );
