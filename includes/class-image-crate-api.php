<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Image_Crate_Api Class
 *
 * Handle returned data for image source
 *
 * @version  0.1.1
 * @package  WP_Image_Crate
 * @author   justintucker
 */
class Image_Crate_Api {

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
	 * Setup image source calls and filters
	 *
	 * @param $plugin Image_Crate
	 */
	public function __construct( $plugin ) {
		$this->key = USAT_API_KEY;
		$this->secret = USAT_API_SECRET;
		$this->api_url = "http://www.usatodaysportsimages.com/api/searchAPI/";
		$this->directory = 'usat-images';

		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'update_media_modal_file_refs' ), 99, 1 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'set_image_path' ) );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'update_scrset_attr' ), 10, 1 );
		add_filter( 'image_send_to_editor', array( $this, 'send_to_editor' ), 10, 1 );
		add_filter( 'image_get_intermediate_size', array( $this, 'set_image_editor_thumb_url' ), 10, 1 );
		add_filter( 'image_crate_controller_title', array( $this, 'filter_controller_title') );
		add_action( 'admin_init', array( $this, 'register_fields' ) );
		add_filter( 'plugin_action_links_'. $plugin->plugin_name , array( $this, 'add_action_links' ) );
	}

	/**
	 * Filter page title in media modal.
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function filter_controller_title( $title ) {
		$title['page_title'] = 'USA Today Sports Images';
		return $title;
	}

	/**
	 * Register default field.
	 */
	public function register_fields () {
		register_setting( 'general', 'image_crate_default_search', 'esc_attr' );
		add_settings_field(
			'image_crate_default_search_term',
			'<label for="image_crate_default_search_term">' . __( 'Image Crate Default Term', 'image_crate_default_search' ) . '</label>',
			array( $this, 'fields_html' ),
			'general'
		);
	}

	/**
	 * Output form field markup
	 */
	public function fields_html() {
		$value = get_option( 'image_crate_default_search', '' );
		echo '<input type="text" id="image_crate_default_search_term" name="image_crate_default_search" value="' . esc_attr( $value ) . '" />';
	}

	/**
	 * Set default query
	 *
	 * @return string
	 */
	public function set_default_query() {
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
	 * Add settings link to plugin list page
	 *
	 * @param $links Current plugin links
	 *
	 * @return array Plugin menu data
	 */
	public function add_action_links( $links ) {
		$links[] = '<a href="' . admin_url( 'options-general.php#image_crate_default_search_term' ) . '">Settings</a>';

		return $links;
	}

	/**
	 * Get the default query
	 *
	 * @return string
	 */
	public function get_default_query() {
		return $this->set_default_query();
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
		$mode                 = 'phrase'; // options include (any, all, phrase, bool)
		$offset               = $page;
		$terms                = strtolower( $phrase ) . '*'; // asterisk is needed for wildcard searches on usatoday images

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

		// todo: fix array error catching for secondary array_map param
		$images = array_map( function ( $index ) {
			return $index[0];
		}, $response_body['results']['item'] );

		return $images;
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
			'download_uri' => $attachment['fullUrl'],
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
	 * Set up image path to point to new getty image location
	 *
	 * On the admin side, WordPress filters the output of the url string used to display the
	 * image. That is altered here.
	 *
	 * @param   string|array $image_path Default image source
	 *
	 * @return  string|array Updated image src to include 'getty-images'
	 */
	public function set_image_path( $image_path ) {
		// Make sure image path url is a string
		if ( is_array( $image_path ) ) {
			$image_path_url = $image_path[0];
		} else {
			$image_path_url = $image_path;
		}

		// Check for old multisite directory structure
		if ( ! stristr( $image_path_url, '/files/' . $this->directory . '/' ) ) {
			$search_str  = '/\/sites\/\d*\/' . $this->directory . '\//';
			$replace_str = '/' . $this->directory . '/';
		} else {
			$search_str  = '/\/files\/' . $this->directory . '\//';
			$replace_str = '/wp-content/uploads/' . $this->directory . '/';
		}

		if ( is_array( $image_path ) ) {
			$image_path[0] = preg_replace( $search_str, $replace_str, $image_path[0] );
		} else {
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
	 * Update srcset urls to point to getty images global folder location
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
	 * @return  string|array Updated markup with 'getty-images' in src
	 */
	public function send_to_editor( $html ) {
		return $this->set_image_path( $html );
	}
}