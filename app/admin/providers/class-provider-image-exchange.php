<?php

namespace ImageCrate\Admin\Providers;


use ImageCrate\Admin\Import;
use ImageCrate\Service\Image_Exchange;

class Provider_Image_Exchange extends Provider {

	/**
	 * The provider name
	 */
	const PROVIDER = 'image_exchange';

	/**
	 * If image provider should be tracked.
	 */
	const TRACKING = true;

	/**
	 * The directory images will be saved to.
	 */
	const CUSTOM_DIRECTORY = 'image-exchange';

	/**
	 * The provider service
	 *
	 * @var Image_Exchange
	 */
	private $service;

	/**
	 * Provider_Getty_Images constructor.
	 */
	public function __construct() {
		$this->service = new Image_Exchange();
	}

	/**
	 * Retrieve image data from provider.
	 *
	 * @param array $query Data passed from client
	 *
	 * @return array
	 */
	public function fetch( $query ) {

		$vertical       = ( ! empty ( $query['vertical'] ) ? strtolower( $query['vertical'] ) : '' );
		$paged          = ( ! empty ( $query['paged'] ) ? intval( $query['paged'] ) : 1 );
		$posts_per_page = ( ! empty ( $query['posts_per_page'] ) ? intval( $query['posts_per_page'] ) : 40 );
		$search         = ( ! empty ( $query['search'] ) ? strtolower( $query['search'] ) : '' );

		$images = $this->service->fetch( $vertical, $paged, $posts_per_page, $search );

		return $images;

	}

	/**
	 * Download the selected image
	 *
	 * @return mixed
	 */
	public function download( $query ) {

		$import    = new Import( self::TRACKING );
		$image_url = $query['download_url'];
		$remote_id = 'ie_' . $query['id'];
		$caption = $query['caption'];

		$attachment = $import->image(
			$image_url,
			$remote_id,
			self::CUSTOM_DIRECTORY,
			self::PROVIDER,
            $caption
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