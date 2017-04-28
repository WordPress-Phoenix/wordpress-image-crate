<?php
/**
 * Plugin Name: Image Crate
 * Plugin URI: https://github.com/WordPress-Phoenix/wordpress-image-crate
 * Description: Add image providers to the WordPress media modal.
 * Author: justintucker
 * Version: 1.1.1
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
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/class-image-crate.php';
add_action( 'plugins_loaded', array('ImageCrate\\Init', 'run') );