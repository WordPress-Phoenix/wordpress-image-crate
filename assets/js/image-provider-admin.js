(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var ImageProviderController = wp.media.controller.Library.extend({
    defaults: _.defaults({
        id: 'ii',
        title: 'Image Source',
        multiple: false,
        menu: 'default',
        router: 'ii',
        toolbar: 'ii-toolbar',
        searchable: true,
        filterable: false,
        sortable: false,
        autoSelect: true,
        describe: false,
        contentUserSetting: true,
        syncSelection: false,
        priority: 800,
        isImageCrate: true
    }, wp.media.controller.Library.prototype.defaults ),

    initialize: function () {
        if (!this.get('library')) {
            this.set('library', wp.media.query({ ii: true }) );
        }
        wp.media.controller.Library.prototype.initialize.apply(this, arguments);
    }

});

module.exports = ImageProviderController;
},{}],2:[function(require,module,exports){

var ImageProviderController = require('./controllers/image-provider-controller.js'),
    StockPhotosModel = require('./models/image-provider-photo-model.js'),
    // StockPhotosBrowser = require('./views/browser/image-provider-photos.js'),
    coreCreateStates = wp.media.view.MediaFrame.Post.prototype.createStates,
    coreBindHandlers = wp.media.view.MediaFrame.Select.prototype.bindHandlers,
    coreAttachmentRender = wp.media.view.Attachment.prototype.render;

_.extend( wp.media.view.MediaFrame.prototype, {
    ii: {
        activate: function () {
            var view = _.first(this.views.get('.media-frame-router')),
                viewSettings = {};

            viewSettings.usatoday = {text: 'USA Today Sports', priority: 60};
            // viewSettings.getty = {text: 'Getty Images', priority: 80};
            view.set(viewSettings);

            this.content.mode('usatoday');
        },

        createToolbar: function (  ) {
            var controller = this;
            this.toolbar.set(new wp.media.view.Toolbar({
                controller: this,
                items: {
                    insert: {
                        style: 'primary',
                        text: 'Insert Image',
                        priority: 80,
                        requires: {
                            library: true,
                            selection: true
                        },

                        click: function () {
                            var state = controller.state(),
                                selection = state.get('selection');
                            
                            console.log( 'we made it!' );

                            controller.close();
                        }
                    }
                }
            }));

        },

        loadUSAT: function () {
            var state = this.state(),
                collection = state.get('image_crate_photos');

            if (_.isUndefined(collection)) {
                collection = new StockPhotosModel(
                    null,
                    {
                        props: {
                            query: true,
                            category: 'generic'
                        }
                    }
                );

                // Reference the state if needed later
                state.set('image_crate_photos', collection);
            }

            var state = this.state(),
                selection = state.get('selection'),
                view;

            this.content.set(new wp.media.view.AttachmentsBrowser({
                className: 'image-crate attachments-browser',
                controller: this,
                collection: collection,
                selection: selection,
                model: state,
                filters: false,
                date: false,
                sidebar: true,
                sortable: false,
            }));

        },

        loadGetty: function () {
            console.log('getty ready');
        }
    }
});

wp.media.view.Attachment.prototype.render = function() {
    var options = this.options || {};
    if ('ii' == this.controller.state().get('id')) {
        options.size = this.imageSize('full');
    }
    coreAttachmentRender.apply( this, arguments );
};


wp.media.view.MediaFrame.Select.prototype.bindHandlers = function () {
    coreBindHandlers.apply(this, arguments);

    this.on('router:create:ii', this.createRouter, this);
    this.on('router:activate:ii', this.ii.activate, this);
    this.on('router:deactivate:ii', this.deactivate, this);

    this.on('toolbar:create:ii-toolbar', this.ii.createToolbar, this);

    this.on('content:render:getty', this.ii.loadGetty, this);
    this.on('content:render:usatoday', this.ii.loadUSAT, this);

};

wp.media.view.MediaFrame.Post.prototype.createStates = function () {
    coreCreateStates.apply(this, arguments);
    this.states.add(new ImageProviderController);
};
},{"./controllers/image-provider-controller.js":1,"./models/image-provider-photo-model.js":3}],3:[function(require,module,exports){
/* global require */
var StockPhotosQuery = require('./image-provider-photos-query');

var StockPhotos = wp.media.model.Attachments.extend({

    initialize: function (models, options) {

        wp.media.model.Attachments.prototype.initialize.call(this, models, options);

        this.StockPhotosProps = new Backbone.Model();

        this.StockPhotosProps.set('importing', false);
        this.StockPhotosProps.set('previewing', false);

    },

    _requery: function (refresh) {

        var props;

        if ( this.props.get('query') ) {

            props = this.props.toJSON();

            props.cache = ( true !== refresh );

            this.mirror( StockPhotosQuery.get( props ) );

        }

    }

});

module.exports = StockPhotos;

},{"./image-provider-photos-query":4}],4:[function(require,module,exports){
var StockPhotosQuery = wp.media.model.Query.extend({

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
        sync: function (method, model, options) {

            var args;

            // Overload the read method so Attachment.fetch() functions correctly.
            options = options || {};
            options.context = this;
            options.data = _.extend(options.data || {}, {
                action: 'image_implementor_get'
            });

            // Clone the args so manipulation is non-destructive.
            args = _.clone(this.args);
            // Determine which page to query.
            if (-1 !== args.posts_per_page) {
                args.paged = Math.round(this.length / args.posts_per_page) + 1;
            }

            options.data.query = args;
            return wp.media.ajax(options);

        }

    },
    {
        /**
         * Overriding core behavior
         */
        get: (function () {
            /**
             * @static
             * @type Array
             */
            var queries = [];

            /**
             * @returns {Query}
             */
            return function (props, options) {
                var someprops = props;
                var Query = StockPhotosQuery,
                    args = {},
                    query,
                    cache = !!props.cache || _.isUndefined(props.cache);

                // Remove the `query` property. This isn't linked to a query,
                // this *is* the query.
                delete props.query;
                delete props.cache;

                // Generate the query `args` object.
                // Correct any differing property names.
                _.each(props, function (value, prop) {

                    if (_.isNull(value)) {

                        return;

                    }

                    args[prop] = value;

                });


                // Fill any other default query args.
                _.defaults(args, Query.defaultArgs);

                // Search the query cache for a matching query.
                if (cache) {

                    query = _.find(queries, function (query) {

                        return _.isEqual(query.args, args);

                    });

                } else {

                    queries = [];

                }

                // Otherwise, create a new query and add it to the cache.
                if (!query) {

                    query = new Query([], _.extend(options || {}, {
                        props: props,
                        args: args
                    }));

                    queries.push(query);

                }

                return query;

            };
        }())
    });

module.exports = StockPhotosQuery;

},{}]},{},[2]);
