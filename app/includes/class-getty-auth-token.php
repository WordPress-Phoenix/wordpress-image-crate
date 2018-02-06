<?php

namespace ImageCrate\Includes;

use ImageCrate\Includes\Getty_API_Helper as Getty;

/**
 * Class Getty_Auth_Token
 * @package ImageCrate\Admin
 */
class Getty_Auth_Token {

	/**
	 * Retrieve Auth Token needed for Getty API calls. Set in wp_cache for just shy 30m. Auto-retry POST if first call
	 * times out.
	 *
	 * @return bool|mixed
	 */
	public static function get_auth_token() {
		$cached = wp_cache_get( Getty::$cache_key, Getty::$cache_group );

		// return cache value
		if ( ! empty( $cached ) ) {
			return $cached;
		}

		// Make call for token to use in authorized resource requests
		$response = self::post_to_getty_api();

		if ( is_wp_error( $response )
		     || ! isset( $response['body'] )
		     || empty( $response['body'] )
		) {
			$response = self::post_to_getty_api( 10 ); // retry call, longer timeout
		} else {
			$response_data = json_decode( $response['body'] );
			$token         = $response_data->access_token;
			// getty token expirary is every 30 minutes, we cache for just under that
			wp_cache_set( Getty::$cache_key, $token, Getty::$cache_group, Getty::$cache_timeout );

			return $token;
		}

		if ( is_wp_error( $response ) || ! isset( $response['body'] ) || empty( $response['body'] ) ) {
			return false;
		}

		$response_data = json_decode( $response['body'] );
		wp_cache_set(
			Getty::$cache_key,
			$response_data->access_token,
			Getty::$cache_group,
			Getty::$cache_timeout
		);

		return $response_data->access_token;
	}

	public static function get_headers_auth_array( $headers = array() ) {
		return wp_parse_args( [
				'Api-Key'       => Getty::get_api_key(),
				'Authorization' => 'Bearer ' . self::get_auth_token(),
			], $headers
		);
	}

	public static function post_to_getty_api( $timeout = 5 ) {
		$response = wp_remote_post( Getty::$api_root . '/' . Getty::$route_auth, [
			'headers' => array( 'Content-Type: application/x-www-form-urlencoded' ),
			'timeout' => $timeout,
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => Getty::get_api_key(),
				'client_secret' => Getty::get_api_secret_key(),
			),
		] );

		return $response;
	}
}