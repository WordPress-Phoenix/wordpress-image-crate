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
		add_action( 'print_media_templates', array( $this, 'no_results_template') );
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
	public function enqueue_scripts() {
		if ( ! wp_script_is( 'media-views', 'enqueued' ) ) {
			if ( ! is_customize_preview() ) {
				return;
			}
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'image-crate', IC_URL . "/assets/js/image-crate-admin{$suffix}.js", array('media-views'), '0.1.0', true );

		wp_localize_script(
			'image-crate',
			'imagecrate', apply_filters( 'image_crate_controller_title', array(
				'page_title'     => __( 'Image Crate', 'image-crate' ),
				'default_search' => Image_Crate_Api::get_default_query(),
				'nonce'          => wp_create_nonce( 'image_crate' )
			) )
		);

		wp_enqueue_script( 'image-crate' );
	}

	/**
	 * Append custom to display no results
	 */
	public function no_results_template() {
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
