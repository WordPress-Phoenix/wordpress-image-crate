<?php

namespace ImageCrate\Service;

/**
 * Class Imagn
 *
 * @package ImageCrate\Service
 */
class Imagn {

	/**
	 * API base URL
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * API OAuth2 token
	 *
	 * @var string
	 */
	private $api_token;

	public function __construct() {
		$this->api_url = 'https://imagn.com';

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

		$offset = ($page - 1) * $posts_per_page;

		$request_url = "{$this->api_url}/rest/search/" .
		               "?limit={$posts_per_page}" .
                       "&offset={$offset}" .
		               "&terms={$search_term}";

		$request_url = str_replace( ' ', '%20', $request_url );

		$response = wp_remote_get(
			$request_url,
			[
				'timeout'       => 10,
				'headers'       => [
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
					"{$response_code} response from Imagn API - Request URL: {$request_url}"
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
		if ( ! isset( $results['response']['payload'] ) ) {
			return [];
		}

		$results = $results['response']['payload']['results']['item'];

		$images = [];

		foreach ( $results as $image ) {
			$images[] = [
				'id'          => strval($image[0]['imgId']),
				'title'       => $image[0]['headline'],
				'filename'    => $image[0]['headline'],
				'caption'     => $image[0]['caption'],
				'description' => $image[0]['caption'],
				'type'        => 'image',
				'sizes'       => [
					'thumbnail' => [
						'url'    => $image[0]['thumbUrl'],
						'width'  => '250',
						'height' => '250',
					],
				],
				'url'         => $image[0]['fullUrl'],
				'max_width'   => (int)$image[0]['width'],
				'max_height'  => (int)$image[0]['height'],
				'date'        => strtotime( $image[0]['dateCreate'] )
			];
		}

		return $images;
	}

	/**
	 * Get image download link for single image
	 *
	 * @param string $imgId
	 *
	 * @return string
	 */
	public function download_single( $imgId ) {
	    $image_url = 'https://imagn.com/rest/download/?imageID=' . $imgId;

        // Adds auth headers to download url request
        add_filter('http_request_args', function ($r, $url) use ($image_url) {
            if ($image_url !== $url) {
                return $r;
            }

            $r['headers']['Authorization'] = "Bearer $this->api_token";

            return $r;
        }, 10, 2);

	    return $image_url;
	}

	/**
	 * Get OAuth2 token from Getty Images
	 */
	private function get_access_token() {
	    if (defined('IMAGN_API_TOKEN')) {
	        $this->api_token = IMAGN_API_TOKEN;
        }
	}
}