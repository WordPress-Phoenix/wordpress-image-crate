<?php

namespace ImageCrate\Admin;


/**
 * Admin Class
 *
 * Loads setting settings and scripts.
 *
 * @version  2.0.0
 * @package  Image_Crate
 * @author   justintucker
 */
class Admin {

	/**
	 * Run Hooks
	 */
	public static function init() {
		add_action( 'init', array( get_called_class(), 'register_fields' ) );
		add_filter( 'plugin_action_links_wordpress-image-crate/image-crate.php', array( get_called_class(), 'add_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'enqueue_scripts' ) );
		add_action( 'print_media_templates', array( get_called_class(), 'no_results_template' ) );
		add_action( 'wp_enqueue_scripts', array( get_called_class(), 'enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( get_called_class(), 'alter_attachment_thumb_display' ) );
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

	/**
	 * Adjust thumbnail preview size in display modal.
	 */
	public static function alter_attachment_thumb_display() {
		?>
		<style>
			.image-crate .attachment-details .thumbnail,
			.image-crate .attachment-details .thumbnail img {
				max-width: unset;
				max-height: unset;
				width: 100%;
				height: auto;
			}

			/* cheap way to fields without replicating an entire js template*/
			.image-crate .attachment-details label[data-setting=url],
			.image-crate .attachment-details label[data-setting=alt] {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue custom media modal scripts
	 */
	public static function enqueue_scripts() {
		if ( ! wp_script_is( 'media-views', 'enqueued' ) ) {
			if ( ! is_customize_preview() ) {
				return;
			}
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'image-crate',
			plugins_url( '/wordpress-image-crate/app/assets/js/image-crate-admin' . $suffix . '.js' ),
			array( 'media-views' ), '0.1.0', true );

		wp_localize_script(
			'image-crate',
			'imagecrate', apply_filters( 'image_crate_controller_title', array(
				'page_title'     => __( 'Image Crate', 'image-crate' ),
				'default_search' => Api::get_default_query(),
				'nonce'          => wp_create_nonce( 'image_crate' )
			) )
		);

		wp_enqueue_script( 'image-crate' );
	}

	/**
	 * Append custom to display no results
	 */
	public static function no_results_template() {
		?>
		<script type="text/html" id="tmpl-image-crate-no-results">
			<# var messageClass = data.message ? 'has-upload-message' : 'no-upload-message'; #>
			<div class="uploader-inline-content {{ messageClass }}">
				<# if ( data.message ) { #>
					<h2 class="upload-message">{{ data.message }}</h2>
				<# } #>

				<div class="upload-ui">
					<h2 class="upload-instructions drop-instructions"><?php _e( 'Please search for a different term.' ); ?></h2>
				</div>
			</div>
		</script>
		<?php
	}
}