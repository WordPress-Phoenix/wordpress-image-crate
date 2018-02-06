<?php

namespace ImageCrate\Includes;

use WPOP\V_3_1\Password;

class Getty_API_Helper {
	public static $api_root = 'https://api.gettyimages.com';
	public static $route_auth = 'oauth2/token';
	public static $route_search = '/v3/search/images';
	public static $route_single = 'v3/image/';
	public static $cache_key = 'oauth-token';
	public static $cache_group = 'ic-getty-images';
	public static $cache_timeout = ( 30 * MINUTE_IN_SECONDS - 45 );
	public static $opt_api_key = 'ic_getty_api_key';
	public static $opt_api_secret = 'ic_getty_api_secret';
	public static $opts_class_version = '3_1';

	public static function get_api_key() {
		return self::get_encrypted_option( self::$opt_api_key );
	}

	public static function get_api_secret_key() {
		return self::get_encrypted_option( self::$opt_api_secret );
	}

	protected static function get_encrypted_option( $key, $version = null, $default = false ) {
		$version = ! empty( $version ) ? $version : self::$opts_class_version;
		if ( class_exists( 'WPOP\\V_' . $version . '\\Password' ) ) {
			$encrypted = is_multisite() ? get_site_option( $key, $default ) : get_option( $key, $default );

			return ! empty( $encrypted ) ? Password::decrypt( $encrypted ) : false;
		} else {
			return null;
		}
	}
}