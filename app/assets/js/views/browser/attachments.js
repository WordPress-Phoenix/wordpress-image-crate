/**
 * wp.media.view.StockPhotosBrowser
 *
 * @class
 * @augments wp.media.view.AttachmentsBrowser
 */
var ImageCrateSearch = require( './search.js' ),
	NoResults = require( './no-results.js' ),
	NoResultsSearch = require( './no-results-search.js' ),
	VerticalsFilter = require( './verticals-filter.js' ),
	coreAttachmentsInitialize = wp.media.view.AttachmentsBrowser.prototype.initialize,
	ProviderPhotosBrowser;

ProviderPhotosBrowser = wp.media.view.AttachmentsBrowser.extend( {

	initialize: function() {
		coreAttachmentsInitialize.apply( this, arguments );

		this.createToolBar();
		this.createUploader( true );

		this.collection.props.set( 'waitForSearch', this.model.get( 'waitForSearch' ) );
	},

	updateContent: function() {
		var view = this;

		if ( !this.collection.length ) {
			this.toolbar.get( 'spinner' ).show();

			this.dfd = this.collection.more().done( function() {
				view.toolbar.get( 'spinner' ).hide();
			} );

		} else {
			view.toolbar.get( 'spinner' ).hide();
		}
	},

	/**
	 * Override core toolbar view rendering.
	 *
	 * Change events are auto assigned to select fields and text inputs. Any form change will send
	 * new values to the backend via an ajax call.
	 */
	createToolBar: function() {

		var model =  this.collection.props;

		if ( this.model.get( 'verticalFilter') ) {

			// Labels are display visually, but they are rendered for accessibility.
			this.toolbar.set( 'VerticalsFilterLabel', new wp.media.view.Label( {
				value: 'Verticals Label',
				attributes: {
					'for': 'media-attachment-vertical-filters'
				},
				priority: -75
			} ).render() );

			this.toolbar.set( 'VerticalsFilter', new VerticalsFilter( {
				controller: this.controller,
				model: this.collection.props,
				priority: -75
			} ).render() );

		}

		this.toolbar.unset( 'dateFilterLabel', {} );
		this.toolbar.unset( 'dateFilter', {} );
		this.toolbar.unset( 'search', {} );

		if ( this.model.get( 'searchable' ) ) {
			this.toolbar.set( 'search', new ImageCrateSearch( {
				controller: this.controller,
				model: this.collection.props,
				searchable: this.model.get( 'searchable' ),
				priority: -70
			} ).render() );
		}

		this.views.add( this.toolbar );
	},

	/**
	 * Override core uploader method.
	 */
	createUploader: function( render ) {
		var shouldRender = ( !_.isUndefined( render ) );

		if ( this.model.get( 'searchable' ) ) {
			this.uploader = new NoResultsSearch( {
				controller: this.controller,
				model: this.collection.props,
				collection: this.collection,
				shouldRender: shouldRender
			} );
		} else {
			this.uploader = new NoResults( {
				controller: this.controller,
				model: this.collection.props,
				collection: this.collection
			} );
		}

		this.uploader.$el.addClass( 'hidden' );

		this.views.add( this.uploader );
	}

} );

module.exports = ProviderPhotosBrowser;