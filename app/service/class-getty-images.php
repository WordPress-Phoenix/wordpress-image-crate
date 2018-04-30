<?php

namespace ImageCrate\Service;

/**
 * Class Getty_Images
 *
 * @package ImageCrate\Service
 */
class Getty_Images {

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

	/**
	 * If site has premium access.
	 *
	 * @var string
	 */
	private $access_type;

	public function __construct() {

		if ( defined( 'GETTY_API_KEY' ) ) {
			$this->api_key = GETTY_API_KEY;
		}

		if ( defined( 'GETTY_CLIENT_SECRET' ) ) {
			$this->api_secret = GETTY_CLIENT_SECRET;
		}

		$premium_access    = get_option( 'fs_option_getty_access_type' );
		$this->access_type = $premium_access === 'Premium' ? 'premiumaccess' : 'editorialsubscription';

		$this->api_url = 'https://api.gettyimages.com';

		$this->get_access_token();

	}

	/**
	 * Fetch image list from API
	 *
	 * @param string $search_term    The image search term
	 * @param int    $page           The request page number
	 * @param int    $posts_per_page The images per page
	 *
	 * @return array
	 */
	public function fetch( $search_term = '', $page = 1, $posts_per_page = 40 ) {

		// Only make an API request if a search term has been set.
		if ( empty( $search_term ) ) {
			return [];
		}

		$request_url = "{$this->api_url}/v3/search/images/editorial" .
		               "?file_types=jpg" .
		               "&page={$page}" .
		               "&page_size={$posts_per_page}" .
		               "&phrase={$search_term}" .
		               "&sort_order=newest" .
		               "&product_types={$this->access_type}" .
		               "&fields=detail_set,largest_downloads,max_dimensions,date_submitted";

		$response = wp_remote_get(
			$request_url,
			[
				'timeout'       => 10,
				'headers'       => [
					'Api-Key'       => "$this->api_key",
					'Authorization' => "Bearer $this->api_token",
				],
				'wp-rest-cache' => 'exclude',
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) ) {
			return wp_send_json_error( $response->get_error_message(), 500 );
		}

		try {
			if ( $response_code != '200' ) {
				throw new \Exception(
					"{$response_code} response from Getty API - Request URL: {$request_url}"
				);
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e );
		}

		$results = json_decode( $response['body'], true );

		// If there are no image results return nothing
		if ( ! isset( $results['images'] ) ) {
			return [];
		}

		$images = [];

		foreach ( $results['images'] as $image ) {
			$images[] = [
				'id'          => $image['id'],
				'title'       => $image['title'],
				'filename'    => $image['title'],
				'caption'     => $image['caption'],
				'description' => $image['caption'],
				'type'        => 'image',
				'sizes'       => [
					'thumbnail' => [
						'url'    => $image['display_sizes'][0]['uri'],
						'width'  => '150',
						'height' => '150',
					],
				],
				'url'         => $image['largest_downloads'][0]['uri'],
				'max_width'   => $image['max_dimensions']['width'],
				'max_height'  => $image['max_dimensions']['height'],
				'date'        => strtotime( $image['date_submitted'] )
			];
		}

		return $images;
	}

	/**
	 * Get image download link for single image
	 *
	 * @param string $download_url API url for image download
	 *
	 * @return string
	 */
	public function download_single( $download_url ) {
		$response = wp_remote_post(
			$download_url .
			"?auto_download=false" .
			"&product_type={$this->access_type}",
			[
				'timeout'       => 10,
				'headers'       => [
					'Api-Key'       => "$this->api_key",
					'Authorization' => "Bearer $this->api_token",
				],
				'wp-rest-cache' => 'exclude',
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) ) {
			return wp_send_json_error( $response->get_error_message(), 500 );
		}

		try {
			if ( $response_code != '200' ) {
				throw new \Exception(
					"Error requesting single download from Getty API." .
					"Response code: {$response_code} - Request URL: {$download_url}"
				);
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e );
		}

		$results = json_decode( $response['body'], true );

		return $results['uri'];
	}

	/**
	 * Get OAuth2 token from Getty Images
	 */
	private function get_access_token() {
		$response = wp_remote_post(
			"{$this->api_url}/oauth2/token",
			[
				'method'        => 'POST',
				'timeout'       => 5,
				'headers'       => [ 'Content-Type: application/x-www-form-urlencoded' ],
				'wp-rest-cache' => 'exclude',
				'body'          => [
					'grant_type'    => 'client_credentials',
					'client_id'     => $this->api_key,
					'client_secret' => $this->api_secret,
				],
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) ) {
			return wp_send_json_error( $response->get_error_message(), 500 );
		}

		try {
			if ( $response_code != '200' ) {
				throw new \Exception(
					"Error getting access token from Getty API. Response code: {$response_code}"
				);
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e );
		}

		$body = json_decode( $response['body'], true );

		$this->api_token = $body['access_token'];
	}
}