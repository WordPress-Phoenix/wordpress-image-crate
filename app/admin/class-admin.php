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
		add_action( 'wp_enqueue_media', array( get_called_class(), 'enqueue_scripts' ) );
		add_action( 'print_media_templates', array( get_called_class(), 'no_results_template' ) );
		add_action( 'admin_print_styles', array( get_called_class(), 'alter_attachment_thumb_display' ) );
		add_filter( 'plugin_action_links_wordpress-image-crate/image-crate.php', array(
			get_called_class(),
			'add_action_links'
		) );
	}

	/**
	 * Enqueue custom media modal scripts
	 */
	public static function enqueue_scripts() {
		if ( wp_doing_ajax() || ! wp_script_is( 'media-views', 'enqueued' ) ) {
			if ( ! is_customize_preview() ) {
				return;
			}
		}


		$suffix = SCRIPT_DEBUG ? '' : '.min';
		wp_register_script(
			'image-crate',
			plugins_url( '/wordpress-image-crate/app/assets/js/image-crate-admin' . $suffix . '.js' ),
			array( 'media-views' ),
			rand( 0, 10000000 ),
			true
		);

		wp_enqueue_script( 'image-crate' );

		wp_localize_script(
			'image-crate',
			'imagecrate', apply_filters( 'image_crate_controller_title', array(
				'page_title'     => __( 'Getty', 'image-crate' ),
				'default_search' => Api::get_default_query(),
				'nonce'          => wp_create_nonce( 'image_crate' )
			) )
		);

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
	 * Add settings link to plugin list page
	 *
	 * @param $links Current plugin links
	 *
	 * @return array Plugin menu data
	 */
	public static function add_action_links( $links ) {
		// TODO: Update to multisite / vs single-site option url
		$links[] = '<a href="' . admin_url( 'network/settings.php?page=image-provider-settings#general' ) . '">Settings</a>';

		return $links;
	}
}
