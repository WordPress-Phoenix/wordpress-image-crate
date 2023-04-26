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
		add_filter( 'wp_get_attachment_image_src', [ $this, 'set_imagn_image_path' ] );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'getty_update_scrset_attr' ], 10, 1 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'imagn_update_scrset_attr' ], 10, 1 );
		add_filter( 'image_send_to_editor', [ $this, 'getty_send_to_editor' ], 10, 1 );
		add_filter( 'image_send_to_editor', [ $this, 'imagn_send_to_editor' ], 10, 1 );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'update_media_modal_file_refs' ], 99, 1 );
        add_filter( 'media_library_infinite_scrolling', '__return_true' );

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
	public function set_getty_image_path( $image_path ) {
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
	 * Set up image path to point to new Imagn image location
	 *
	 * On the admin side, WordPress filters the output of the url string used to display the
	 * image. That is altered here.
	 *
	 * @param   string|array $image_path Default image source
	 *
	 * @return  string|array Updated image src to include 'getty-images'
	 */
	public function set_imagn_image_path( $image_path ) {
		// Make sure image path url is a string
		if ( is_array( $image_path ) ) {
			$image_path_url = $image_path[0];
		} else {
			$image_path_url = $image_path;
		}

		// Check for old multisite directory structure
		if ( ! stristr( $image_path_url, '/files/imagn-images/' ) ) {
			$search_str  = '/\/sites\/\d*\/imagn-images\//';
			$replace_str = '/imagn-images/';
		} else {
			$search_str  = '/\/files\/imagn-images\//';
			$replace_str = '/wp-content/uploads/imagn-images/';
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
	public function getty_update_scrset_attr( $sources ) {
		foreach ( $sources as $key => $source ) {
			if ( preg_match( '/getty-images/', $sources[ $key ]['url'] ) ) {
				$sources[ $key ]['url'] = $this->set_getty_image_path( $source['url'] );
			}
		}

		return $sources;
	}
	
	/**
	 * Update srcset urls to point to getty images global folder location
	 *
	 * @param   array $sources One or more arrays of source data to include in the 'srcset'.
	 *
	 * @return  array Array with updated src in urls
	 */
	public function imagn_update_scrset_attr( $sources ) {
		foreach ( $sources as $key => $source ) {
			if ( preg_match( '/imagn-images/', $sources[ $key ]['url'] ) ) {
				$sources[ $key ]['url'] = $this->set_imagn_image_path( $source['url'] );
			}
		}

		return $sources;
	}

	/**
	 * Set up image path to point to new getty image location
	 *
	 * @param   array   $response   array of prepared attachment data.
	 *
	 * @return  array              array of updated attachment data.
	 */
	public function update_media_modal_file_refs( $response ) {

		if ( preg_match( '/getty-images/', $response['url'] ) ) {

			$response['url'] = $this->set_getty_image_path( $response['url'] );

			if ( isset( $response['sizes'] ) ) {

				foreach ( $response['sizes'] as $key => $size ) {
					$response['sizes'][ $key ]['url'] = $this->set_getty_image_path( $size['url'] );
				}
			}
		}
		
		if ( preg_match( '/imagn-images/', $response['url'] ) ) {

			$response['url'] = $this->set_imagn_image_path( $response['url'] );

			if ( isset( $response['sizes'] ) ) {

				foreach ( $response['sizes'] as $key => $size ) {
					$response['sizes'][ $key ]['url'] = $this->set_imagn_image_path( $size['url'] );
				}
			}
		}
		return $response;
	}

	/**
	 * Update post meta and image path when image is sent to the editor
	 * from the media modal.
	 *
	 * @param   string          $html    Image markup sent to the editor
	 *
	 * @return  string|array             Updated markup with 'getty-images' in src
	 */
	public function getty_send_to_editor( $html ) {
		return $this->set_getty_image_path( $html );
	}
	
	/**
	 * Update post meta and image path when image is sent to the editor
	 * from the media modal.
	 *
	 * @param   string          $html    Image markup sent to the editor
	 *
	 * @return  string|array             Updated markup with 'getty-images' in src
	 */
	public function imagn_send_to_editor( $html ) {
		return $this->set_imagn_image_path( $html );
	}

}