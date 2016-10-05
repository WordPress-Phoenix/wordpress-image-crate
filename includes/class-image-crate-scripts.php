<?php
/**
 * Image_Crate_Scripts Class
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Crate_Scripts {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'admin_print_styles', array( $this, 'alter_attachment_thumb_display'), PHP_INT_MAX);
	}

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