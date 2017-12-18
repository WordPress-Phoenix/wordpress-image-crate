<?php

namespace ImageCrate\Includes;

use ImageCrate\Includes\Getty_API_Helper as Getty;
/**
 * Class Getty_Auth_Token
 * @package ImageCrate\Admin
 */
class Getty_Auth_Token {

	public static function get_auth_token() {
		$cached = wp_cache_get( Getty::$cache_key, Getty::$cache_group );
		if ( ! empty( $cached ) ) {
			return $cached;
		}

		// Make call for token to use in authorized resource requests
		$response = wp_remote_post( Getty::$api_root . '/' . Getty::$route_auth, [
			'timeout' => 5,
			'headers' => array( 'Content-Type: application/x-www-form-urlencoded' ),
			'body' => array(
				'grant_type' => 'client_credentials',
				'client_id' => Getty::get_api_key(),
				'client_secret' => Getty::get_api_secret_key(),
			),
		] );

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			return false;
		}

		$response_data = json_decode( $response['body'] );
		$token = $response_data->access_token;
		// getty token expirary is every 30 minutes, we cache for just under that
		wp_cache_set( Getty::$cache_key, $token, Getty::$cache_group, Getty::$cache_timeout );

		return $token;
	}

	public static function get_headers_auth_array() {
		return [
			'Api-Key' => Getty::get_api_key(),
			'Authorization' => 'Bearer ' . self::get_auth_token(),
		];
	}
}