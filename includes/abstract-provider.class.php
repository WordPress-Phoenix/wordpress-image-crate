<?php
/**
 * Abstract Trapper_Keeper Image Provider Class
 *
 * Implemented by abstract class factory pattern.
 *
 * @version  0.1.1
 * @package  WP_Trapper_Keeper
 * @category Abstract Class
 * @author   justintucker
 */
abstract class Image_Provider {

	protected $tab_name = '';


	// add upload tabs
	public function set_tab_name( $tabs ) {
		$tabs[ get_called_class() . '_Tab' ] = $this->tab_name;
		return $tabs;
	}

	public function add_filters() {
		//add_filter( 'media_upload_tabs', array($this, 'set_tab_name') );
		add_filter( 'media_view_strings', array( $this, 'remove_media_tab' ) );

		add_action('print_media_templates', array( $this, 'output_template' ) );
	}

	public function remove_media_tab( $strings ) {
		unset( $strings["insertFromUrlTitle"] );
		return $strings;
	}

	public function enqueue_scripts() {

		//wp_enqueue_media();

		wp_enqueue_script(
			'provider-admin',
			TK_URL . '/assets/js/image-provider-admin.js',
			array( 'media-editor', 'media-views', 'jquery' ),
			'2.0.1',
			'all'
		);

		//// Load 'terms' into a JavaScript variable that collection-filter.js has access to
		//wp_localize_script( 'provider-admin', 'MediaLibraryTaxonomyFilterData', array(
		//	'terms' => get_terms( 'post_tag', array( 'hide_empty' => false ) ),
		//) );
		//// Overrides code styling to accommodate for a third dropdown filter
		//add_action( 'admin_footer', function () {
		//	?>
		<!--	<style>-->
		<!--		.media-modal-content .media-frame select.attachment-filters {-->
		<!--			max-width: -webkit-calc(33% - 12px);-->
		<!--			max-width: calc(33% - 12px);-->
		<!--		}-->
		<!--	</style>-->
		<!--	--><?php
		//} );

	}

	public function run() {
		$this->add_filters();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'media_view_strings', array($this, 'custom_media_string'), 10, 2 );



	}

	public function custom_media_string( $strings, $post ) {
		$strings['imageProviderTitle'] = __( 'Insert External Image', 'custom' );
		$strings['imageProviderButton']    = __( 'Insert Image', 'custom' );

		return $strings;
	}

	/**
	 * @return string
	 */
	public function output_template() {
		?>
		<script type="text/html" id="tmpl-api-image-search">
			<h2><input type="text" /></h2>
		</script>
		<?php
	}



}