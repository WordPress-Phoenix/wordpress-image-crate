<?php

namespace ImageCrate\Admin;


/**
 * Scripts Class
 *
 * Enqueues backbone scripts that make the magic happen. Provides template adjustments for the media modal.
 *
 * @version  3.0.0
 * @package  WP_Image_Crate
 * @author   justintucker
 */
class Scripts {
	/**
	 * Image_Crate_Scripts constructor.
	 */
	public static function setup() {
		add_action( 'wp_enqueue_scripts', array( get_called_class() , 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', array( get_called_class() , 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'admin_print_styles', array( get_called_class() , 'alter_attachment_thumb_display'), PHP_INT_MAX);
	}

	/**
	 * Adjust thumbnail preview size in display modal.
	 */
	public static function alter_attachment_thumb_display () {
		?>
		<style>
			.image-crate .attachment-details .thumbnail,
			.image-crate .attachment-details .thumbnail img{
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
		wp_register_script(
			'image-crate',
			plugins_url('/wordpress-image-crate/app/assets/js/image-crate' . $suffix . '.js'),
			array('media-views'),
			IMAGE_CRATE_VERSION,
			true
		);

		wp_register_style(
			'image-crate',
			plugins_url('/wordpress-image-crate/app/assets/css/image-crate.css'),
			[],
			IMAGE_CRATE_VERSION
		);

		wp_localize_script(
			'image-crate',
			'imagecrate', apply_filters( 'image_crate_controller_title', array(
				'page_title'     => __( 'Image Crate', 'image-crate' ),
				//'default_search' => Api::get_default_query(),
				'nonce'          => wp_create_nonce( 'image_crate' )
			) )
		);

		wp_enqueue_script( 'image-crate' );
		wp_enqueue_style( 'image-crate' );
	}

}
