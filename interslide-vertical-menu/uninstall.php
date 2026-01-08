<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'ivm_settings', array() );
if ( isset( $settings['cleanup_on_uninstall'] ) && 1 === (int) $settings['cleanup_on_uninstall'] ) {
	delete_option( 'ivm_settings' );
}
