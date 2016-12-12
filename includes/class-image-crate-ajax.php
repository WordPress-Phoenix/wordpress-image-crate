<?php
/**
 * Image_Crate_Ajax Class
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Crate_Ajax {

	private $api = null;

	function __construct( $api ) {
		$this->api = $api;

		add_action( 'wp_ajax_image_crate_get', array( $this, 'get' ) );
		add_action( 'wp_ajax_image_crate_download', array( $this, 'download') );
	}

	public function get() {

		// todo: need to add a filter here. fansided users are not allowed to upload files
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}

		$search_term = isset( $_REQUEST['query']['search'] ) ? $_REQUEST['query']['search'] : false;
		$page = isset( $_REQUEST['query']['paged'] ) ? $_REQUEST['query']['paged'] : 1;
		$per_page = isset( $_POST['query']['posts_per_page'] ) ? absint( $_POST['query']['posts_per_page'] ) : 40;
		$page = ( $page * $per_page ) + 1;

		if ( false == $search_term ) {
			wp_send_json_error();
		}

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

		//if ( ! isset( $_POST['filename'], $_POST['id'], $_POST['nonce'] ) ) {
		//	wp_send_json_error();
		//}

		$filename = sanitize_file_name( $_POST['filename'] );
		$service_image_id = absint( $_POST['id'] );

		//check_ajax_referer( 'image_crate_download_' . $id, 'nonce' );

		$import   = new Image_Crate_Import();
		$dir = $this->api->directory;
		$image_id = $import->image( $service_image_id, $filename, $dir );

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