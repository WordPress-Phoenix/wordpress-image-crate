<?php

namespace ImageCrate\Admin\Providers;


use ImageCrate\Admin\Import;
use ImageCrate\Service\Imagn;

/**
 * Class Provider_Imagn
 * @package ImageCrate\Admin\Providers
 */
class Provider_Imagn extends Provider {

	/**
	 * The provider name
	 */
	const PROVIDER = 'imagn';
	
	/**
	 * If image provider should be tracked.
	 */
	const TRACKING = true;

	/**
	 * The directory images will be saved to.
	 */
	const CUSTOM_DIRECTORY = 'imagn-images';
	
	/**
	 * The provider service
	 *
	 * @var Imagn
	 */
	private $service;

	/**
	 * Provider_Imagn constructor.
	 */
	public function __construct() {
		$this->service = new Imagn();
	}

	/**
	 * Retrieve image data from provider.
	 *
	 * @param array $query Data passed from client
	 *
	 * @return array
	 */
	public function fetch( $query ) {

		$search_term    = ( ! empty ( $query['search'] ) ? $query['search'] : '' );
		$paged          = ( ! empty ( $query['paged'] ) ? intval( $query['paged'] ) : 1 );
		$posts_per_page = ( ! empty ( $query['posts_per_page'] ) ? intval( $query['posts_per_page'] ) : 40 );

		$images = $this->service->fetch( $search_term, $paged, $posts_per_page );

		return $images;

	}

	/**
	 * Download the selected image
	 *
	 * @param array $query Data passed from client.
	 *
	 * @return array
	 */
	public function download( $query ) {

		$import       = new Import( self::TRACKING );
		$remote_id    = $query['id'];

		$image_url = $this->service->download_single( $remote_id );

		$attachment = $import->image(
			$image_url,
			$remote_id,
			self::CUSTOM_DIRECTORY,
			self::PROVIDER
		);

		if ( ! $attachment ) {
			wp_send_json_error();
		}

		$attachment_prepared = wp_prepare_attachment_for_js( $attachment );

		if ( ! $attachment_prepared ) {
			wp_send_json_error();
		}

		return $attachment_prepared;

	}

}