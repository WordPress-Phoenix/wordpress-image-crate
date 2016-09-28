(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var ImageProviderController = wp.media.controller.Library.extend({
    defaults: _.defaults({
        id: 'ii',
        title: 'Image Source',
        multiple: false,
        content: 'getty',
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
        priority: 800
    }, wp.media.controller.Library.prototype.defaults),

    initialize: function () {
        if (!this.get('library')) {
            this.set('library', wp.media.query({ii: true}));
        }

        wp.media.controller.Library.prototype.initialize.apply(this, arguments);
    },

});

module.exports = ImageProviderController;
},{}],2:[function(require,module,exports){

var ImageProviderController = require('./controllers/image-provider-controller.js'),
    StockPhotosModel = require('./models/image-provider-photos.js'),
    StockPhotosBrowser = require('./views/browser/image-provider-photos.js'),
    coreCreateStates = wp.media.view.MediaFrame.Post.prototype.createStates,
    coreBindHandlers = wp.media.view.MediaFrame.Select.prototype.bindHandlers;

_.extend( wp.media.view.MediaFrame.prototype, {
    ii: {

        activate: function () {
            // coreActivate.apply(this, arguments);
            var view = _.first(this.views.get('.media-frame-router')),
                viewSettings = {};

            viewSettings.getty = {text: 'Getty Images', priority: 60};
            // viewSettings.usatoday = {text: 'USA Today', priority: 80};
            view.set(viewSettings);

            this.content.mode('getty');
        },

        createToolbar: function (  ) {
            var controller = this;
            this.toolbar.set(new wp.media.view.Toolbar({
                controller: this,
                items: {
                    insert: {
                        style: 'primary',
                        text: 'Insert the Getty',
                        priority: 80,
                        requires: {
                            library: true,
                            selection: true
                        },

                        click: function () {
                            var state = controller.state(),
                                selection = state.get('selection');

                            controller.close();
                            // make the call to insert
                            // state.trigger('videopress:insert', selection).reset();
                        }
                    }
                }
            }));

        },

        loadGetty: function () {
            var state = this.state(),
                collection = state.get('image_crate_photos');

            if (_.isUndefined(collection)) {
              //  console.log('not loaded');

                collection = new StockPhotosModel(
                    null,
                    {
                        props: {
                            query: true,
                            category: 'generic'
                        }
                    }
                );

                //console.log(collection);
                // Reference the state if needed later
                state.set('image_crate_photos', collection);

            }

            this.content.set(new StockPhotosBrowser({
                controller: this,
                collection: collection
            }));

        },

        loadUSAT: function () {
            console.log('usat ready');
        }
    }
});

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
    // this.states.add([
    //     new wp.media.controller.Library({
    //         id: 'ii',
    //         router: 'ii',
    //         toolbar: 'ii-toolbar',
    //         title: 'Image Crate',
    //         priority: 800,
    //         searchable: true,
    //         sortable: false
    //     })
    // ]);
};
},{"./controllers/image-provider-controller.js":1,"./models/image-provider-photos.js":4,"./views/browser/image-provider-photos.js":6}],3:[function(require,module,exports){
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

},{}],4:[function(require,module,exports){
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

        if (this.props.get('query')) {

            props = this.props.toJSON();

            props.cache = ( true !== refresh );

            this.mirror(StockPhotosQuery.get(props));

        }

    }

});

module.exports = StockPhotos;

},{"./image-provider-photos-query":3}],5:[function(require,module,exports){
/* global module, wpaas_stock_photos */

var StockPhotoThumb = wp.media.view.Attachment.extend({

    // events: {
    //     // 'click': 'previewImage'
    // },
    // template: wp.template('attachment'),

    initialize: function () {

        wp.media.view.Attachment.prototype.initialize.apply(this, arguments);

        var selection = this.options.selection;
        console.log( selection );
       // this.listenTo(this.collection.StockPhotosProps, 'change:importing', this.toggleState);

    },

    render: function () {

        wp.media.view.Attachment.prototype.render.apply(this, arguments);

        // this.toggleState();

        return this;

    },

    // overwrite the image size pulling function
    imageSize: function (size) {

        wp.media.view.Attachment.prototype.imageSize.apply(this, arguments);

        var sizes = this.model.get('sizes');

        // reset to thumbnail as default
        size = size || 'thumbnail';

        // Use the provided image size if possible.
        if (sizes) {
            if (sizes[size]) {
                matched = sizes[size];
            } else if (sizes.large) {
                matched = sizes.large;
            } else if (sizes.thumbnail) {
                matched = sizes.thumbnail;
            } else if (sizes.full) {
                matched = sizes.full;
            }

            if (matched) {
                return _.clone(matched);
            }
        }

        return {
            url: this.model.get('url'),
            width: this.model.get('width'),
            height: this.model.get('height'),
            orientation: this.model.get('orientation')
        };
    },

    // previewImage: function (event) {
    //
    //     event.preventDefault();
    //
    //     this.collection.StockPhotosProps.set('previewing', this.model);
    //
    // },

    downloadImage: function () {

        var t = this;

        wp.media.ajax({
            data: {
                action: 'wpaas_stock_photos_download',
                filename: this.model.get('filename'),
                id: this.model.get('id'),
                nonce: this.model.get('nonces').download
            }
        }).done(function (attachment) {

            var browse = wp.media.frame.content.mode('browse');

            browse.get('gallery').collection.add(attachment);
            browse.get('selection').collection.add(attachment);

            // This will trigger all mutation observer
            wp.Uploader.queue.add(attachment);
            wp.Uploader.queue.remove(attachment);

            // @todo find a better way
            browse.get('gallery').$('li:first .thumbnail').click();

        }).fail(function () {

            // @todo

        }).always(function () {

            t.collection.StockPhotosProps.set('importing', false);
            t.toggleState();
            t.$el.blur();

        });

    }
});

module.exports = StockPhotoThumb;

},{}],6:[function(require,module,exports){
/* global require */

var StockPhotoThumb = require('./image-provider-photo.js');
    //StockPhotosFilter = require('./image-provider-photos-filter.js');

var StockPhotosBrowser = wp.media.view.AttachmentsBrowser.extend({
//var StockPhotosBrowser = wp.media.view.Frame.extend({

    tagName: 'div',
    className: 'image-crate attachments-browser',

    initialize: function () {

        this.createToolbar();
        this.createAttachments();
        // this.updateContent();

        this.listenTo(this.collection, 'add remove reset', _.bind(this.updateContent, this));

        // this.controller.on('toggle:upload:attachment', this.toggleUploader, this);
        // this.controller.on('edit:selection', this.editSelection);
        // this.createToolbar();
        // this.createUploader();
        // this.createAttachments();
        // if (this.options.sidebar) {
            this.createSidebar();
        // }
        // this.updateContent();
        //
        // if (!this.options.sidebar || 'errors' === this.options.sidebar) {
        //     this.$el.addClass('hide-sidebar');
        //
        //     if ('errors' === this.options.sidebar) {
        //         this.$el.addClass('sidebar-for-errors');
        //     }
        // }
        //
        // this.collection.on('add remove reset', this.updateContent, this);
    },


    createAttachments: function () {
        this.attachments = new wp.media.view.Attachments({
            controller: this.controller,
            collection: this.collection,
            AttachmentView: StockPhotoThumb
        });

        this.views.add(this.attachments);

        this.attachmentsNoResults = new wp.media.View({
            controller: this.controller,
            tagName: 'div',
            className: 'uploader-inline'
        });

        this.attachmentsNoResults.$el.addClass('hidden');
        this.attachmentsNoResults.$el.html(
            '<div class="uploader-inline-content has-upload-message">' +
            '<h2 class="upload-message">' +
            'No Images sucka' +
            //wpaas_stock_photos.no_images +
            '</h2></div>'
        );

        this.views.add(this.attachmentsNoResults);

    },

    createToolbar: function () {

        var toolbarOptions;

        toolbarOptions = {
            controller: this.controller
        };

        this.toolbar = new wp.media.view.Toolbar(toolbarOptions);

        this.views.add(this.toolbar);

        // This is required to prevent a js warning
        this.toolbar.set('spinner', new wp.media.view.Spinner({
            priority: -60
        }));

        // "Filters" will return a <select>, need to render
        // screen reader text before
        this.toolbar.set('filtersLabel', new wp.media.view.Label({
            value: 'Helo',
            attributes: {
                'for': 'media-attachment-filters'
            },
            priority: -80
        }).render());

        // Let's put the actual category filter
        this.toolbar.set('filters', new wp.media.view.AttachmentFilters({
            controller: this.controller,
            model: this.collection.props,
            StockPhotosProps: this.collection.StockPhotosProps,
            priority: -80
        }).render());

    },

    createSidebar: function () {
        var options = this.options,
            selection = options.selection,
            sidebar = this.sidebar = new wp.media.view.Sidebar({
                controller: this.controller
            });

    //     // todo: need to figure out what selection:single is
    //
        this.views.add(sidebar);
    //
    //     // if (this.controller.uploader) {
    //     //     sidebar.set('uploads', new wp.media.view.UploaderStatus({
    //     // //         controller: this.controller,
    //     //         priority: 40
    //     //     }));
    //     // }
    //     //
    //     // selection.on('selection:single', this.createSingle, this);
    //     // selection.on('selection:unsingle', this.disposeSingle, this);
    //
    //     // if (selection.single()) {
    //     //     this.createSingle();
    //     // }
    },

    dispose: function () {
        // this.options.selection.off(null, null, this);
        wp.media.View.prototype.dispose.apply(this, arguments);
        return this;
    },

    updateContent: function () {

        var view = this;

        this.toolbar.get('spinner').show();

        if (this.collection.length) {

            view.attachmentsNoResults.$el.addClass('hidden');

            view.toolbar.get('spinner').hide();

            return;

        }

        this.collection.more().always(function () {

            if (!view.collection.length) {

                view.attachmentsNoResults.$el.removeClass('hidden');

            } else {

                view.attachmentsNoResults.$el.addClass('hidden');

            }

            view.toolbar.get('spinner').hide();

        });

    }

});

module.exports = StockPhotosBrowser;

},{"./image-provider-photo.js":5}]},{},[2]);
