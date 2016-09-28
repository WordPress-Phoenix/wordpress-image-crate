
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