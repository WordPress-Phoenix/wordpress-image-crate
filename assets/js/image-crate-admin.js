(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/**
 * wp.media.controller.ImageCrateController
 *
 * A state for downloading images from an external image source
 *
 * todo: localize text
 *
 * @augments wp.media.controller.Library
 */
var ImageCrateController = wp.media.controller.Library.extend({
    defaults: _.defaults({
        id: 'image-crate',
        title: imagecrate.page_title,
        multiple: false,
        menu: 'default',
        router: 'image-crate',
        toolbar: 'image-crate-toolbar',
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
            this.set('library', wp.media.query({ imagecrate: true }) );
        }
        wp.media.controller.Library.prototype.initialize.apply(this, arguments);
    }
});

module.exports = ImageCrateController;
},{}],2:[function(require,module,exports){

var ImageCrateController = require('./controllers/image-crate-controller.js'),
    StockPhotoThumb = require('./views/browser/image-crate-photo.js'),
    StockPhotosModel = require('./models/image-crate-photo-model.js'),
    StockPhotoBrowser = require('./views/browser/image-crate-photos.js'),
    coreCreateStates = wp.media.view.MediaFrame.Post.prototype.createStates,
    coreBindHandlers = wp.media.view.MediaFrame.Select.prototype.bindHandlers;

_.extend( wp.media.view.MediaFrame.prototype, {
    image_crate: {
        activate: function () {
            // todo: goal to more image providers as tabs
            var view = _.first(this.views.get('.media-frame-router')),
                viewSettings = {};

            viewSettings.usatoday = {
                text: 'USA Today Sports',
                priority: 60
            };
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
                        text: 'Download Image',
                        priority: 80,
                        // todo: fix bug where require selection only works if at least one image is in the library
                        requires: {
                            library: true,
                            selection: true
                        },

                        click: function () {
                            var state = controller.state(),
                                selection = state.get('selection');

                            this.$el.attr('disabled', 'disabled')
                                    .text('Downloading');

                            console.log( 'filename' );
                            console.log( selection.models[0].get('filename') );

                            wp.media.ajax({
                                data: {
                                    action: 'image_crate_download',
                                    filename: selection.models[0].get('filename'),
                                    id: selection.models[0].get('id'),
                                    download_uri: selection.models[0].get('download_uri'),
                                    _ajax_nonce: imagecrate.nonce
                                }
                            }).done(function (attachment) {

                                var browse = wp.media.frame.content.mode('browse');
                                browse.get('gallery').collection.add(attachment);
                                browse.get('selection').collection.add(attachment);

                                // This will trigger all mutation observer
                                wp.Uploader.queue.add(attachment);
                                wp.Uploader.queue.remove(attachment);

                                // reset back to insert mode for adding post to editor
                                controller.setState('insert');

                                browse.get('gallery').$('li:first .thumbnail').click();

                            });
                        }
                    }
                }
            }));
        },

        loadUSAT: function () {
            var state = this.state(),
                collection = state.get('image_crate_photos'),
                selection = state.get('selection');

            if ( _.isUndefined( collection ) ) {
                collection = new StockPhotosModel(
                    null,
                    {
                        props: {
                            query: true,
                        }
                    }
                );

                // Reference the state if needed later
                state.set('image_crate_photos', collection);
            }

            this.content.set(new StockPhotoBrowser({
                className: 'image-crate attachments-browser',
                controller: this,
                collection: collection,
                selection: selection,
                model: state,
                filters: false,
                date: false,
                AttachmentView: StockPhotoThumb
            }));
        },
    }
});

wp.media.view.MediaFrame.Select.prototype.bindHandlers = function () {
    coreBindHandlers.apply(this, arguments);

    this.on('router:create:image-crate', this.createRouter, this);
    this.on('router:activate:image-crate', this.image_crate.activate, this);
    this.on('router:deactivate:image-crate', this.deactivate, this);

    this.on('toolbar:create:image-crate-toolbar', this.image_crate.createToolbar, this);

    this.on('content:render:usatoday', this.image_crate.loadUSAT, this);
};

wp.media.view.MediaFrame.Post.prototype.createStates = function () {
    coreCreateStates.apply(this, arguments);
    this.states.add(new ImageCrateController);
};
},{"./controllers/image-crate-controller.js":1,"./models/image-crate-photo-model.js":3,"./views/browser/image-crate-photo.js":5,"./views/browser/image-crate-photos.js":6}],3:[function(require,module,exports){
/**
 * wp.media.model.StockPhotosQuery
 *
 * A collection of attachments.
 *
 * @class
 * @augments wp.media.model.Attachments
 */
var StockPhotosQuery = require('./image-crate-photos-query');

var StockPhotos = wp.media.model.Attachments.extend({

    initialize: function (models, options) {
        wp.media.model.Attachments.prototype.initialize.call(this, models, options);
    },
    // todo: bug - state to display when first opening frame
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

},{"./image-crate-photos-query":4}],4:[function(require,module,exports){
/**
 * wp.media.model.Query
 *
 * A collection of attachments from the external data source.
 *
 * @augments wp.media.model.Query
 */
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
            if ('read' === method) {
                options = options || {};
                options.context = this;
                options.data = _.extend(options.data || {}, {
                    action: 'image_crate_get',
                    _ajax_nonce: imagecrate.nonce
                });

                // Clone the args so manipulation is non-destructive.
                args = _.clone(this.args);

                // Determine which page to query.
                if (-1 !== args.posts_per_page) {
                    args.paged = Math.round(this.length / args.posts_per_page) + 1;
                }

                options.data.query = args;
                return wp.media.ajax(options);

                // Otherwise, fall back to Backbone.sync()
            } else {
                /**
                 * Call wp.media.model.Attachments.sync or Backbone.sync
                 */
                fallback = Attachments.prototype.sync ? Attachments.prototype : Backbone;
                return fallback.sync.apply(this, arguments);
            }
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

},{}],5:[function(require,module,exports){
/**
 * wp.media.view.StockPhotoThumb
 *
 * @augments wp.media.view.Attachment
 */
var StockPhotoThumb = wp.media.view.Attachment.extend({

    render: function () {
        var options = _.defaults(this.model.toJSON(), {
            orientation: 'landscape',
            uploading: false,
            type: '',
            subtype: '',
            icon: '',
            filename: '',
            caption: '',
            title: '',
            dateFormatted: '',
            width: '',
            height: '',
            compat: false,
            alt: '',
            description: ''
        }, this.options);

        options.buttons = this.buttons;
        options.describe = this.controller.state().get('describe');

        if ('image' === options.type) {
            options.size = this.imageSize('thumbnail');
        }

        options.can = {};
        if (options.nonces) {
            options.can.remove = !!options.nonces['delete'];
            options.can.save = !!options.nonces.update;
        }

        if (this.controller.state().get('allowLocalEdits')) {
            options.allowLocalEdits = true;
        }

        if (options.uploading && !options.percent) {
            options.percent = 0;
        }

        this.views.detach();
        this.$el.html(this.template(options));

        this.$el.toggleClass('uploading', options.uploading);

        if (options.uploading) {
            this.$bar = this.$('.media-progress-bar div');
        } else {
            delete this.$bar;
        }

        // Check if the model is selected.
        this.updateSelect();

        // Update the save status.
        this.updateSave();

        this.views.render();

        return this;
    },
});

module.exports = StockPhotoThumb;

},{}],6:[function(require,module,exports){
/**
 * wp.media.view.StockPhotosBrowser
 *
 * @class
 * @augments wp.media.view.AttachmentsBrowser
 */
var ImageCrateSearch = require('./search.js'),
    NoResults = require('./no-results.js'),
    coreAttachmentsInitialize  = wp.media.view.AttachmentsBrowser.prototype.initialize;


var StockPhotosBrowser = wp.media.view.AttachmentsBrowser.extend({
    tagName: 'div',
    className: 'image-crate attachments-browser',

    defaults: _.defaults({
        filters: false,
        search: false,
        date: false,
        display: false,
        sidebar: true,
    }, wp.media.view.AttachmentsBrowser.prototype.defaults),

    initialize: function () {
        coreAttachmentsInitialize.apply(this, arguments);
        this.toolbar.set('search', new ImageCrateSearch({
            controller: this.controller,
            model: this.collection.props,
            priority: 60
        }).render())
    },

    createUploader: function () {
        this.uploader = new NoResults({
            controller: this.controller,
            status: false,
            message: 'Sorry, No images were found.'
        });

        this.uploader.hide();
        this.views.add(this.uploader);
    },
});

module.exports = StockPhotosBrowser;
},{"./no-results.js":7,"./search.js":8}],7:[function(require,module,exports){
/**
 * wp.media.view.NoResults
 *
 * @augments wp.media.view.UploaderInline
 */
var UploaderInline = wp.media.view.UploaderInline,
    NoResults;

NoResults = UploaderInline.extend({
    tagName: 'div',
    className: 'image-crate-no-results uploader-inline',
    template: wp.template('image-crate-no-results'),

    ready: function () {
        var $browser = this.options.$browser,
            $placeholder;

        if (this.controller.uploader) {
            $placeholder = this.$('.browser');

            // Check if we've already replaced the placeholder.
            if ($placeholder[0] === $browser[0]) {
                return;
            }

            $browser.detach().text($placeholder.text());
            $browser[0].className = 'browser button button-hero';
            $placeholder.replaceWith($browser.show());
        }

        this.refresh();
        return this;
    }
});

module.exports = NoResults;

},{}],8:[function(require,module,exports){
/**
 * wp.media.view.ImageCrateSearch
 *
 * @augments wp.media.view.Search
 */
var ImageCrateSearch = wp.media.View.extend({
    tagName: 'input',
    className: 'search ic-search',
    id: 'media-search-input',

    attributes: {
        type: 'search',
        placeholder: 'Search Images'
    },

    events: {
        'input': 'search',
        'keyup': 'search',
    },

    /**
     * @returns {wp.media.view.Search} Returns itself to allow chaining
     */
    render: function () {
        this.el.value = this.model.escape('search');
        return this;
    },

    search: function (event) {
        this.deBounceSearch(event);
    },

    /**
     * There's a bug in core where searches aren't debounced in the media library.
     * Normally, not a problem, but with external api calls or tons of image/users, ajax
     * calls could effect server performance. This fixes that for now.
     */
    deBounceSearch: _.debounce(function (event) {
        if (event.target.value) {
            this.model.set('search', event.target.value);
        } else {
            this.model.unset('search');
        }
    }, 500)

});

module.exports = ImageCrateSearch;
},{}]},{},[2]);
