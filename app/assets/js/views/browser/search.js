/**
 * wp.media.view.ImageCrateSearch
 *
 * imagecrate.default_search is rendered on the page by using wp_localize_script on image-crate.js.
 *
 * @augments wp.media.view.Search
 */
var ImageCrateSearch = wp.media.View.extend( {
	tagName: 'form',
	className: 'ic-search-form',

	events: {
		'submit': 'search'
	},

	initialize: function() {

		if ( this.model.get( 'search' ) === undefined ) {
			this.model.set( 'search', '' );
		}

		this.model.on( 'change', this.render, this );
	},

	/**
	 * @returns {wp.media.view.Search} Returns itself to allow chaining
	 */
	render: function() {
		jQuery( this.el ).empty()

		jQuery( this.el ).append(
			jQuery( '<input type="text" name="media-search-input" placeholder="Search images" value="' + this.model.escape( 'search' ) + '" />' )
		)

		jQuery( this.el ).append(
			jQuery( '<input type="submit" class="button button-primary" value="Search" />' )
		)

		return this;
	},

	search: function( event ) {
		event.preventDefault();

		this.model.set( 'search', event.target[0]['value'] )
		this.model.set( 'searchActive', true )
	}

} );

module.exports = ImageCrateSearch;