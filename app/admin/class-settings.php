<?php

namespace ImageCrate\Admin;

use WPOP\V_2_8 as Opts;

class Settings {
	public $installed_dir;
	public $installed_url;
	public $provider_settings;
	public $option_prefix = 'ic_';

	function __construct( $dir, $url ) {
		$this->installed_dir = $dir;
		$this->installed_url = $url;
		$this->setup_provider_options();
	}

	function setup_provider_options() {

		$pre_ = $this->option_prefix;
		$provider_settings = new Opts\page( [
			'parent_id'  => 'settings.php',
			'id'         => 'image-provider-settings',
			'page_title' => 'Image Crate Provider Settings' .
			                ' <small style="font-size:0.66rem;"><code>wordpress-image-crate</code></small>',
			'menu_title' => 'Image Providers',
			'dashicon'     => 'dashicons-images-alt',
			'network_page' => true
		] );

		$this->provider_settings = ( $provider_settings );

		// setup sections
		$this->provider_settings->add_part(
			$general = new Opts\section(
				'general', array(
					'title'    => 'General',
					'dashicon' => 'dashicons-admin-generic',
				)
			)
		);
		$this->provider_settings->add_part(
			$getty_images = new Opts\section(
				'brand-config', array(
					'title'    => 'Getty Images',
					'dashicon' => 'dashicons-cloud',
				)
			)
		);

		/**
		 * Getty Configuration Fields
		 */
		ob_start(); ?>
			<style type="text/css">
				#getty-notes {  padding: 2rem;  }
				#getty-notes a {  color: #fff;  text-decoration: none;  }
				#getty-notes a:hover {  color: #00A7E1;  }
			</style>
			<div id="getty-notes" class="wp-ui-primary">
				<strong style="font-size:1.5rem;">Getty Images Developer API</strong>
				<p style="font-size:1rem;">Use Getty Images in the WordPress Media Modal (coming soon to the Media
					Library) to insert Getty Images into WordPress using Getty API credentials. Uses Oauth2 to
					create access tokens that expire every 30 minutes, and then the <code>/search/images</code> and
					<code>/images/:id</code> endpoints to sideload images into the WordPress Media Library.
				</p>
				<h4><span class="dashicons dashicons-media-code"></span> Getty API Documentation for Reference</h4>
				<ul style="padding:0 2rem;list-style-type:square;">
					<li><a href="http://developers.gettyimages.com/api/docs/v3/search/images/get/">Search Getty Images</a></li>
					<li><a href="http://developers.gettyimages.com/api/docs/v3/search/images/get/">Get Single Image</a></li>
					<li><a href="http://developers.gettyimages.com/api/docs/v3/images/id/similar/get/">Get Related Images by Getty Image UID</a></li>
					<li><a href="http://developers.gettyimages.com/api/docs/v3/oauth2.html#client-credentials-flow">Oauth2 Token Authorization: Using Getty Client Credentials Flow</a></li>
				</ul><br>
				<span class="dashicons dashicons-lock"></span> <code>Credentials stored with 256-bit encryption.</code>
			</div>

		<?php
		$getty_images->add_part(
			$brand_url_staging = new Opts\include_markup(
				$pre_ . 'getty_into_panel', array(), ob_get_clean()
			)
		);
		$getty_images->add_part(
			$brand_url_staging = new Opts\toggle_switch(
				$pre_ . 'getty_enabled', array(
					'label' => 'Enable Service',
					'value' => 'on'
				)
			)
		);
		$getty_images->add_part(
			$brand_url_staging = new Opts\text(
				$pre_ . 'getty_api_key', array(
					'label' => 'API Key',
				)
			)
		);
		$getty_images->add_part(
			$brand_url_staging = new Opts\text(
				$pre_ . 'getty_api_secret', array(
					'label' => 'Secret Key',
				)
			)
		);

		$this->provider_settings->initialize_panel();
	}

}
