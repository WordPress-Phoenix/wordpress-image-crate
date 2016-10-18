
var ImageCrateController = require('./controllers/image-crate-controller.js'),
    StockPhotosModel = require('./models/image-crate-photo-model.js'),
    StockPhotoBrowser = require('./views/browser/image-crate-photos.js'),
    coreCreateStates = wp.media.view.MediaFrame.Post.prototype.createStates,
    coreBindHandlers = wp.media.view.MediaFrame.Select.prototype.bindHandlers;

_.extend( wp.media.view.MediaFrame.prototype, {
    image_crate: {
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
                        text: 'Download Image',
                        priority: 80,
                        requires: {
                            library: true,
                            selection: true
                        },

                        click: function () {
                            var state = controller.state(),
                                selection = state.get('selection');

                            this.$el.attr('disabled', 'disabled')
                                    .text('Downloading');

                            wp.media.ajax({
                                data: {
                                    action: 'image_crate_download',
                                    filename: selection.models[0].get('filename'),
                                    id: selection.models[0].get('id'),
                                    download_uri: selection.models[0].get('download_uri')
                                    // nonce: this.model.get('nonces').download
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

                            // controller.close();
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
                            // search: 'airplane',
                            // posts_per_page: 5,
                            // paged: 1
                        }
                    }
                );

                // Reference the state if needed later
                state.set('image_crate_photos', collection);
            }

            this.content.set( new StockPhotoBrowser({
                className: 'image-crate attachments-browser',
                controller: this,
                collection: collection,
                selection: selection,
                model: state,
                filters: false,
                date: false,
            }) );

        },

        loadGetty: function () {
            console.log('getty ready');
        }
    }
});

// var coreAttachmentRender = wp.media.view.Attachment.prototype.render;
// wp.media.view.Attachment.prototype.render = function() {
//     var options = this.options || {};
//     if ('image-crate' == this.controller.state().get('id')) {
//         //todo: medium sizes are loading for some reason, needs to load thumbnail
//         // var sizes = this.model.get('sizes');
//         // options.size = sizes.thumbnail.url;
//     }
//     coreAttachmentRender.apply( this, arguments );
//
//     return this;
// };


wp.media.view.MediaFrame.Select.prototype.bindHandlers = function () {
    coreBindHandlers.apply(this, arguments);

    this.on('router:create:image-crate', this.createRouter, this);
    this.on('router:activate:image-crate', this.image_crate.activate, this);
    this.on('router:deactivate:image-crate', this.deactivate, this);

    this.on('toolbar:create:image-crate-toolbar', this.image_crate.createToolbar, this);

    this.on('content:render:getty', this.image_crate.loadGetty, this);
    this.on('content:render:usatoday', this.image_crate.loadUSAT, this);

};

wp.media.view.MediaFrame.Post.prototype.createStates = function () {
    coreCreateStates.apply(this, arguments);
    this.states.add(new ImageCrateController);
};