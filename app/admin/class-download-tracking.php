<?php

namespace ImageCrate\Admin;

/**
 * Class Download_Tracking
 * @package ImageCrate\Admin
 */
class Download_Tracking {

	/**
	 * The attachment post from the current site
	 *
	 * @var \WP_Post
	 */
	private $post_data;

	/**
	 * The image provider
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * The master site's ID
	 *
	 * @var int
	 */
	private $master_site_id;

	/**
	 * The currents sites's ID
	 *
	 * @var int
	 */
	private $current_site_id;

	/**
	 * Track image downloads as post on master site
	 *
	 * @param \WP_Post $post_data The attachment from current site.
	 * @param string   $provider  The image provider.
	 *
	 * @return int The master site tracking post ID.
	 */
	public function track_attachment( \WP_Post $post_data, string $provider ) {

		$master_site = get_current_site();

		$this->post_data       = $post_data;
		$this->provider        = $provider;
		$this->master_site_id  = $master_site->id;
		$this->current_site_id = get_current_blog_id();

		$network_post_id = $this->check_network_post_exists();

		if ( $network_post_id ) {
			return $network_post_id;
		}

		$network_post_id = $this->create_network_post();

		try {
			if ( is_wp_error( $network_post_id ) ) {
				throw new \Exception( $network_post_id->get_error_message() );
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e );

			return 0;
		}

		return $network_post_id;

	}

	/**
	 * Create a tracking post on the master site
	 *
	 * @return int|\WP_Error
	 */
	private function create_network_post() {

		$current_site_url    = site_url();
		$post_data           = $this->post_data;
		$attachment_metadata = get_post_meta( $post_data->ID, '_wp_attachment_metadata', true );

		switch_to_blog( $this->master_site_id );

		// Replace the GUID with the master site url
		$guid = $post_data->guid;
		$guid = str_replace( $current_site_url, site_url(), $guid );

		$new_post = [
			'post_author'    => 1,
			'post_content'   => $post_data->post_content,
			'post_title'     => $post_data->post_title,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_name'      => $post_data->post_name,
			'post_mime_type' => $post_data->post_mime_type,
			'guid'           => $guid,
		];

		// Insert the new attachment post in blog id 1 for global tracking
		$post_id      = wp_insert_post( $new_post, true );
		if ( is_wp_error( $post_id ) ) return $post_id;

		$network_post = get_post( $post_id );
		$post_name    = $network_post->post_name;

		$usage_count = [
			'api_hit_amount'    => 1,
			'attached_amount'   => 0,
			'file_id'           => $post_name,
			'attached_to_posts' => []
		];

		add_post_meta( $post_id, "{$this->provider}_usage", $usage_count, true );
		add_post_meta( $post_id, '_wp_attachment_metadata', $attachment_metadata, true );

		switch_to_blog( $this->current_site_id );

		return $post_id;
	}

	/**
	 * Check if attachment post exists in master blog table
	 *
	 * This means that the image has been used already on one for the sites.
	 *
	 * @return int The attachment ID
	 */
	private function check_network_post_exists() {

		$post_name = $this->post_data->post_name;

		switch_to_blog( $this->master_site_id );

		global $wpdb;
		$attachment_id = $wpdb->get_col(
			$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name='%s';", $post_name )
		);

		if ( ! empty( $attachment_id[0] ) ) {
			$this->increment_network_post_hit( $attachment_id[0] );
		}

		switch_to_blog( $this->current_site_id );

		if ( ! empty( $attachment_id[0] ) ) {
			return $attachment_id[0];
		}

		return 0;
	}

	/**
	 * Increment the api hit count
	 *
	 * Must be run on the master site. It must be inside a switch_to_blog()
	 * 
	 * @param string|int $post_id Network post ID
	 */
	private function increment_network_post_hit( $post_id ) {

		$usage = get_post_meta( $post_id, "{$this->provider}_usage", true );

		if ( ! is_array( $usage ) ) {
			$usage = [];
		}

		$usage = $this->transform_legacy_getty_tracking( $usage );
		
		$usage['api_hit_amount'] = intval( $usage['api_hit_amount'] ) + 1;

		update_post_meta( $post_id, "{$this->provider}_usage", $usage );

	}

	/**
	 * Transforms forms the legacy getty images tracking to new data format.
	 * 
	 * @param array $usage The existing meta data.
	 *
	 * @return array
	 */
	private function transform_legacy_getty_tracking( array $usage ) {
		
		if ( isset( $usage['api_hit_amount'] ) ) {
			return $usage;
		}

		$usage_count = [
			'api_hit_amount'    => ( $usage['getty_api_hit_amount'] ? $usage['getty_api_hit_amount'] : 1 ),
			'attached_amount'   => ( $usage['getty_attached_amount'] ? $usage['getty_attached_amount'] : 0 ),
			'file_id'           => ( $usage['getty_file_id'] ? $usage['getty_file_id'] : '' ),
			'attached_to_posts' => ( $usage['getty_to_posts'] ? $usage['getty_to_posts'] : [] )
		];
		
		return $usage_count;
		
	}

}