(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
/**
 * wp.media.controller.GettyImagesController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var Library = wp.media.controller.Library,
	GettyImagesController;

GettyImagesController = Library.extend( {

	/**
	 * Extend the core defaults and add modify listener key values. These values are referenced when
	 * the controller is triggered.
	 */
	defaults: _.defaults( {
		id: 'getty-images',
		title: 'Getty Images',
		priority: 300,
		content: 'provider',
		router: 'image-provider',
		toolbar: 'image-provider',
		button: 'Download Getty Image',
		verticalFilter: false,
		waitForSearch: true,

		/**
		 * Any data that needs to be passed from this controller via ajax, should be passed with this object.
		 *
		 * The provider key is parsed on the backend to determine which object to use. The chosen object is then used
		 * to retrieve images from a external service.
		 */
		library: wp.media.query( { provider: 'getty-images' } )

	}, Library.prototype.defaults ),

	activate: function() {
		this.set( 'mode', this.id );
	}
} );

module.exports = GettyImagesController;
},{}],2:[function(require,module,exports){
/**
 * wp.media.controller.ImageExchangeController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var Library = wp.media.controller.Library,
    ImageExchangeController;

ImageExchangeController = Library.extend({

    /**
     * Extend the core defaults and add modify listener key values. These values are referenced when
     * the controller is triggered.
     */
    defaults: _.defaults({
        id: 'image-exchange',
        title: 'Image Exchange',
        priority: 320,
        content: 'provider',
        router: 'image-provider',
        toolbar: 'image-provider',
        button: 'Download FanSided Image',
        searchable: true,
        verticalFilter: true,
        waitForSearch: false,

        /**
         * Any data that needs to be passed from this controller via ajax, should be passed with this object.
         *
         * The provider key is parsed on the backend to determine which object to use. The chosen object is then used
         * to retrieve images from a external service.
         */
        library: wp.media.query({ provider: 'image-exchange' })
    }, Library.prototype.defaults ),

    activate: function () {
        this.set( 'mode', this.id );
    }
});

module.exports = ImageExchangeController;
},{}],3:[function(require,module,exports){
/**
 * wp.media.controller.ImagnController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var Library = wp.media.controller.Library,
	ImagnController;

ImagnController = Library.extend( {

	/**
	 * Extend the core defaults and add modify listener key values. These values are referenced when
	 * the controller is triggered.
	 */
	defaults: _.defaults( {
		id: 'imagn',
		title: 'Imagn Images',
		priority: 280,
		content: 'provider',
		router: 'image-provider',
		toolbar: 'image-provider',
		button: 'Download Imagn Image',
		verticalFilter: false,
		waitForSearch: true,

		/**
		 * Any data that needs to be passed from this controller via ajax, should be passed with this object.
		 *
		 * The provider key is parsed on the backend to determine which object to use. The chosen object is then used
		 * to retrieve images from a external service.
		 */
		library: wp.media.query( { provider: 'imagn' } )

	}, Library.prototype.defaults ),

	activate: function() {
		this.set( 'mode', this.id );
	}
} );

module.exports = ImagnController;
},{}],4:[function(require,module,exports){
(function ($) {
	$(function () {

        /**
         * Image Crate Manifest - Adding custom controllers to the WordPress media modal.
         *
         * The main effort of this project is to add multiple image providers in a native WordPress way. This is
         * executed by extending the Post MediaFrame {VVV/www/wordpress-develop/src/wp-includes/js/media/views/frame/post.js}
         *
         */
		var imagecrate = imagecrate || {};

        // Store the core post view.
		var corePost = wp.media.view.MediaFrame.Post;

		// Controllers
		imagecrate.ImageExchangeController = require('./controllers/image-exchange.js');
		imagecrate.GettyImagesController = require('./controllers/getty-images.js');
		imagecrate.ImagnController = require('./controllers/imagn.js');

		// Attachment Models
        imagecrate.ProviderAttachments = require('./models/attachments.js');

		// Views
		imagecrate.ProviderToolbar = require('./views/toolbars/provider.js');
        imagecrate.ProviderPhotosBrowser = require('./views/browser/attachments.js');

		/**
		 * Add controllers to the media modal Post Frame
		 */
		wp.media.view.MediaFrame.Post = corePost.extend({

            /**
             * If you want to extend the function body from a parent object you need to call prototype.functionName.
             *
             * This is similar to using `parent::__construct();` in php.
             */
            createStates: function () {
				corePost.prototype.createStates.apply(this, arguments);

                /**
                 * Adding states adds menu items to the left menu on the media modal.
                 */
				this.states.add([
					new imagecrate.GettyImagesController,
					new imagecrate.ImageExchangeController,
					new imagecrate.ImagnController,
				]);
			},

            /**
             * Assign handlers to controllers.
             *
             * `content:create:provider` is a listener assignment for an event that is triggered when a provider
             * controller is clicked. When this event is triggered, the callback is fired and any listeners subscribed
             * to the event, will update their views.
             */
            bindHandlers: function () {
				corePost.prototype.bindHandlers.apply(this, arguments);

				this.on('toolbar:create:image-provider', this.createToolbar, this);
				this.on('toolbar:render:image-provider', imagecrate.ProviderToolbar, this);

                this.on('router:create:image-provider', this.createRouter, this);
                this.on('router:render:image-provider', this.providerRouter, this);

                this.on('content:create:provider', this.providerContent, this);
			},

            /**
             * Load images from an external source.
             *
             * @param contentRegion
             */
            providerContent: function( contentRegion ) {
                var state = this.state(),
                    id = state.get('id'),
                    collection = state.get('image_crate_photos'),
                    selection = state.get('selection');

                if (_.isUndefined(collection)) {
                    collection = new imagecrate.ProviderAttachments(
                        null,
                        {
                            /**
                             * Passing the props from the controller is important here. The provider type is set when
                             * the controller is instantiated. When the ajax call is sent, provider type passed as a
                             * request param. That value is then used to create new object to get images from the
                             * requested provider.
                             */
                            props: state.get('library').props.toJSON()
                        }
                    );

                    // Reference the state if needed later
                    state.set('image_crate_photos', collection);
                }

                /**
                 * Set main content view to display external images.
                 *
                 * @see /assets/js/views/browser/attachments.js
                 */
                contentRegion.view = new imagecrate.ProviderPhotosBrowser({
                    tagName: 'div',
                    className: id + ' image-crate attachments-browser',
                    controller: this,
                    collection: collection,
                    selection: selection,
                    model: state,
                    filters: true,
                    search: true,
                });
            },

            /**
             * When the router listener is fired, the view updates the tabs located above the image browser.
             *
             * If only one object is passed, the tab view will not display. Priority controls render order.
             */
            providerRouter: function (routerView) {
                routerView.set({

                    /*
                     * The naming of this object is important here. When this router is rendered,
                     * 'content:create:provider' is trigger and the content is updated.
                     */
                    provider: {
                        text: 'Provider',
                        priority: 20
                    }
                });
            }
		});
	});
})(jQuery);

},{"./controllers/getty-images.js":1,"./controllers/image-exchange.js":2,"./controllers/imagn.js":3,"./models/attachments.js":5,"./views/browser/attachments.js":7,"./views/toolbars/provider.js":12}],5:[function(require,module,exports){
/**
 * wp.media.model.StockPhotosQuery
 *
 * A collection of attachments.
 *
 * @class
 * @augments wp.media.model.Attachments
 */
var ProviderQuery = require('./query');

var ProviderAttachments = wp.media.model.Attachments.extend({
    /**
     * Override core _requery method to accept a custom query
     *
     * @param refresh
     * @private
     */
    _requery: function (refresh) {
        var props;

        if ( this.props.get('query') ) {
            props = this.props.toJSON();
            props.cache = ( true !== refresh );
            this.mirror( ProviderQuery.get( props ) );
        }
    }
});

module.exports = ProviderAttachments;

},{"./query":6}],6:[function(require,module,exports){
var Attachments = wp.media.model.Attachments;

/**
 * wp.media.model.ProviderQuery
 *
 * A collection of attachments from the external data source.
 *
 * This file is nearly one to one replica of the core query file. Exceptions are where options.data is extended to
 * communicate with a custom method and where Query is updated to use the overridden core query.
 *
 * @augments wp.media.model.Query
 */
var ProviderQuery = wp.media.model.Query.extend( {

		/**
		 * Override the initialize method to delay query until search term is provided
		 *
		 * @param models
		 * @param options
		 * @return {boolean}
		 */
		initialize: function( models, options ) {
			var allowed;

			// Bail if search term is not provided
			if ( _.isUndefined( options.args.search ) || _.isEmpty( options.args.search ) ) {
				if ( options.args.waitForSearch ) {
					return false;
				}
			}

			options = options || {};
			Attachments.prototype.initialize.apply( this, arguments );

			this.args     = options.args;
			this._hasMore = true;
			this.created  = new Date();

			this.filters.order = function( attachment ) {
				var orderby = this.props.get('orderby'),
					order = this.props.get('order');

				if ( ! this.comparator ) {
					return true;
				}

				// We want any items that can be placed before the last
				// item in the set. If we add any items after the last
				// item, then we can't guarantee the set is complete.
				if ( this.length ) {
					return 1 !== this.comparator( attachment, this.last(), { ties: true });

					// Handle the case where there are no items yet and
					// we're sorting for recent items. In that case, we want
					// changes that occurred after we created the query.
				} else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
					return attachment.get( orderby ) >= this.created;

					// If we're sorting by menu order and we have no items,
					// accept any items that have the default menu order (0).
				} else if ( 'ASC' === order && 'menuOrder' === orderby ) {
					return attachment.get( orderby ) === 0;
				}

				// Otherwise, we don't want any items yet.
				return false;
			};

			// Observe the central `wp.Uploader.queue` collection to watch for
			// new matches for the query.
			//
			// Only observe when a limited number of query args are set. There
			// are no filters for other properties, so observing will result in
			// false positives in those queries.
			allowed = [ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent' ];
			if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() ) {
				this.observe( wp.Uploader.queue );
			}
		},

		/**
		 * Overrides wp.media.model.Query.sync
		 * Overrides Backbone.Collection.sync
		 * Overrides wp.media.model.Attachments.sync
		 *
		 * @param {String} method
		 * @param {Backbone.Model} model
		 * @param {Object} [options={}]
		 * @returns {Promise}
		 */
		sync: function( method, model, options ) {
			var args;

			// Overload the read method so Attachment.fetch() functions correctly.
			if ( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action: 'image_crate_get',
					_ajax_nonce: imagecrate.nonce
				} );

				// Clone the args so manipulation is non-destructive.
				args = _.clone( this.args );

				// Determine which page to query.
				if ( -1 !== args.posts_per_page ) {
					args.paged = Math.round( this.length / args.posts_per_page ) + 1;
				}

				options.data.query = args;
				return wp.media.ajax( options );

				// Otherwise, fall back to Backbone.sync()
			} else {
				/**
				 * Call wp.media.model.Attachments.sync or Backbone.sync
				 */
				fallback = Attachments.prototype.sync ? Attachments.prototype : Backbone;
				return fallback.sync.apply( this, arguments );
			}
		}
	},
	{
		/**
		 * Overriding core behavior
		 */
		get: (function() {
			/**
			 * @static
			 * @type Array
			 */
			var queries = [];

			/**
			 * @returns {Query}
			 */
			return function( props, options ) {
				var someprops = props;
				var Query = ProviderQuery,
					args = {},
					query,
					cache = !!props.cache || _.isUndefined( props.cache );

				// Remove the `query` property. This isn't linked to a query,
				// this *is* the query.
				delete props.query;
				delete props.cache;

				// Generate the query `args` object.
				// Correct any differing property names.
				_.each( props, function( value, prop ) {
					if ( _.isNull( value ) ) {
						return;
					}
					args[prop] = value;
				} );

				// Fill any other default query args.
				_.defaults( args, Query.defaultArgs );

				// Search the query cache for a matching query.
				if ( cache ) {
					query = _.find( queries, function( query ) {
						return _.isEqual( query.args, args );
					} );
				} else {
					queries = [];
				}

				// Otherwise, create a new query and add it to the cache.
				if ( !query ) {
					query = new Query( [], _.extend( options || {}, {
						props: props,
						args: args
					} ) );
					queries.push( query );
				}
				return query;
			};
		}())
	} );

module.exports = ProviderQuery;

},{}],7:[function(require,module,exports){
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
},{"./no-results-search.js":8,"./no-results.js":9,"./search.js":10,"./verticals-filter.js":11}],8:[function(require,module,exports){
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
},{"./search.js":10}],9:[function(require,module,exports){
var NoResults = wp.media.View.extend( {
	tagName: 'div',
	className: 'no-results-found',

	/**
	 * @returns {wp.media.view.Search} Returns itself to allow chaining
	 */
	render: function() {

		jQuery( this.el ).empty();

		jQuery( this.el ).append(
			jQuery(
				'<div class="uploader-inline image-crate-no-results">' +
				'<div class="uploader-inline-content">' +
				'<h2 class="upload-message">Sorry. No images found.</h2>' +
				'</div>' +
				'</div>'
			)
		)

		return this;
	}

} );

module.exports = NoResults;
},{}],10:[function(require,module,exports){
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
},{}],11:[function(require,module,exports){
/**
 * wp.media.view.VerticalsFilter
 *
 * @augments wp.media.view.AttachmentFilters
 */
var VerticalsFilter = wp.media.view.AttachmentFilters.extend( {
    id: 'media-attachment-vertical-filters',

    createFilters: function () {
        var filters = {};
        var verticals = [
            { vertical: 'ENTERTAINMENT', text: 'ENTERTAINMENT' },
            { vertical: 'TRENDING TOPICS', text: 'TRENDING TOPICS' },
            { vertical: 'EXTRA', text: 'EXTRA' },
            { vertical: 'LOCAL', text: 'LOCAL' },
            { vertical: 'NFL', text: 'NFL' },
            { vertical: 'NBA', text: 'NBA' },
            { vertical: 'MLB', text: 'MLB' },
            { vertical: 'NHL', text: 'NHL' },
            { vertical: 'SOCCER', text: 'SOCCER' },
            { vertical: 'NCAABB', text: 'NCAABB' },
            { vertical: 'NCAAF', text: 'NCAAF' },
            { vertical: 'LIFESTYLE', text: 'LIFESTYLE' },
            { vertical: 'ESPORTS', text: 'ESPORTS' }
        ];

        _.each(verticals || {}, function ( value, index ) {
            filters[ index ] = {
                text: value.text,
                props: {
                    vertical: value.vertical
                }
            };
        });

        filters.all = {
            text: 'All Verticals',
            props: {
                vertical: ''
            },
            priority: 10
        };
        this.filters = filters;
    }
});

module.exports = VerticalsFilter;
},{}],12:[function(require,module,exports){
/**
 * wp.media.controller.GettyImagesController
 *
 * Custom Toolbar for downloading images
 *
 * @augments wp.media.controller.Library
 */
var ProviderToolbar = function( view ) {
	var controller = this,
		state = controller.state();

	this.selectionStatusToolbar( view );

	view.set( state.get( 'id' ), {
		style: 'primary',
		priority: 80,
		text: state.get( 'button' ),
		requires: { selection: true },

		/**
		 * @fires wp.media.controller.State#insert
		 */
		click: function() {
			var selection = state.get( 'selection' );
			var provider = state.get( 'id' );

			jQuery( '.media-toolbar-primary .media-button' )
				.text( 'Downloading...' )
				.before( '<span class="image-crate-spinner spinner is-active"></span>' )

			wp.media.ajax( {
				data: {
					action: 'image_crate_download',
					query: {
						download_url: selection.models[0].get( 'url' ),
						caption: selection.models[0].get( 'caption' ),
						provider: provider,
						filename: selection.models[0].get( 'filename' ),
						id: selection.models[0].get( 'id' )
					},
					_ajax_nonce: imagecrate.nonce
				}
			} ).done( function( attachment ) {

				// Swap back to insert state to manipulate collection.
				controller.setState( 'insert' );

				// Image may exist in the library, so we move it to the front of the line
				var browse = wp.media.frame.content.mode( 'browse' );

				// might not need this
				browse.get( 'gallery' ).collection.add( attachment );
				browse.get( 'selection' ).collection.add( attachment );

				browse.get( 'insert' ).collection.remove( attachment );
				browse.get( 'insert' ).collection.unshift( attachment );

				// This will trigger all mutation observer
				wp.Uploader.queue.add( attachment );
				wp.Uploader.queue.remove( attachment );

				browse.get( 'insert' ).$( 'li:first .thumbnail' ).click();
			} );
		}
	} );
};

module.exports = ProviderToolbar;
},{}]},{},[4]);
