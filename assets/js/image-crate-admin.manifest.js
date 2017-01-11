
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

                                console.log( 'attachment' );
                                console.log(attachment );

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