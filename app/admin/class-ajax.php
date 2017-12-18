<?php

namespace ImageCrate\Admin;


/**
 * Ajax Class
 *
 * Handles ajax calls for getting image data and downloading individual images.
 *
 * @version  2.0.0
 * @package  Image_Crate
 * @author   justintucker
 */
class Ajax {

	/**
	 * Holds api object for making data calls.
	 *
	 * @var object
	 */
	private $api = null;

	private $getty_search;
	private $getty_downloader;

	/**
	 * Ajax constructor.
	 *
	 * @param $search_api \ImageCrate\Admin\Getty_Images_Search
	 * @param $download_single_api \ImageCrate\Admin\Getty_Import_Image
	 */
	public function __construct( $search_api, $download_single_api ) {
		$this->getty_search = $search_api;
		$this->getty_downloader = $download_single_api;

		add_action( 'wp_ajax_image_crate_get', array( $this, 'get' ) );
		add_action( 'wp_ajax_image_crate_download', array( $this, 'download') );
	}

	/**
	 * Get images and send them to the media modal.
	 */
	public function get() {
		check_ajax_referer( 'image_crate' );

		$search_term = isset( $_REQUEST['query']['search'] ) ? $_REQUEST['query']['search'] : '';
		$page = isset( $_REQUEST['query']['paged'] ) ? $_REQUEST['query']['paged'] : 1;
		$per_page = isset( $_POST['query']['posts_per_page'] ) ? absint( $_POST['query']['posts_per_page'] ) : 40;
		$page = ( $page - 1 ) * $per_page;

		$images = $this->getty_search->fetch( $search_term, $page, $per_page );

		if ( empty( $images ) ) {
			wp_send_json_success( [] );
		}

		$images = $this->getty_search->prepare_attachments( $images );

		$images = array_filter( $images );

		return wp_send_json_success( $images );
	}

	/**
	 * Download an image given an Getty Image ID
	 */
	public function download() {
		check_ajax_referer( 'image_crate' );

		$filename = sanitize_file_name( $_POST['filename'] );
		$download_url = esc_url_raw( $_POST['download_uri'] );

		$dir = $this->api->directory;
		$image_id = $this->getty_downloader->image( $download_url, $filename, $dir );

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
