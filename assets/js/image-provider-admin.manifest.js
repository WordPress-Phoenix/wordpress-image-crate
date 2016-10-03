
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