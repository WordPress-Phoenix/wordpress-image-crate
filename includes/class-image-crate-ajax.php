<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Image_Crate_Ajax Class
 *
 * AJAX Event Handler.
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */
class Image_Crate_Ajax {

	/**
	 * Holds api object for making data calls.
	 *
	 * @var object
	 */
	private $api = null;

	/**
	 * Setup api class connections
	 *
	 * @param $api
	 */
	public function __construct( $api ) {
		$this->api = $api;

		add_action( 'wp_ajax_image_crate_get', array( $this, 'get' ) );
		add_action( 'wp_ajax_image_crate_download', array( $this, 'download') );
	}

	/**
	 * Get images and send them to the media modal.
	 */
	public function get() {
		check_ajax_referer('image_crate');

		$search_term = isset( $_REQUEST['query']['search'] ) ? $_REQUEST['query']['search'] : '';
		$page = isset( $_REQUEST['query']['paged'] ) ? $_REQUEST['query']['paged'] : 1;
		$per_page = isset( $_POST['query']['posts_per_page'] ) ? absint( $_POST['query']['posts_per_page'] ) : 40;
		$page = ( $page - 1 ) * $per_page;

		$images = $this->api->fetch( $search_term, $page, $per_page );

		if ( empty( $images ) ) {
			wp_send_json_success( [] );
		}

		$images = $this->api->prepare_attachments( $images );
		$images = array_filter( $images );

		return wp_send_json_success( $images );
	}

	/**
	 * Download an image given an url
	 */
	public function download() {
		check_ajax_referer( 'image_crate' );

		$filename = sanitize_file_name( $_POST['filename'] );
		$download_url = esc_url_raw( $_POST['download_uri'] );

		$import = new Image_Crate_Import();
		$dir = $this->api->directory;
		$image_id = $import->image( $download_url, $filename, $dir );

		if ( ! $image_id ) {
			wp_send_json_error();
		}

		$attachment = wp_prepare_attachment_for_js( $image_id );

		if ( ! $attachment ) {
			wp_send_json_error();
		}

		wp_send_json_success( $attachment );
	}
}