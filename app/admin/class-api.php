<?php

namespace ImageCrate\Admin;


/**
 * Api Class
 *
 * Handle returned data for image source.
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */
class Api {

	/**
	 * Key for image source endpoint
	 *
	 * @var string
	 */
	private $key;

	/**
	 * Secret for image source endpoint
	 *
	 * @var string
	 */
	private $secret;

	/**
	 * Image source endpoint to hit
	 *
	 * @var string
	 */
	private $api_url;

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
	 * Query Parameter to load USAT specific entertainment images
	 *
	 * @var string
	 */
	public $sipa;

	/**
	 * Setup image source calls and filters
	 *
	 */
	public function __construct() {
		$this->key = USAT_API_KEY;
		$this->secret = USAT_API_SECRET;
		$this->api_url = "http://www.usatodaysportsimages.com/api/searchAPI/";
		$this->directory = 'usat-images';

		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'update_media_modal_file_refs' ), 99, 1 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'set_image_path' ) );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'update_scrset_attr' ), 10, 1 );
		add_filter( 'image_send_to_editor', array( $this, 'send_to_editor' ), 10, 1 );
		add_filter( 'image_get_intermediate_size', array( $this, 'set_image_editor_thumb_url' ), 10, 1 );
		add_filter( 'w', array( $this, 'filter_controller_title') );
	}

	/**
	 * Filter page title in media modal.
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function filter_controller_title( $title ) {
		$title['page_title'] = 'USA Today Images';
		return $title;
	}

	/**
	 * Set default query
	 *
	 * @return string
	 */
	public static function get_default_query() {
		global $fs_vip;

		// check if site option
		$term = get_option( 'image_crate_default_search', false );

		// fallback to api details
		if ( empty( $term ) ) {
			$current_site_details = $fs_vip->modules->site_settings->get_details();
			$location = ! empty( $current_site_details['location'] ) ? $current_site_details['location'] : '';
			$topic = ! empty( $current_site_details['topic'] ) ? $current_site_details['topic'] : '';

			// format term based on data set
			if ( $location == '' && $topic != '' ) {
				$term = $topic;
			} elseif( $location != '' && $topic != '' ) {
				$term = sprintf('%s %s', $location, $topic );
			}

			// remove all from any term
			$term = str_replace( 'All', '', $term);
		}

		return $term;
	}

	/**
	 * Request image data from source
	 *
	 * @param string $phrase Search phrase to make the call
	 * @param string $page Offset to return a new 'page'
	 * @param string $per_page Amount to show per page
	 *
	 * @return array|bool
	 */
	public function fetch( $phrase, $page, $per_page ) {
		// One thing to note about this, if you add other parameters to the call you need to append them to
		// the sigBase variable in Alphabetical order or the call will fail.
		$baseUrl              = $this->api_url;
		$consumerKey          = $this->key;
		$consumerSecret       = $this->secret;
		$oauthTimestamp       = time();
		$nonce                = md5( mt_rand() );
		$oauthSignatureMethod = "HMAC-SHA1";
		$oauthVersion         = "1.0";
		$limit                = $per_page;
		$mode                 = 'bool'; // options include (any, all, phrase, bool)
		$offset               = $page;
		$this->vertical       = $this->determine_vertical();
		$this->sipa           = $this->get_sipa_value();
		$terms                = $this->search_negotiator( $phrase );

		// Generate signature
		$sigBase = "GET&" . rawurlencode( $baseUrl ) . "&"
					. rawurlencode( "limit=" . $limit
					. "&mode=" . $mode
					. "&oauth_consumer_key=" . rawurlencode( $consumerKey )
					. "&oauth_nonce=" . rawurlencode( $nonce )
					. "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
					. "&oauth_timestamp=" . $oauthTimestamp
					. "&oauth_version=" . $oauthVersion
					. "&offset=" . $offset
					. "&sipa=" . $this->sipa
					. "&terms=" . rawurlencode( $terms ) );

		$sigKey   = $consumerSecret . "&";
		$oauthSig = base64_encode( hash_hmac( "sha1", $sigBase, $sigKey, true ) );

		// Generate full request URL
		$requestUrl = $baseUrl . "?"
					. "&limit=" . $limit
					. "&mode=" . rawurlencode( $mode )
					. "&oauth_consumer_key=" . rawurlencode( $consumerKey )
					. "&oauth_nonce=" . rawurlencode( $nonce )
					. "&oauth_signature_method=" . rawurlencode( $oauthSignatureMethod )
					. "&oauth_timestamp=" . rawurlencode( $oauthTimestamp )
					. "&oauth_version=" . rawurlencode( $oauthVersion )
					. "&oauth_signature=" . rawurlencode( $oauthSig )
					. "&offset=" . $offset
					. "&sipa=" . $this->sipa
					. "&terms=" . rawurlencode( $terms );

		// Make call
		$response = wp_remote_get( $requestUrl, array(
			'method'  => 'GET',
			'timeout' => 10,
			'wp-rest-cache' => 'exclude'
		));

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode(wp_remote_retrieve_body( $response ), true);

		if ( ! is_null( $response_body ) ) {
			$images = array_map( function ( $index ) {
				return $index[0];
			}, $response_body['results']['item'] );

			return $images;
		}

		return [];
	}

	/**
	 * Process query values for USA Today Images
	 *
	 * Parse query values and reconstruct them in a format that usa today images can understand.
	 * Surrounding each term with () allows grouping and more specific searching within the USA Today API.
	 *
	 * @param $phrase
	 *
	 * @return string
	 */
	public function search_negotiator( $phrase ) {
		if ( empty( $phrase ) && false == $this->vertical) {
		    return '';
		}

		$search_phrase = [];

		if ( isset( $phrase ) ) {
			$search_phrase[] = $phrase;
		}

		if ( isset( $this->vertical ) && 'ENT' !== $this->vertical ) {
			array_unshift( $search_phrase, $this->vertical );
		}

		$search_phrase = array_map( function ( $item ) {
			if ( 'false' == $item || false == $item ) {
			    return false;
			}

			// Entertainment is a special snowflake and needs extra filtering for more concise results.
			$ent = 'ENT' === $this->vertical ? $this->vertical : '';

			return sprintf( '(%s) %s', trim( $item ), $ent );
		}, $search_phrase );

		// Strip out any false array values
		$search_phrase = array_filter( $search_phrase );

		return implode( $search_phrase, '' );
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

		$download_url = $attachment['fullUrl'];

		/**
		 * This condition is in place until USAT can fix the endpoint for downloading SIPA images
		 *
		 * Implemented 2-17-2017
		 */
		if ( isset( $attachment['isSipa'] ) && $attachment['isSipa'] == 1 ) {
			$download_url = 'http://www.usatsimg.com/api/downloadSipa/?imageID=' . $attachment['uniqueId'];
		}

		return array(
			'id' => $attachment['imgId'],
			'title' => htmlspecialchars( $attachment['headline'] ),
			'filename' => htmlspecialchars( $attachment['imgId'] . ' ' . $attachment['headline'] ),
			'caption' => htmlspecialchars( $attachment['caption'] ),
			'description' => htmlspecialchars( $attachment['caption'] ),
			'type' => 'image',
			'sizes' => array(
				'thumbnail' => array(
					'url' => $attachment['thumbUrl'],
					'width' => $attachment['width'],
					'height' => $attachment['height'],
				),
				'full' => array(
					'url' => $attachment['fullUrl'],
					'width'  => $attachment['width'],
					'height' => $attachment['height'],
				),
				'large'   => array(
					'url' => $attachment['previewUrl'],
					'width'  => $attachment['width'],
					'height' => $attachment['height'],
				),
			),
			'download_uri' => $download_url,
			'max_width' => $attachment['width'],
			'max_height' => $attachment['height'],
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
		if ( stristr($data['url'], $this->directory ) ) {
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

	/**
	 * Set vertical chosen from the front end.
	 *
	 * @return string|boolean
	 */
	private function determine_vertical() {
		$vertical = false;
		if ( isset( $_REQUEST['query']['vertical'] ) ) {
			$vertical = $_REQUEST['query']['vertical'];
		}

		return $vertical;
	}

	/**
	 * Set SIPA image param if the vertical is entertainment
	 *
	 * @return string
	 */
	private function get_sipa_value() {
		if ( 'ENT' === $this->vertical ) {
			return '1';
		}
		return '0';
	}
}