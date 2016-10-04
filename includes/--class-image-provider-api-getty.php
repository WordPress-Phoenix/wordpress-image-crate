<?php

/**
 * Image_Api_Provider Class
 *
 * @version  0.1.1
 * @package  WP_Trapper_Keeper
 * @author   justintucker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Crate_Api {

	private $key;
	private $secret;
	private $token_url;

	public function __construct() {

		// todo: obviously these values need to live somewhere else
		$this->key = GETTY_API_KEY;
		$this->secret = GETTY_CLIENT_SECRET;
		$this->token_url = 'https://api.gettyimages.com/oauth2/token';

	}

	private function get_access_token() {

		// todo: add filters for expansion
		$response = wp_remote_post( $this->token_url, array(
			'method'  => 'POST',
			'timeout' => 5,
			'headers' => array( "Content-Type: application/x-www-form-urlencoded" ),
			'body'    => array( 'grant_type' => 'client_credentials', 'client_id' => $this->key, 'client_secret' => $this->secret )
		) );

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$token         = $response_body->access_token;

		return $token;

	}

	public function fetch( $phrase, $pageToLoad ) {

		// todo: maybe add filters for expansion
		$url = "https://api.gettyimages.com:443/v3/search/images/editorial";
		$data = [
			'fields' => 'detail_set,largest_downloads,max_dimensions',
			'page' => $pageToLoad,
			'page_size' => '30',
			'phrase' => $phrase,
			'sort_order' => 'best_match'
		];
		$params = http_build_query( $data );

		$request = $url . '?' . $params;

		$access_token = $this->get_access_token();

		if ( empty( $access_token ) ) {
		    return [];
		}

		$response = wp_remote_get( $request, [
			'method'  => 'GET',
			'timeout' => 10,
			'headers' => array(
				"Api-Key"       => "$this->key",
				"Authorization" => "Bearer $access_token"
			)
		]);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		// usa today puts all their images in an array called 'item'.....
		return $response_body->images;

	}

	public function prepare_attachments( $attachments ) {
		return array_map( array( $this, 'prepare_attachment_for_js'), $attachments );
	}

	private function prepare_attachment_for_js( $attachment ) {
		return array(
			'id'           => $attachment->id,
			'title'        => $attachment->title,
			'caption'      => $attachment->caption,
			'type'         => 'image',
			'sizes'        => array(
				'thumbnail' => array(
					'url' => $attachment->display_sizes[2]->uri,
				),
				'full'      => array(
					'url' => $attachment->display_sizes[0]->uri,
				),
				'large'     => array(
					'url' => $attachment->display_sizes[1]->uri,
				),
			),
			'download_uri' => sprintf( '%s?auto_download=false', $attachment->largest_downloads[0]->uri ),
			'max_width'    => $attachment->max_dimensions->width,
			'max_height'   => $attachment->max_dimensions->height,
		);
	}


}