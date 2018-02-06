<?php

namespace ImageCrate\Admin;

use WPOP\V_3_1 as Opts;

class Settings {
	public $installed_dir;
	public $installed_url;
	public $plugin_settings;
	public $option_prefix = 'ic_';

	function __construct( $dir, $url ) {
		$this->installed_dir = $dir;
		$this->installed_url = $url;
		$this->setup_imagecrate_options();
	}

	function setup_imagecrate_options() {
		$sections = array(
			'services' => $this->services_section(),
			'getty'    => $this->getty_section(),
		);

		$this->plugin_settings = new Opts\page(
			$this->image_crate_panel_config(),
			$sections
		);

		// initialize_panel() is a function in the opt panel Container class
		$this->plugin_settings->initialize_panel();
	}

	function image_crate_panel_config() {
		return array(
			'id'             => 'wp-image-crate-options',
			'parent_page_id' => is_multisite() ? 'settings.php' : 'options-general.php',
			'api'            => is_multisite() ? 'network' : 'site',
			'page_title'     => 'WordPress Image Crate',
			'menu_title'     => 'Image Crate',
			'dashicon'       => 'dashicons-portfolio'
		);
	}

	function services_section() {
		return array(
			'label'    => 'Services',
			'dashicon' => 'dashicons-networking',
			'parts'    => array(
				$this->option_prefix . 'enable_getty_images' => array(
					'label' => 'Enable Getty Images',
					'part'  => 'toggle_switch',
				),
			)
		);
	}

	function getty_section() {
		return array(
			'label'    => 'Getty',
			'dashicon' => 'dashicons-admin-network',
			'parts'    => array(
				$this->option_prefix . 'getty_api_key'    => array(
					'label' => 'Getty API Key',
					'part'  => 'password',
				),
				$this->option_prefix . 'getty_api_secret' => array(
					'label' => 'Getty API Secret Key',
					'part'  => 'password',
				),
			)
		);
	}

}
