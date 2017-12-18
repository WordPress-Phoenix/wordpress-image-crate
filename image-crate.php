<?php
/**
 * Plugin Name: Image Crate
 * Plugin URI: https://github.com/WordPress-Phoenix/wordpress-image-crate
 * Description: Add image providers to the WordPress media modal.
 * Author: justintucker
 * Version: 2.0.0
 * Author URI: http://github.com/justintucker
 * License: GPL V2
 * Text Domain: image-crate
 *
 * GitHub Plugin URI: https://github.com/WordPress-Phoenix/wordpress-image-crate
 * GitHub Branch: master
 *
 * @package  WP_Image_Crate
 * @category plugin
 * @author   justintucker
 * @internal Plugin derived from https://github.com/WordPress-Phoenix/abstract-plugin-base
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Create plugin instance on plugins_loaded action to maximize flexibility of wp hooks and filters system.
//require_once __DIR__ . '/vendor/autoload.php';

// manually include composer dependencies for safety
if ( ! class_exists( 'WPOP\\V_2_8') ) {
	require_once __DIR__ . '/vendor/wordpress-phoenix/wordpress-options-builder-class/wordpress-phoenix-options-panel.php';
}
if ( ! class_exists( 'WPAZ_Plugin_Base\\V_2_5\\Abstract_Plugin') ) {
	require_once __DIR__ . '/vendor/wordpress-phoenix/abstract-plugin-base/src/abstract-plugin.php';
}
// locate plugin
require_once __DIR__ . '/app/class-plugin.php';

ImageCrate\Plugin::run( __FILE__ );