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

	/**
	 * Download an image given an url
	 */
	public function download() {

		//if ( ! isset( $_POST['filename'], $_POST['id'], $_POST['nonce'] ) ) {
		//	wp_send_json_error();
		//}

		$filename = sanitize_file_name( $_POST['filename'] );
		$id       = absint( $_POST['id'] );
		$filename = sprintf('%s-%s', $id, $filename);

		//check_ajax_referer( 'image_crate_download_' . $id, 'nonce' );

		/**
		 * Resize to max 2400 px wide 80% quality
		 * Documentation: https://github.com/asilvas/node-image-steam
		 */
		//$url = esc_url_raw( sprintf( '%s/%s/:/rs=w:2400/qt=q:80', \WPaaS\Plugin::config( 'imageApi.url' ), $filename ) );

		//$url = esc_url_raw( sprintf( 'http://www.usatodaysportsimages.com/api/download/?imageID=%s?auto_download=false', $id ) );

		$import   = new Image_Crate_Import();
		$image_id = $import->image( $id, $filename );

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