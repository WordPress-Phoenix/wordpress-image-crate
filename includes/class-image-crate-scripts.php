<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image_Crate_Scripts Class
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */
class Image_Crate_Scripts {

	/**
	 * Image_Crate_Scripts constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'admin_print_styles', array( $this, 'alter_attachment_thumb_display'), PHP_INT_MAX);
	}

	/**
	 * Adjust thumbnail preview size in display modal.
	 */
	public function alter_attachment_thumb_display () {
		?>
		<style>
			.image-crate .attachment-details .thumbnail,
			.image-crate .attachment-details .thumbnail img{
				max-width: unset;
				max-height: unset;
				width: 100%;
				height: auto;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue custom media modal scripts
	 */
	public function enqueue_scripts() {
		if ( ! wp_script_is( 'media-views', 'enqueued' ) ) {
			if ( ! is_customize_preview() ) {
				return;
			}
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'image-implementor', IC_URL . "/assets/js/image-crate-admin{$suffix}.js", array('media-views'), '0.1.0', true );

		wp_localize_script(
			'image-crate',
			'crate', array(
				'page_title' => __( 'Image Crate', 'image-crate' )
			)
		);
	}
}
