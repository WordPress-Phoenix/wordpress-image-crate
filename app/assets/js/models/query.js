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
