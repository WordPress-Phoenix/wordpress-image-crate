<?php
/**
 * Plugin Name: WP Trapper Keeper
 * Plugin URI: https://github.com/WordPress-Phoenix/wordpress-trapper-keeper
 * Description: Add image providers to the WordPress media modal.
 * Author: justintucker
 * Version: 0.1.1
 * Author URI: http://github.com/justintucker
 * License: GPL V2
 * Text Domain: trapper_keeper
 *
 * GitHub Plugin URI: https://github.com/WordPress-Phoenix/wordpress-trapper-keeper
 * GitHub Branch: master
 *
 * @package  WP_Trapper_Keeper
 * @category plugin
 * @author justintucker
 * @internal Plugin derived from https://github.com/scarstens/worpress-plugin-boilerplate-redux
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Trapper_Keeper' ) ) {

	define( 'TK_VERSION', '1.0.0' );
	define( 'TK_URL', plugins_url( basename( __DIR__ ) ) );
	define( 'TK_PATH', dirname( __FILE__ ) );
	define( 'TK_INC', TK_PATH . '/includes' );

	class Trapper_Keeper {

		public $debug;
		public $installed_dir;
		public $installed_url;
		public $admin;
		public $modules;
		public $network;
		public $current_blog_globals;
		public $detect;

		/**
		 * Construct the plugin object
		 *
		 * @since   0.1
		 */
		public function __construct() {

			// hook can be used by mu plugins to modify plugin behavior after plugin is setup
			do_action( get_called_class() . '_preface', $this );
			define( 'TRAPPER_URL', plugins_url( basename( __DIR__ ) ) );

			//simplify getting site options with custom prefix with multisite compatibility
			if ( ! function_exists( 'get_custom_option' ) ) {
				// builds  the function in global scope
				function get_custom_option( $s = '', $network_option = false ) {
					if ( $network_option ) {
						return get_site_option( SITEOPTION_PREFIX . $s );
					} else {
						return get_option( SITEOPTION_PREFIX . $s );
					}
				}
			}

			// Always load libraries first
			$this->load_libary();

			// configure and setup the plugin class variables
			$this->configure_defaults();

			// define globals used by the plugin including bloginfo
			$this->defines_and_globals();

			// Load /includes/ folder php files
			$this->load_classes();

			// initialize
			add_action( 'init', array( $this, 'init' ) );

			// init for use with logged in users, see this::authenticated_init for more details
			add_action( 'init', array( $this, 'authenticated_init' ) );

			// uncomment the following to setup custom widget registration
			//add_action( 'widgets_init', array( $this, 'register_custom_widget' ) );

			// hook can be used by mu plugins to modify plugin behavior after plugin is setup
			do_action( get_called_class() . '_setup', $this );

		} // END public function __construct

		/**
		 * Initialize the plugin - for public (front end)
		 *
		 * @since   0.1
		 * @return  void
		 */
		public function init() {

			do_action( get_called_class() . '_before_init' );

			if ( class_exists( 'Getty_Image_Provider' ) ) {
				new Getty_Image_Provider;
				new USAToday_Image_Provider;
			}

//			if ( class_exists( 'sm_options_page' ) ) {
//				// create admin site options page to allow gui configuration of plugin
//				$plugin_options = new sm_options_page(array('theme_page' => TRUE, 'parent_id' => 'themes.php', 'page_title' => 'Configure Theme Customizations', 'menu_title' => 'Theme Options','id' => 'whitelabel-appearance-options'));
//				$plugin_options->add_part($plugin_options_section_1 = new sm_section('section_1', array('title'=>'Section 1')) );
//				$plugin_options_section_1->add_part($site_favicon = new sm_media_upload('website_favicon', array('label'=>'Favicon', 'description'=>'Website icon to be used for your website. Must be 16x16 or 32x32 and .ico format. Leaving this field blank will load the favicon.ico file from the themes folder or fallback to the generic favicon.ico file.')));
//				$plugin_options->add_part($plugin_options_section_2 = new sm_section('section_2', array('title'=>'Page Meta')) );
//				$plugin_options_section_2->add_part($genTag = new sm_checkbox('page_meta_generator', array('label'=>'Include Generator Meta Tag', 'value'=>'true', 'classes'=>array('onOffSwitch') )));
//			}

			do_action( get_called_class() . '_after_init' );
		}

		/**
		 * Initialize the plugin - for admin (back end)
		 * You would expected this to be handled on action admin_init, but it does not properly handle
		 * the use case for all logged in user actions. Always keep is_user_logged_in() wrapper within
		 * this function for proper usage.
		 *
		 * @since   0.1
		 * @return  void
		 */
		public function authenticated_init() {
			if ( is_user_logged_in() ) {
				//Uncomment below if you have created an admin folder for admin only plugin partials
				//Change the name below to a custom name that matches your plugin to avoid class collision
				//require_once( $this->installed_dir . '/admin/Main_Admin.class.php' );
				//$this->admin = new Main_Admin( $this );
				//$this->admin->init();
			}
		}

		/**
		 * Activate the plugin
		 *
		 * @since   0.1
		 * @return  void
		 */
		public static function activate() {

		}

		/**
		 * Deactivate the plugin
		 *
		 * @since   0.1
		 * @return  void
		 */
		public static function deactivate() {

		}

		/**
		 * Loads PHP files in the includes folder
		 * @TODO: Move to using spl_autoload_register
		 *
		 * @since   0.1
		 * @return  void
		 */
		protected function load_classes() {
			// load all files with the pattern *.class.php from the includes directory
			foreach ( glob( dirname( __FILE__ ) . '/includes/*.class.php' ) as $class ) {
				require_once $class;
				//$this->modules->count ++;
			}
		}

		/**
		 * Load all files from /lib/ that match extensions like filename.class.php
		 * @TODO: Move to using spl_autoload_register
		 *
		 * @since   0.1
		 * @return  void
		 */
		protected function load_libary() {
			// load all files with the pattern *.php from the directory inc
			foreach ( glob( dirname( __FILE__ ) . '/lib/*.class.php' ) as $class ) {
				require_once $class;
			}
		}

		protected function defines_and_globals() {
			/*
			 * Uncomment parts of this section to enable these features
			 */

			// confirm PHP_TAB exists for use in printing
//			if ( ! defined( 'PHP_TAB' ) ) {
//				define( 'PHP_TAB', "\t" );
//			}

			// extend current blog global variable with all blog details
//			$GLOBALS['current_blog'] = new stdClass();
//			if ( function_exists( 'get_blog_details' ) ) {
//				$GLOBALS['current_blog']              = get_blog_details();
//				$GLOBALS['current_blog']->description = get_bloginfo( 'description' );
//			} else {
//				$GLOBALS['current_blog']->domain = $_SERVER['SERVER_NAME'];
//			}
//
//			$this->current_blog_globals = $GLOBALS['current_blog'];
//
//			// setup network url and fallback in case siteurl is not defined
//			if ( ! defined( 'WP_NETWORKURL' ) && is_multisite() ) {
//				define( 'WP_NETWORKURL', network_site_url() );
//			} elseif ( ! defined( 'WP_NETWORKURL' ) ) {
//				define( 'WP_NETWORKURL', get_site_url() );
//			}
//			$this->network = WP_NETWORKURL;
		}

		protected function configure_defaults() {
			// Setup plugins global params
			// TODO: customize with your plugins custom prefix (usually matches your text domain)
			if ( ! defined('SITEOPTION_PREFIX') ) {
				define( 'SITEOPTION_PREFIX', 'trapper_keeper_option_' );
			}
			$this->modules        = new stdClass();
			$this->modules->count = 0;
			$this->installed_dir  = dirname( __FILE__ );
			$this->installed_url  = plugins_url( '/', __FILE__ );
		}

		/**
		 * This function is used to make it quick and easy to programatically do things only on your development
		 * domains. Typical usage would be to change debugging options or configure sandbox connections to APIs.
		 */
		public static function is_dev() {
			// catches dev.mydomain.com, mydomain.dev, wpengine staging domains and mydomain.staging
			return (bool) ( stristr( WP_NETWORKURL, '.dev' ) || stristr( WP_NETWORKURL, '.wpengine' ) || stristr( WP_NETWORKURL, 'dev.' ) || stristr( WP_NETWORKURL, '.staging' ) );
		}

	} // END class
} // END if(!class_exists())

/**
 * Build and initialize the plugin
 */
if ( class_exists( 'Trapper_Keeper' ) ) {
	// Installation and un-installation hooks
	register_activation_hook( __FILE__, 'activate' );
	register_deactivation_hook( __FILE__, 'deactivate' );

	// instantiate the plugin class, which should never be instantiated more then once
	global $main_plugin;
	$main_plugin = new Trapper_Keeper();
}