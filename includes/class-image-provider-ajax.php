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

		add_action( 'wp_ajax_image_implementor_get', array( $this, 'get' ) );
		//add_action( 'wp_ajax_wpaas_stock_photos_download', [ $this, 'download' ] );
	}

	public function get() {

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}

		$search_term = isset( $_POST['query']['search_term'] ) ? $_POST['query']['search_term'] : false;
		$page = isset( $_POST['query']['paged'] ) ? $_POST['query']['paged'] : 1;

		$search_term = 'marvel';

		if ( false == $search_term ) {
			wp_send_json_error();
		}

		$images = $this->api->fetch( $search_term, $page );

		if ( empty( $images ) ) {
			wp_send_json_success( [] );
		}

		$images = $this->api->prepare_attachments( $images );

		return wp_send_json_success( $images );

	}

}