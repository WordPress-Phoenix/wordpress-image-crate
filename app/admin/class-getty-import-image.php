<?php

namespace ImageCrate\Admin;

use ImageCrate\Includes\Getty_API_Helper as Getty;
use ImageCrate\Includes\Getty_Auth_Token;

/**
 * Import Class
 *
 * Handle returned data for image source.
 *
 * @version  2.0.0
 * @package  WP_Image_Crate
 * @author   justintucker
 */
final class Getty_Import_Image {

	public $directory;

	/**
	 * Import image from an url
	 *
	 * @param $download_url
	 * @param $filename
	 * @param $custom_directory
	 *
	 * @return bool|int|object
	 */
	public function image( $download_url, $filename, $custom_directory ) {
		$this->directory = 'getty';

		$file_array = [];
		$post_name = strtolower( $filename );

		// bail if
		if ( ! empty( $this->check_attachment_exists( $post_name ) )  ) {
			return $id_exists;
		}

		// place the images in a custom directory
		add_filter( 'upload_dir', array( &$this, 'set_upload_dir' ) );

		error_log( 'begin api call for fullsize url...' );
		$download_url = self::get_fullsize_delivery_url( $download_url );

		error_log( 'done, here\'s what I got: ' . var_export( $download_url, true ) );

		$file_array['tmp_name'] = $this->download( $download_url );

		error_log( 'ran download' );
		error_log( var_export( $file_array, true ) );

		if ( ! $file_array['tmp_name'] ) {
			return false;
		}

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_array['tmp_name'], $matches );

		if ( ! $matches ) {
			unlink( $file_array['tmp_name'] );
			return false;
		}

		$file_array['name'] = basename( $matches[0] );

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$api_image   = $file_array['name'];

		$image_type = pathinfo( $api_image );
		$file_name  = basename( $api_image, '.' . $image_type['extension'] );
		$file_array['name'] = str_replace( $file_name, $post_name, $api_image );

		error_log( '$file_array' );
		error_log( var_export( $file_array, true ) );

		// Do the validation and storage stuff
		$id = media_handle_sideload( $file_array, 0, null, ['post_name' => $filename ] );

		error_log( 'deleting temp file...' );
		$this->delete_file( $file_array['tmp_name'] );

		return is_wp_error( $id ) ? false : $id;
	}

	static function get_fullsize_delivery_url( $getty_download_endpoint_url ) {
		$user = wp_get_current_user();
		$dl_txt = 'Downloaded with WordPress Image Crate by ';
		$response = wp_remote_post(
			$getty_download_endpoint_url . '?auto_download=false',
			array(
				'headers' => Getty_Auth_Token::get_headers_auth_array(),
				'body' => array(
					'download_notes' => $dl_txt . $user->display_name . ' (' . $user->ID . ') at ' . current_time( 'mysql')
				)
			)
		);

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			error_log( 'successful fullsize url retrieval...' );
			$data = wp_remote_retrieve_body( $response );
			if ( ( is_array( $data ) || is_array( $data = json_decode( $data, true ) ) && isset( $data['uri'] ) ) ) {
				return $data['uri'];
			}
		}

		return false;
	}

	/**
	 * Download a file by its URL
	 *
	 * @param $getty_api_url
	 *
	 * @return bool|string
	 * @internal param string $id
	 *
	 */
	private function download( $getty_api_url ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( function_exists( 'wpcom_vip_download_image' ) ) {
			$file = wpcom_vip_download_image( $getty_api_url );
		} else {
			$file = download_url( $getty_api_url , 15 );
		}

		if ( is_wp_error( $file ) ) {
			return false;
		}

		// Added functionality to deal with image without extension
		$tmp_ext = pathinfo( $file, PATHINFO_EXTENSION );

		// Get the real image extension
		$file_ext = image_type_to_extension( exif_imagetype( $file ) );

		// Replace extension of basename file
		$new_file = basename( $file, ".$tmp_ext" ) . $file_ext;

		// Replace old file with new file in complete path location
		$new_file = str_replace( basename( $file ), $new_file, $file );

		// Rename from .tpm to actual file format
		rename( $file, $new_file );

		$file = $new_file;

		return $file;
	}

	/**
	 * Delete a file
	 *
	 * @param  string $filepath
	 *
	 * @return bool
	 */
	private function delete_file( $filepath ) {
		return is_readable( $filepath ) ? @unlink( $filepath ) : false;
	}

	/**
	 * Check if attachment exists
	 *
	 * @param        $post_name
	 * @param string $call_type
	 *
	 * @return int Post attachment id
	 */
	public function check_attachment_exists( $post_name, $call_type = 'remote' ) {
		// Switch to another blog to check post existence.
		if ( $call_type == 'remote' && is_multisite() ) {
			switch_to_blog( get_current_blog_id() );
		}

		global $wpdb;

		$attachment_id = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_name='%s';",
				$post_name
			)
		);

		if ( ! empty( $attachment_id[0] ) ) {
			$date          = date( 'Y-m-d h:i:s' );
			$update        = [
				'ID'                => $attachment_id[0],
				'post_date'         => $date,
				'post_date_gmt'     => get_gmt_from_date( $date, $format = 'Y-m-d H:i:s' ),
				'post_modified'     => $date,
				'post_modified_gmt' => get_gmt_from_date( $date, $format = 'Y-m-d H:i:s' ),
			];
			$attachment_id = wp_update_post( $update );
		} else {
			$attachment_id = 0;
		}

		if ( $call_type == 'remote' && is_multisite() ) {
			restore_current_blog();
		}

		return $attachment_id;
	}

	/**
	 * Temporarily change upload directory for downloading getty images
	 *
	 * @param   array $upload Filtered upload dir locations
	 *
	 * @return  array Filtered upload dir locations
	 */
	public function set_upload_dir( $upload ) {
		$upload['subdir']  = '/' . $this->directory . $upload['subdir'];
		$upload['basedir'] = WP_CONTENT_DIR . '/uploads';
		$upload['baseurl'] = content_url() . '/uploads';
		$upload['path']    = $upload['basedir'] . $upload['subdir'];
		$upload['url']     = $upload['baseurl'] . $upload['subdir'];

		return $upload;
	}
}