<?php

namespace ImageCrate\Admin;


/**
 * Import Class
 *
 * Handle returned data for image source.
 *
 * @version  3.0.0
 * @package  WP_Image_Crate
 */
final class Import {

	/**
	 * @var string The directory to download image to.
	 */
	public $directory;

	/**
	 * @var Download_Tracking The tracking class.
	 */
	private $tracking;

	/**
	 * Import constructor.
	 *
	 * @param bool $tracked Whether image should be tracked
	 *                      in main blog posts table.
	 */
	public function __construct( $tracked = false ) {

		if ( $tracked ) {
			$this->tracking = new Download_Tracking();
		}

	}

	/**
	 * Import image from an url
	 *
	 * @param string     $download_url     Url to download image file.
	 * @param string|int $remote_id        A unique id from external source used for the post_name.
	 * @param string     $custom_directory Where to download the image to.
	 * @param string     $provider         The image provider.
	 *
	 * @return bool|int|object
	 */
	public function image( $download_url, $remote_id, $custom_directory, string $provider ) {

		$this->directory = $custom_directory;

		$file_array = [];

		$post_name = strtolower( $remote_id );
		$id_exists = $this->check_attachment( $post_name );

		// filename will determine if download will occur
		if ( $id_exists > 0 ) {
			$media_post = get_post( $id_exists );

			if ( $this->tracking ) {
				$this->tracking->track_attachment( $media_post, $provider );
			}

			return $id_exists;
		}

		// place the images in a custom directory
		add_filter( 'upload_dir', array( &$this, 'set_upload_dir' ) );

		$file_array['tmp_name'] = $this->download( $download_url );

		if ( ! $file_array['tmp_name'] ) {
			return false;
		}

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_array['tmp_name'], $matches );

		if ( ! $matches ) {
			unlink( $file_array['tmp_name'] );

			return false;
		}

		$file_array['name'] = basename( $matches[0] );

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$api_image = $file_array['name'];

		$image_type         = pathinfo( $api_image );
		$file_name          = basename( $api_image, '.' . $image_type['extension'] );
		$file_array['name'] = str_replace( $file_name, $post_name, $api_image );

		add_filter( 'wp_insert_attachment_data', [ $this, 'save_image_caption' ] );

		// Do the validation and storage stuff
		$id = media_handle_sideload( $file_array, 0, null, [ 'post_name' => $remote_id ] );

		remove_filter( 'wp_insert_attachment_data', [ $this, 'save_image_caption'] );

		$this->delete_file( $file_array['tmp_name'] );

		if ( is_wp_error( $id ) ) {
			// TODO: New Relic and AJAX error
			return false;
		}

		add_post_meta( $id, 'image_provider', $provider, true );

		$media_post = get_post( $id );

		if ( $this->tracking ) {
			$this->tracking->track_attachment( $media_post, $provider );
		}

		return $media_post;
	}

	/**
	 * Download a file by its URL
	 *
	 * @param $url
	 *
	 * @return bool|string
	 * @internal param string $id
	 *
	 */
	private function download( $url ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$file = download_url( $url );

		if ( is_wp_error( $file ) ) {
			return false;
		}

		// Added functionality to deal with image without extension
		$tmp_ext = pathinfo( $file, PATHINFO_EXTENSION );

		// Get the real image extension
		$file_ext = image_type_to_extension( exif_imagetype( $file ) );

		// Replace extension of basename file
		$new_file = basename( $file, ".$tmp_ext" ) . $file_ext;

		// Replace old file with new file in complete path location
		$new_file = str_replace( basename( $file ), $new_file, $file );

		// Rename from .tpm to actual file format
		rename( $file, $new_file );

		$file = $new_file;

		$image_object = new \Imagick( $file );

		try {
			if ( $image_object ) {
				$original_width  = $image_object->getImageWidth();
				$original_height = $image_object->getImageHeight();

				$landscape_width = 3200;
				$portrait_width  = 1600;

				$landscape = ( $original_width > $original_height );
				if ( $landscape && $landscape_width < $original_width ) {
					$image_object->scaleImage( $landscape_width, 0 );
					$image_object->writeImage( $file );
				} elseif ( $portrait_width < $original_width ) {
					$image_object->scaleImage( $portrait_width, 0 );
					$image_object->writeImage( $file );
				}
			}
		} catch ( \Exception $e ) {
			if ( function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $e );
			}
			error_log( $e->getMessage() );
		} finally {
			unset( $image_object );
		}

		return $file;
	}

	/**
	 * Delete a file
	 *
	 * @param  string $filepath
	 *
	 * @return bool
	 */
	private function delete_file( $filepath ) {
		return is_readable( $filepath ) ? @unlink( $filepath ) : false;
	}

	/**
	 * Check if attachment exists on current site.
	 *
	 * If attachment exists, update it's timestamp so that
	 * it appears at the beginning of the media library.
	 *
	 * @param string $post_name The is a unique ID provided by external service.
	 *
	 * @return int Post attachment id
	 */
	public function check_attachment( $post_name ) {

		global $wpdb;
		$attachment_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name='%s';", $post_name ) );

		if ( ! empty( $attachment_id[0] ) ) {
			$date   = date( 'Y-m-d h:i:s' );
			$update = [
				'ID'                => $attachment_id[0],
				'post_date'         => $date,
				'post_date_gmt'     => get_gmt_from_date( $date, $format = 'Y-m-d H:i:s' ),
				'post_modified'     => $date,
				'post_modified_gmt' => get_gmt_from_date( $date, $format = 'Y-m-d H:i:s' ),
			];

			return wp_update_post( $update );
		}

		return 0;

	}

	/**
	 * Temporarily change upload directory for downloading getty images
	 *
	 * @param array $upload Filtered upload dir locations
	 *
	 * @return array Filtered upload dir locations
	 */
	public function set_upload_dir( $upload ) {
		$upload['subdir']  = '/' . $this->directory . $upload['subdir'];
		$upload['basedir'] = WP_CONTENT_DIR . '/uploads';
		$upload['baseurl'] = content_url() . '/uploads';
		$upload['path']    = $upload['basedir'] . $upload['subdir'];
		$upload['url']     = $upload['baseurl'] . $upload['subdir'];

		return $upload;
	}

	/**
	 * Add an image caption to the attachment post.
	 *
	 * @param array $data The post data to be inserted as attachment
	 *
	 * @return array
	 */
	public function save_image_caption( $data ) {
		$data['post_excerpt'] = ( $data['post_content'] ? $data['post_content'] : '' );

		return $data;
	}

}