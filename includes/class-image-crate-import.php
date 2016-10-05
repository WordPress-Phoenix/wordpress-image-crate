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
	 * Import image from an url
	 *
	 * @param $url
	 *
	 * @return bool|int|object
	 */
	public function image( $imageID, $filename ) {

		$file_array = [];

		// Download file to temp location
		$file_array['tmp_name'] = $this->download( $imageID );

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

		$proper_name = $filename;
		$api_image   = $file_array['name'];

		$image_type = pathinfo( $api_image );
		$file_name  = basename( $api_image, '.' . $image_type['extension'] );

		$file_array['name'] = str_replace( $file_name, $proper_name, $api_image );


		// Do the validation and storage stuff
		$id = media_handle_sideload( $file_array, 0 );

		$this->delete_file( $file_array['tmp_name'] );

		return is_wp_error( $id ) ? false : $id;

	}

	/**
	 * Download a file by its URL
	 *
	 * @param  string $url
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

}