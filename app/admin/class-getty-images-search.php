<?php

namespace ImageCrate\Admin;

use ImageCrate\Includes\Getty_API_Helper as Getty;
use ImageCrate\Includes\Getty_Auth_Token;

class Getty_Images_Search {

	/**
	 * Directory where to store images
	 *
	 * @var string
	 */
	public $directory;

	/**
	 * Site vertical to pull from
	 *
	 * @var string
	 */
	public $vertical;

	/**
	 * Site vertical to pull from
	 *
	 * @var string
	 */
	public $request = [];


	/**
	 * Setup image source calls and filters
	 *
	 */
	public function __construct() {
		$this->directory = 'getty';

		// process image paths for saving on server
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'update_media_modal_file_refs' ), 99, 1 );

		add_filter( 'wp_get_attachment_image_src', array( $this, 'set_image_path' ) );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'update_scrset_attr' ), 10, 1 );
		add_filter( 'image_send_to_editor', array( $this, 'send_to_editor' ), 10, 1 );
		add_filter( 'image_get_intermediate_size', array( $this, 'set_image_editor_thumb_url' ), 10, 1 );
		add_filter( 'w', array( $this, 'filter_controller_title' ) );
	}

	/**
	 * Filter page title in media modal.
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function filter_controller_title( $title ) {
		$title['page_title'] = 'Getty Images';

		return $title;
	}

	/**
	 * Request image data from source
	 *
	 * @param string $phrase   Search phrase to make the call
	 * @param string $page     Offset to return a new 'page'
	 * @param string $per_page Amount to show per page
	 *
	 * @return array|bool
	 */
	public function fetch( $phrase, $page, $per_page ) {

		$search_args = array(
			'phrase'        => $phrase,
			'page'          => $page,
//			'page_size'     => $per_page,
			'page_size'     => '5',
			'sort_order'    => 'best_match',
			'product_types' => 'premiumaccess',
			'file_types'    => 'jpg',
			'fields'        => implode( ',', [
					'id',
					'largest_downloads',
					'orientation',
					'preview',
					'thumb',
					'date_created',
					'caption',
					'title',
				]
			),
		);

		if ( empty( $phrase ) ) {
			unset( $search_args['phrase'] );
		}

		if ( 0 === $page ) {
			unset( $search_args['page'] );
		}

//		error_log( var_export( Getty::$api_root . Getty::$route_search . '?' . http_build_query( $search_args ), true
//		 ) );
//
//		error_log( 'the authoriz token yo:' );
//		error_log( var_export( Getty_Auth_Token::get_auth_token(), true ) );


		$search_response = wp_remote_get(
			Getty::$api_root . Getty::$route_search . '?' . http_build_query( $search_args ),
			array(
				'timeout' => 10,
				'headers' => Getty_Auth_Token::get_headers_auth_array(),
			)
		);

		if ( is_wp_error( $search_response ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $search_response ), true );

		if ( ! is_null( $response_body ) && is_array( $response_body ) ) {
			return $response_body['images'];
		}

		return [];
	}

	/**
	 * Iterate over results to map data
	 *
	 * @param array $attachments raw data from the remote call
	 *
	 * @return array Image data formatted to what WordPress expects
	 */
	public function prepare_attachments( $attachments ) {
		return array_map( array( $this, 'prepare_attachment_for_js' ), $attachments );
	}

	/**
	 * Map requested image data to what WordPress expects
	 *
	 * @param array $attachment
	 *
	 * @return array Formatted data backbone collections
	 */
	private function prepare_attachment_for_js( $attachment ) {
		error_log( 'first attachment...' );
		error_log( var_export( $attachment, true ) );

		$thumb = wp_parse_url( $attachment['sizes']['thumb']['url'] );

		return array(
			'id'           => $attachment['id'],
			'title'        => htmlspecialchars( $attachment['title'] ),
			'filename'     => htmlspecialchars( $attachment['id'] . '-' . sanitize_title( $attachment['title'] ) ),
			'caption'      => htmlspecialchars( $attachment['caption'] ),
			'description'  => htmlspecialchars( $attachment['caption'] ),
			'type'         => 'image',
			'sizes'        => array(
				'large'     => array(
					'url'    => $attachment['display_sizes'][0]['uri'],
					'width'  => 320,
					'height' => 240,
				),
				'thumbnail' => array(
					'url'    => $attachment['display_sizes'][1]['uri'],
					'width'  => 160,
					'height' => 160,
				),

			),
			'download_uri' => $attachment['largest_downloads'][0]['uri'],
//			'max_width'    => $attachment['width'],
//			'max_height'   => $attachment['height'],
		);
	}

	/**
	 * Set up image path to point to new $directory location
	 *
	 * @param array $response array of prepared attachment data.
	 *
	 * @return array Updated attachment data.
	 */
	public function update_media_modal_file_refs( $response ) {
		if ( preg_match( "/" . $this->directory . "/", $response['url'] ) ) {
			$response['url'] = $this->set_image_path( $response['url'] );
			if ( isset( $response['sizes'] ) ) {
				foreach ( $response['sizes'] as $key => $size ) {
					$response['sizes'][ $key ]['url'] = $this->set_image_path( $size['url'] );
				}
			}
		}

		return $response;
	}

	/**
	 * Set up image path to point to custom image directory
	 *
	 * On the admin side, WordPress filters the output of the url string used to display the
	 * image. That is altered here.
	 *
	 * @param   string|array $image_path Default image source
	 *
	 * @return  string|array Updated image src to reference custom directory
	 */
	public function set_image_path( $image_path ) {
		$image_path_url = is_array( $image_path ) ? $image_path[0] : $image_path;

		if ( stristr( $image_path_url, $this->directory ) ) {

			$search_str  = '/\/files\/' . $this->directory . '\//';
			$replace_str = '/wp-content/uploads/' . $this->directory . '/';

			if ( stristr( $image_path_url, '/blogs.dir/' ) ) {
				$search_str  = '/\/blogs.dir\/\d*\/files\/sites\/\d*\/' . $this->directory . '\//';
				$replace_str = '/uploads/' . $this->directory . '/';
			}

			$image_path = preg_replace( $search_str, $replace_str, $image_path );
		}

		return $image_path;
	}

	/**
	 * Filter edit image thumbnail in admin modal editor
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function set_image_editor_thumb_url( $data ) {
		if ( stristr( $data['url'], $this->directory ) ) {
			$data['url'] = $this->set_image_path( $data['url'] );
		}

		return $data;
	}

	/**
	 * Update srcset urls to point to custom image directory
	 *
	 * @param   array $sources One or more arrays of source data to include in the 'srcset'.
	 *
	 * @return  array Data with updated src in urls
	 */
	public function update_scrset_attr( $sources ) {
		foreach ( $sources as $key => $source ) {
			if ( preg_match( "/" . $this->directory . "/", $sources[ $key ]['url'] ) ) {
				$sources[ $key ]['url'] = $this->set_image_path( $source['url'] );
			}
		}

		return $sources;
	}

	/**
	 * Update post meta and image path when image is sent to the editor
	 * from the media modal.
	 *
	 * @param   string $html Image markup sent to the editor
	 *
	 * @return  string|array Updated markup with to reference custom directory
	 */
	public function send_to_editor( $html ) {
		return $this->set_image_path( $html );
	}

}