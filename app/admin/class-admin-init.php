<?php

namespace ImageCrate\Admin;

use ImageCrate\Admin\Providers\Provider_Getty_Images;

/**
 * Admin Class
 *
 * Loads setting settings and scripts.
 *
 * @version  2.0.0
 * @package  Image_Crate
 * @author   justintucker
 */
class Admin_Init {

	/**
	 * Run Hooks
	 */
	public static function run() {
		Scripts::setup();
		$usage_tracking = new Usage_Tracking();

		add_action( 'admin_init', array( get_called_class(), 'register_fields' ) );
		add_filter( 'plugin_action_links_wordpress-image-crate/image-crate.php', array( get_called_class(), 'add_action_links' ) );
		add_action( 'wp_ajax_image_crate_get', array( get_called_class(), 'get' ) );
		add_action( 'wp_ajax_image_crate_download', array( get_called_class(), 'download' ) );
		add_action( 'save_post', [ $usage_tracking, 'track' ], 10, 2 );

	}

	public static function get() {
		check_ajax_referer( 'image_crate' );

		$query = $_REQUEST['query'];

		// Build the provider FQCN.
		$provider = $_REQUEST['query']['provider'];
		$provider = str_replace( '-', ' ', $provider );
		$provider = ucwords( $provider );
		$provider = str_replace( ' ', '_', $provider );
		$provider = '\ImageCrate\Admin\Providers\Provider_' . $provider;

		$provider = new $provider;
		$images = $provider->fetch( $query );

		return wp_send_json_success( $images );
	}

	public static function download() {
		check_ajax_referer( 'image_crate' );

		$query = $_REQUEST['query'];

		// This could be cleaner
		$provider = $_REQUEST['query']['provider'];
		$provider = str_replace( '-', ' ', $provider );
		$provider = ucwords( $provider );
		$provider = str_replace( ' ', '_', $provider );

		$provider = '\ImageCrate\Admin\Providers\Provider_' . $provider;
		$provider = new $provider;
		$download = $provider->download( $query );

		return wp_send_json_success( $download );
	}

	/**
	 * Add settings link to plugin list page
	 *
	 * @param $links Current plugin links
	 *
	 * @return array Plugin menu data
	 */
	public static function add_action_links( $links ) {
		$links[] = '<a href="' . admin_url( 'options-general.php#image_crate_default_search_term' ) . '">Settings</a>';

		return $links;
	}

	/**
	 * Register and output field for setting a default search value
	 */
	public static function register_fields() {
		register_setting( 'general', 'image_crate_default_search', 'esc_attr' );

		add_settings_field(
			'image_crate_default_search_term',
			'<label for="image_crate_default_search_term">' . __( 'Image Crate Default Term', 'image_crate_default_search' ) . '</label>',
			array( get_called_class(), 'fields_html' ),
			'general'
		);
	}

	/**
	 * Output form field markup
	 */
	public static function fields_html() {
		$value = get_option( 'image_crate_default_search', '' );
		echo '<input type="text" id="image_crate_default_search_term" name="image_crate_default_search" value="' . esc_attr( $value ) . '" />';
	}

}