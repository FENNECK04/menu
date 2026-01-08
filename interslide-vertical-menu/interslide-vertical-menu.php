<?php
/**
 * Plugin Name: Interslide Vertical Menu
 * Description: Adds a brut-style vertical menu inspired by brute.media.
 * Version: 1.0.0
 * Author: Interslide
 * Text Domain: interslide-vertical-menu
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IVM_VERSION', '1.0.0' );
define( 'IVM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IVM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once IVM_PLUGIN_DIR . 'includes/class-ivm-plugin.php';

function ivm_init_plugin() {
	return Interslide_Vertical_Menu_Plugin::get_instance();
}

ivm_init_plugin();
