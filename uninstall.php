<?php
/**
 * Uninstall cleanup for BenriBot for WooCommerce
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// List of options to remove.
$options_to_delete = array(
	'benribot_client_key',
	'benribot_widget_embedded',
	'benribot_connected',
	'benribot_oauth_state',
	'benribot_secret_key',
	'benribot_consumer_key',
	'benribot_consumer_secret',
	'benribot_wc_api_key_id',
);

foreach ( $options_to_delete as $option_name ) {
	delete_option( $option_name );
}


