<?php

/**
 * Image_Crate_Api Class
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Crate_Api {

	private $key;
	private $secret;
	private $api_url;
	public $directory;

	public function __construct() {
		// todo: obviously these values need to live somewhere else
		$this->key = USAT_API_KEY;
		$this->secret = USAT_API_SECRET;
		$this->api_url = "http://www.usatodaysportsimages.com/api/searchAPI/";
		$this->directory = 'usat-images';

		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'update_media_modal_file_refs' ), 99, 1 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'set_image_path' ) );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'update_scrset_attr' ), 10, 1 );
		add_filter( 'image_send_to_editor', array( $this, 'send_to_editor' ), 10, 1 );
	}

	public function fetch( $phrase, $pageToLoad ) {

		// todo: maybe add filters for expansion
		// One thing to note about this, if you add other parameters to the call you need to append them to
		// the sigBase variable in Alphabetical order or the call will fail.

		// Oauth Params
		$baseUrl              = $this->api_url;
		$consumerKey          = $this->key;
		$consumerSecret       = $this->secret;
		$oauthTimestamp       = time();
		$nonce                = md5( mt_rand() );
		$oauthSignatureMethod = "HMAC-SHA1";
		$oauthVersion         = "1.0";
		$limit                = 24;
		//$mode                 = 'phrase';
		$terms                = urlencode( $phrase ); // todo: make this format friendly to usa today

		//generate signature
		$sigBase = "GET&" . rawurlencode( $baseUrl ) . "&"
		           . rawurlencode( "limit=" . $limit
                   //. "&mode=" . $mode
		           . "&oauth_consumer_key=" . rawurlencode( $consumerKey )
                   . "&oauth_nonce=" . rawurlencode( $nonce )
                   . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
                   . "&oauth_timestamp=" . $oauthTimestamp
                   . "&oauth_version=" . $oauthVersion
                   . "&terms=" . $terms );

		$sigKey   = $consumerSecret . "&";
		$oauthSig = base64_encode( hash_hmac( "sha1", $sigBase, $sigKey, true ) );

		//generate full request URL
		$requestUrl = $baseUrl . "?"
		              . "limit=" . $limit
		              //. "&mode=" . $mode
		              . "&oauth_consumer_key=" . rawurlencode( $consumerKey )
		              . "&oauth_nonce=" . rawurlencode( $nonce )
		              . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
		              . "&oauth_timestamp=" . rawurlencode( $oauthTimestamp )
		              . "&oauth_version=" . rawurlencode( $oauthVersion )
		              . "&oauth_signature=" . rawurlencode( $oauthSig )
		              . "&terms=" . $terms;

		//make call
		$response = wp_remote_get( $requestUrl, array(
			'method'  => 'GET',
			'timeout' => 10,
		));

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode(wp_remote_retrieve_body( $response ), true);

		// todo: fix array error catching for secondary array_map param
		$images = array_map( function ( $index ) {
			return $index[0];
		}, $response_body['results']['item'] );

		return $images;

	}

	public function prepare_attachments( $attachments ) {
		return array_map( array( $this, 'prepare_attachment_for_js' ), $attachments );
	}

	private function prepare_attachment_for_js( $attachment ) {

		// how usat sends over data
		return array(
			'id' => $attachment['imgId'],
			'title' => $attachment['headline'],
			'filename' => $attachment['headline'],
			'caption' => $attachment['caption'],
			'type' => 'image',
			'sizes' => array(
				'thumbnail' => array(
					'url' => $attachment['thumbUrl'],
				),
				'full' => array(
					'url' => $attachment['fullUrl'],
				),
				'large'   => array(
					'url' => $attachment['previewUrl'],
				),
			),
			//'download_uri' => sprintf( '%s?auto_download=false', $attachment['fullUrl'] ) ,
			'download_uri' => $attachment['fullUrl'],
			'max_width' => $attachment['width'],
			'max_height' => $attachment['height'],
		);
	}

	/**
	 * Set up image path to point to new getty image location
	 *
	 * @param   array $response array of prepared attachment data.
	 *
	 * @return  string              array of updated attachment data.
	 */
	public function update_media_modal_file_refs( $response ) {

		if ( preg_match( "/" . $this->directory . "/", $response['url'] ) ) {

			$response['url'] = $this->set_image_path( $response['url'] );

			if ( isset( $response['sizes'] ) ) {

				foreach ( $response['sizes'] as $key => $size ) {
					$response['sizes'][ $key ]['url'] = $this->set_image_path( $size['url'] );
				}
			}

		}

		return $response;
	}

	/**
	 * Set up image path to point to new getty image location
	 *
	 * On the admin side, WordPress filters the output of the url string used to display the
	 * image. That is altered here.
	 *
	 * @param   string|array $image_path Default image source
	 *
	 * @return  string|array                    Updated image src to include 'getty-images'
	 */
	public function set_image_path( $image_path ) {

		// Make sure image path url is a string
		if ( is_array( $image_path ) ) {
			$image_path_url = $image_path[0];
		} else {
			$image_path_url = $image_path;
		}

		// Check for old multisite directory structure
		if ( ! stristr( $image_path_url, '/files/' . $this->directory . '/' ) ) {
			$search_str  = '/\/sites\/\d*\/' . $this->directory . '\//';
			$replace_str = '/' . $this->directory . '/';
		} else {
			$search_str  = '/\/files\/' . $this->directory . '\//';
			$replace_str = '/wp-content/uploads/' . $this->directory . '/';
		}

		if ( is_array( $image_path ) ) {
			$image_path[0] = preg_replace( $search_str, $replace_str, $image_path[0] );
		} else {
			$image_path = preg_replace( $search_str, $replace_str, $image_path );
		}

		return $image_path;
	}

	/**
	 * Update srcset urls to point to getty images global folder location
	 *
	 * @param   array $sources One or more arrays of source data to include in the 'srcset'.
	 *
	 * @return  array               Array with updated src in urls
	 */
	public function update_scrset_attr( $sources ) {

		foreach ( $sources as $key => $source ) {
			if ( preg_match( "/" . $this->directory . "/", $sources[ $key ]['url'] ) ) {
				$sources[ $key ]['url'] = $this->set_image_path( $source['url'] );
			}
		}

		return $sources;
	}

	/**
	 * Update post meta and image path when image is sent to the editor
	 * from the media modal.
	 *
	 * @param   string $html Image markup sent to the editor
	 *
	 * @return  string|array             Updated markup with 'getty-images' in src
	 */
	public function send_to_editor( $html ) {
		return $this->set_image_path( $html );
	}
}