<?php
/**
 * Plugin Name: BenriBot for WooCommerce
 * Plugin URI:  https://benribot.com
 * Description: Integrates the BenriBot chat widget into your WooCommerce store.
 * Version:     1.0.0
 * Author:      BenriBot
 * Author URI:  https://benribot.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: benribot-for-woocommerce
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
 * Renders the settings page HTML.
 */
function benribot_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php esc_html_e( 'BenriBot is your AI agent for eCommerce. Add your Client Key or embed code below to integrate the chat widget into your WooCommerce store.', 'benribot-for-woocommerce' ); ?></p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'benribot_settings' );
            do_settings_sections( 'benribot_settings' );
            submit_button( __( 'Save Settings', 'benribot-for-woocommerce' ) );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registers the settings and fields.
 */
function benribot_register_settings() {
    register_setting( 'benribot_settings', 'benribot_client_key' );
    register_setting( 'benribot_settings', 'benribot_embed_code' );

    add_settings_section(
        'benribot_general_section',
        '',
        null,
        'benribot_settings'
    );

    add_settings_field(
        'benribot_client_key',
        __( 'Client Key', 'benribot-for-woocommerce' ),
        'benribot_client_key_field_html',
        'benribot_settings',
        'benribot_general_section'
    );

    add_settings_field(
        'benribot_embed_code',
        __( 'Embed Code', 'benribot-for-woocommerce' ),
        'benribot_embed_code_field_html',
        'benribot_settings',
        'benribot_general_section'
    );
}
add_action( 'admin_init', 'benribot_register_settings' );

/**
 * Renders the Client Key input field.
 */
function benribot_client_key_field_html() {
    $client_key = get_option( 'benribot_client_key' );
    ?>
    <input type="text" name="benribot_client_key" value="<?php echo esc_attr( $client_key ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Paste your BenriBot Client Key', 'benribot-for-woocommerce' ); ?>">
    <?php
}

/**
 * Renders the Embed Code textarea.
 */
function benribot_embed_code_field_html() {
    $embed_code = get_option( 'benribot_embed_code' );
    ?>
    <textarea name="benribot_embed_code" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'Paste your full BenriBot embed code', 'benribot-for-woocommerce' ); ?>"><?php echo esc_textarea( $embed_code ); ?></textarea>
    <p class="description">
        <?php esc_html_e( 'You can get your Client Key or embed code from your BenriBot dashboard. If you enter both, the embed code will take priority.', 'benribot-for-woocommerce' ); ?>
    </p>
    <?php
}

/**
 * Injects the BenriBot script into the website's footer.
 */
function benribot_inject_script() {
    $embed_code = get_option( 'benribot_embed_code' );
    $client_key = get_option( 'benribot_client_key' );

    if ( ! empty( $embed_code ) ) {
        // Output the raw embed code. It's expected that the admin provides a trusted script.
        // Escaping this would break the script.
        echo $embed_code;
    } elseif ( ! empty( $client_key ) ) {
        // Generate the script with the client key.
        $script_url = 'https://cdn.benribot.com/v1/widget.js';
        printf(
            '<script async src="%s" data-client-key="%s"></script>',
            esc_url( $script_url ),
            esc_attr( $client_key )
        );
    }
}
add_action( 'wp_footer', 'benribot_inject_script' );
