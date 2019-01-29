<?php

namespace ImageCrate\Service;

/**
 * Class Image_Exchange
 *
 * @package ImageCrate\Service
 */
class Image_Exchange {

	/**
	 * API base URL
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * API key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	private $api_secret;

	/**
	 * API OAuth2 token
	 *
	 * @var string
	 */
	private $api_token;

	public function __construct() {

		$this->api_url = 'https://images.fansided.com';

	}

	/**
	 * Fetch image list from API
	 *
	 * @param string $vertical      Vertical to filter images by
	 * @param int    $page           The request page number
	 * @param int    $posts_per_page The images per page
	 * @param string $search         Search term
	 *
	 * @return array
	 */
	public function fetch( $vertical = 'all', $page = 1, $posts_per_page = 40, $search = '' ) {

		$response = wp_remote_get(
			"{$this->api_url}/wp-json/image-exchange/v1/images" .
			"?vertical={$vertical}" .
			"&paged={$page}" .
			"&posts_per_page={$posts_per_page}" .
            "&search={$search}",
			[
				'timeout'       => 60,
				'wp-rest-cache' => 'exclude',
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) ) {
			$var = $response->get_error_message();
			return wp_send_json_error( $response->get_error_message(), 500 );
		}

		try {
			if ( $response_code != '200' ) {
				throw new \Exception( "{$response_code} response from Getty API" );
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e );
		}

		$images = json_decode( $response['body'], true );

		return $images;
	}

}