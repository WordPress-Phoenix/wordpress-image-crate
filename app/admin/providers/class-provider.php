<?php

namespace ImageCrate\Admin\Providers;


abstract class Provider {

	/**
	 * Name of directory to store provider images.
	 *
	 * @var $directory string
	 */
	public $directory;

	/**
	 * Retrieve image data from provider.
	 *
	 * @param array $query Query params used for fetch images
	 *
	 * @return mixed
	 */
	abstract function fetch( $query );

	/**
	 * Download the selected image
	 *
	 * @return mixed
	 */
	abstract function download( $query );

	/**
	 * Set the custom image directory to store images.
	 *
	 * @link
	 *
	 * @param   array $upload Filtered upload dir locations
	 *
	 * @return  array Filtered upload dir locations
	 */
	public function set_upload_dir( $upload ) {
		$upload['subdir']  = '/' . $this->directory . $upload['subdir'];
		$upload['basedir'] = WP_CONTENT_DIR . '/uploads';
		$upload['baseurl'] = content_url() . '/uploads';
		$upload['path']    = $upload['basedir'] . $upload['subdir'];
		$upload['url']     = $upload['baseurl'] . $upload['subdir'];

		return $upload;
	}
}