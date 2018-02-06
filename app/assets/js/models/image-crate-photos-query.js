/**
 * wp.media.model.Query
 *
 * A collection of attachments from the external data source.
 *
 * @augments wp.media.model.Query
 */
var StockPhotosQuery = wp.media.model.Query.extend( {

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

				console.log( 'the args yo' );
				console.dir( args );

				console.log( 'the model yo' );
				console.dir( model );

				console.log( 'the options yo' );
				console.dir( options );

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
				var Query = StockPhotosQuery,
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
					console.log( 'was no query here it at' );
					console.log( query );
					queries.push( query );
				}
				console.log( 'final return here it at' );
				console.log( query );
				return query;
			};
		}())
	} );

module.exports = StockPhotosQuery;
