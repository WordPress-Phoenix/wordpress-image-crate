<?php

namespace ImageCrate\Includes;

use WPOP\V_2_8\Password;

class Getty_API_Helper {
	public static $api_root  = 'https://api.gettyimages.com';
	public static $route_auth = 'oauth2/token';
	public static $route_search = '/v3/search/images';
	public static $route_single = 'v3/image/';
	public static $cache_key = 'oauth-token';
	public static $cache_group = 'ic-getty-images';
	public static $cache_timeout = ( 30 * MINUTE_IN_SECONDS - 30 );
	public static $opt_api_key = 'ic_getty_api_key';
	public static $opt_api_secret = 'ic_getty_api_secret';

	public static function get_api_key() {
		$encrypted = is_multisite() ? get_site_option( self::$opt_api_key ) : get_option( self::$opt_api_key );
//		error_log( 'decrpyted api_key' );
//		error_log( var_export( Password::decrypt($encrypted), true ) );
//		return ! empty( $encrypted ) ? Password::decrypt( $encrypted ) : 'was-empty';
		return $encrypted;
	}

	public static function get_api_secret_key() {
		$encrypted = is_multisite() ? get_site_option( self::$opt_api_secret ) : get_option( self::$opt_api_secret );
//		error_log( 'decrpyted api secret' );
//		error_log( var_export( Password::decrypt($encrypted), true ) );
//		return ! empty( $encrypted ) ? Password::decrypt( $encrypted ) : 'was-empty';
		return $encrypted;
	}
}