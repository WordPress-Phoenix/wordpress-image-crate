<?php

namespace ImageCrate;


class Legacy_Filters {

	public function __construct() {

		/**
		 * These filters were add from the Getty_Image VIP class.
		 * They probably don't need to exist as there seems to be multiple
		 * sets of filtering on image URLS. This will require some work to
		 * test the removal of URL filters.
		 */
		add_filter( 'wp_get_attachment_image_src', [ $this, 'set_getty_image_path' ] );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'getty_update_scrset_attr' ], 10, 1 );

	}

	/**
	 * Set up image path to point to new getty image location
	 *
	 * On the admin side, WordPress filters the output of the url string used to display the
	 * image. That is altered here.
	 *
	 * @param   string|array $image_path Default image source
	 *
	 * @return  string|array Updated image src to include 'getty-images'
	 */
	function set_getty_image_path( $image_path ) {
		// Make sure image path url is a string
		if ( is_array( $image_path ) ) {
			$image_path_url = $image_path[0];
		} else {
			$image_path_url = $image_path;
		}

		// Check for old multisite directory structure
		if ( ! stristr( $image_path_url, '/files/getty-images/' ) ) {
			$search_str  = '/\/sites\/\d*\/getty-images\//';
			$replace_str = '/getty-images/';
		} else {
			$search_str  = '/\/files\/getty-images\//';
			$replace_str = '/wp-content/uploads/getty-images/';
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
	 * @return  array Array with updated src in urls
	 */
	function getty_update_scrset_attr( $sources ) {
		foreach ( $sources as $key => $source ) {
			if ( preg_match( '/getty-images/', $sources[ $key ]['url'] ) ) {
				$sources[ $key ]['url'] = $this->set_getty_image_path( $source['url'] );
			}
		}

		return $sources;
	}

}