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

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}

		$search_term = isset( $_POST['query']['search'] ) ? $_POST['query']['search'] : false;
		$page = isset( $_POST['query']['paged'] ) ? $_POST['query']['paged'] : 1;
		$per_page = isset( $_POST['query']['posts_per_page'] ) ? absint( $_POST['query']['posts_per_page'] ) : 20;

		//$search_term = 'broncos';

		if ( false == $search_term ) {
			wp_send_json_error();
		}

		$images = $this->api->fetch( $search_term, 41, $page );

		if ( empty( $images ) ) {
			wp_send_json_success( [] );
		}

		$total       = $images['total'];
		$total_pages = ceil( $total / $per_page );
		$page        = max( $page, 1 );
		$page        = min( $page, $total_pages );
		$offset      = ( $page - 1 ) * $per_page;

		//$images = array_splice( $images['items'], $offset, $per_page );
		//$images = array_map( [ $this, 'prepare_attachement_for_js' ], $images );
		$images = $this->api->prepare_attachments( $images['items'] );
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