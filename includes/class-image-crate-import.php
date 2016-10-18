<?php
/**
 * Image_Crate_Import Class
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Image_Crate_Import {

	/**
	 * Custom Directory for image storage
	 * @var string
	 */
	//private $directory;

	/**
	 * Get this party started
	 *
	 * todo: maybe fix collection not loading, unless attachments have been loaded
	 */
	public function __construct() {
		/*
		 * Update image source urls for images uploaded to the getty api
		 */
		//add_filter( 'wp_prepare_attachment_for_js', array( $this, 'update_media_modal_file_refs' ), 99, 1 );
		//add_filter( 'wp_get_attachment_image_src', array( $this, 'set_image_path' ) );
		//add_filter( 'wp_calculate_image_srcset', array( $this, 'update_scrset_attr' ), 10, 1 );
		//add_filter( 'image_send_to_editor', array( $this, 'send_to_editor' ), 10, 1 );
	}

	/**
	 * Import image from an url
	 *
	 * @param $service_image_id
	 * @param $filename
	 *
	 * @return bool|int|object
	 */
	public function image( $service_image_id, $filename, $custom_directory ) {

		if ( $custom_directory ) {
		    $this->directory = $custom_directory;
		}

		$file_array = [];

		$post_name = strtolower( $filename );
		$id_exists = $this->check_attachment( $post_name );


		// filename will determine if download will occur
		if ( $id_exists > 0  ) {
		    return $id_exists;
		}

		// place the images in a custom directory
		add_filter( 'upload_dir', array( &$this, 'set_upload_dir' ) );

		$file_array['tmp_name'] = $this->download( $service_image_id );

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
		$post_name = sprintf( '%s-%s', $service_image_id, $post_name );
		$file_array['name'] = str_replace( $file_name, $post_name, $api_image );

		// Do the validation and storage stuff
		$id = media_handle_sideload( $file_array, 0 );

		$this->delete_file( $file_array['tmp_name'] );

		return is_wp_error( $id ) ? false : $id;

	}

	/**
	 * Download a file by its URL
	 *
	 * @param  string $id
	 *
	 * @return bool|string
	 */
	private function download( $id ) {

		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$baseUrl              = 'http://api.usatodaysportsimages.com/api/download/';
		$consumerKey          = USAT_API_KEY;
		$consumerSecret       = USAT_API_SECRET;
		$oauthTimestamp       = time();
		$nonce                = md5( mt_rand() );
		$oauthSignatureMethod = "HMAC-SHA1";
		$oauthVersion         = "1.0";

		//generate signature
		$sigBase = "GET&" . rawurlencode( $baseUrl ) . "&"
		           . rawurlencode( "imageID=" . $id
		           . "&oauth_consumer_key=" . rawurlencode( $consumerKey )
                   . "&oauth_nonce=" . rawurlencode( $nonce )
                   . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
                   . "&oauth_timestamp=" . $oauthTimestamp
                   . "&oauth_version=" . $oauthVersion
                    );

		$sigKey   = $consumerSecret . "&";
		$oauthSig = base64_encode( hash_hmac( "sha1", $sigBase, $sigKey, true ) );

		//generate full request URL
		$requestUrl = $baseUrl . "?"
		              . "imageID=" . $id
		              . "&oauth_consumer_key=" . rawurlencode( $consumerKey )
		              . "&oauth_nonce=" . rawurlencode( $nonce )
		              . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
		              . "&oauth_timestamp=" . rawurlencode( $oauthTimestamp )
		              . "&oauth_version=" . rawurlencode( $oauthVersion )
		              . "&oauth_signature=" . rawurlencode( $oauthSig );

		$file = download_url( $requestUrl );

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
	public function check_attachment( $post_name, $call_type = 'remote' ) {

		// Switch to another blog to check post existence.
		if ( $call_type == 'remote' && is_multisite() ) {
			//$site = get_current_site();
			//$site_id = $site->id;
			// todo: hard coded number for testing, remove for production
			$site_id = '229';
			switch_to_blog( $site_id );
		}

		global $wpdb;
		$attachment_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name='%s';", $post_name ) );

		if ( ! empty( $attachment_id ) ) {
			$attachment_id = $attachment_id[0];
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