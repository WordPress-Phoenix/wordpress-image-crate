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
		//$limit                = '2';
		//$mode                 = 'any';
		$terms                = $phrase; // todo: maje this format friendly to usa today

		//generate signature
		$sigBase = "GET&" . rawurlencode( $baseUrl ) . "&"
		           . rawurlencode( "oauth_consumer_key=" . rawurlencode( $consumerKey )
		                           . "&oauth_nonce=" . rawurlencode( $nonce )
		                           . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
		                           . "&oauth_timestamp=" . $oauthTimestamp
		                           . "&oauth_version=" . $oauthVersion
		                           . "&terms=" . $terms );

		$sigKey   = $consumerSecret . "&";
		$oauthSig = base64_encode( hash_hmac( "sha1", $sigBase, $sigKey, true ) );

		//generate full request URL
		$requestUrl = $baseUrl . "?"
		              . "oauth_consumer_key=" . rawurlencode( $consumerKey )
		              . "&oauth_nonce=" . rawurlencode( $nonce )
		              . "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
		              . "&oauth_timestamp=" . rawurlencode( $oauthTimestamp )
		              . "&oauth_version=" . rawurlencode( $oauthVersion )
		              . "&oauth_signature=" . rawurlencode( $oauthSig )
		              //. "&mode=" . $mode
		              . "&terms=" . $terms;
		              //. "&limit=" . $limit;

		//make call
		$response = wp_remote_get( $requestUrl, array(
			'method'  => 'GET',
			'timeout' => 10,
		));

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode(wp_remote_retrieve_body( $response ), true);

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
}