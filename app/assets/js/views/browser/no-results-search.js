var ImageCrateSearch = require( './search.js' );

var NoResultsSearch = wp.media.View.extend( {
	tagName: 'div',
	className: 'no-results-found',

	initialize: function() {
		this.model.on( 'change', this.render, this );
		this.collection.on( 'change add remove reset', _.debounce( this.render, 300 ), this );

		if ( this.model.get( 'search' ) === undefined ) {
			this.model.set( 'search', '' );
		}

		if ( this.model.get( 'searchActive' ) === undefined ) {
			this.model.set( 'searchActive', false );
		}
	},

	/**
	 * @returns {wp.media.view.Search} Returns itself to allow chaining
	 */
	render: function() {

		if ( !this.options.shouldRender ) {
			jQuery( this.el ).remove();
			return this;
		}

		jQuery( this.el ).empty();

		if ( this.collection.length > 0 ) {
			return this;
		}

		if ( !this.model.get( 'searchActive' ) ) {
			var Search = new ImageCrateSearch( {
				controller: this.controller,
				model: this.collection.props
			} ).render();

			jQuery( this.el ).append(
				jQuery(
					'<div class="uploader-inline image-crate-no-results">' +
					'<div class="uploader-inline-content">' +
					'<h2 class="upload-message">Search for images</h2>' +
					'</div>' +
					'</div>'
				)
			)

			jQuery( this.el ).find( '.uploader-inline-content' ).append(
				Search.$el
			)

			setTimeout( function() {
				jQuery( '.image-crate-no-results input:text' ).focus();
			}, 200 )

			return this;
		}

		jQuery( this.el ).append(
			jQuery(
				'<div class="uploader-inline image-crate-no-results">' +
				'<div class="uploader-inline-content">' +
				'<h2 class="upload-message">No images found. Try a different search.</h2>' +
				'</div>' +
				'</div>'
			)
		)

		return this;
	}

} );

module.exports = NoResultsSearch;