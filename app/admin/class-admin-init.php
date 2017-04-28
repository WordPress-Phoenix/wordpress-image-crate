<?php

namespace ImageCrate\Admin;


/**
 * Admin Class
 *
 * Loads setting settings and scripts.
 *
 * @version  0.1.1
 * @package  Image_Crate
 * @author   justintucker
 */
class Admin_Init {

	/**
	 * Run Hooks
	 */
	public static function run() {
		add_action( 'admin_init', array( get_called_class(), 'register_fields' ) );
		add_filter( 'plugin_action_links_wordpress-image-crate/image-crate.php', array( get_called_class(), 'add_action_links' ) );
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