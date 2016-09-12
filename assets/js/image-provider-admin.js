(function ($) {

    var media = wp.media,
        imageimplementor = imageimplementor || {},
        Library = media.controller.Library,
        Post = media.view.MediaFrame.Post;

    // extend the library controller for our insertion
    media.controller.ImageImplementor = Library.extend({

        // extend the defaults for our new controller
        // and merge with the parent default options
        defaults: _.defaults({
            id: 'imageimplementor',
            router: 'imageimplementor',
            toolbar: 'imageimplementor-toolbar',
            title: 'Image Implementor',
            priority: 400,
            searchable: false,
            sortable: false
        }, Library.prototype.defaults),

        // this starts the party when "Image Implementor" is clicked
        initialize: function () {

            //console.log(this.get('id') + ' has been added to the queue' );

            // If we haven't been provided a `library`, create a `Selection`.
            if (!this.get('library')) {
                this.set('library', media.query({imageimplementor: true}));
            }

            Library.prototype.initialize.apply(this, arguments);
        }

    });



    media.view.ImageImplementor = media.View.extend({

        // tagName: 'section',
        className: 'image-implementor',
        template: media.template('api-image-search'),
        // template: wp.template('attachment'),
        initialize: function() {
            return media.View.prototype.initialize.apply(this, arguments);
        }
    });
    // var AttachmentsBrowser = media.view.AttachmentsBrowser;
    // media.view.AttachmentsBrowser = AttachmentsBrowser.extend({
    //     /**
    //      * Snag the Core 3.9.2 versions as a quick fix to avoid
    //      * the breakage introduced by r29364-core
    //      */
    //
    //     className: 'attachments-browser',
    //     updateContent: function () {
    //         var view = this;
    //
    //         if (!this.attachments) {
    //             this.createAttachments();
    //         }
    //
    //         if (!this.collection.length) {
    //             this.toolbar.get('spinner').show();
    //             this.collection.more().done(function () {
    //                 if (!view.collection.length) {
    //                     view.createUploader();
    //                 }
    //                 view.toolbar.get('spinner').hide();
    //             });
    //         } else {
    //             view.toolbar.get('spinner').hide();
    //         }
    //     },
    //     /**
    //      * Empty out to avoid breakage.
    //      */
    //     toggleUploader: function () {
    //     },
    //     createUploader: function () {
    //         if ('imageimplementor' !== this.controller.state().get('id')) {
    //             return AttachmentsBrowser.prototype.createUploader.apply(this, arguments);
    //         }
    //     }
    // });







    _.extend(media.view.MediaFrame.prototype, {
        ImageImplementor: {

            activate: function() {
                console.log( 'router on' );
                //console.log(this.get('id') + ' is clicked');

                console.log(this.views.get('.media-frame-router') );
                var view = _.first(this.views.get('.media-frame-router')),
                    viewSettings = {};

                // console.log(this.frame.router.get() );

                viewSettings.upload_getty = {text: 'Getty Images', priority: 20};
                viewSettings.upload_usat = {text: 'USA Today', priority: 40};

                // Intercept and clear all incoming uploads
                wp.Uploader.queue.on('add', this.ImageImplementor.disableUpload, this);

                view.set(viewSettings);
                this.content.mode('upload_getty');

                // console.log( this.content.mode() );
            },

            disableUpload: function ( attachment ) {
                var uploader = this.uploader.uploader.uploader;
                uploader.stop();
                uploader.splice();
                attachment.destroy();
            },

            deactivate: function () {
                console.log('router off');
                wp.Uploader.queue.off('add', this.ImageImplementor.disableUpload);
            },

            contentRendered: function() {
                console.log( 'content rendered' );
            },

            uploadVideo: function () {
                this.content.set(new media.view.ImageImplementor({
                    controller: this
                }));
                return this;
            },

            createToolbar: function () {
                this.toolbar.set(new media.view.Toolbar({
                    controller: this,
                    items: {
                        insert: {
                            style: 'primary',
                            text: 'Implement Image',
                            priority: 80,
                            requires: {selection: true},

                            /**
                             * @fires wp.media.controller.State#reset
                             */
                            click: function () {
                                var state = controller.state(),
                                    selection = state.get('selection');

                                controller.close();
                                state.trigger('insert', selection).reset();
                                // var controller = this.controller,
                                //     state = controller.state(),
                                //     edit = controller.state('edit-image');
                                //
                                // edit.get('library').add(state.get('selection').models);
                                // state.trigger('reset');
                                // controller.setState('edit-image');
                            }
                        }
                    }
                }));
            },

            loadGetty: function () {
                console.log( 'getty ready' );
            },
        }
    });

    media.view.MediaFrame.Post = Post.extend({

        createStates: function () {
            Post.prototype.createStates.apply(this, arguments);
            this.states.add([new media.controller.ImageImplementor()]);
        },

        bindHandlers: function () {
            Post.prototype.bindHandlers.apply(this, arguments);

            this.on('router:create:imageimplementor', this.createRouter, this);
            this.on('router:activate:imageimplementor', this.ImageImplementor.activate, this);
            this.on('router:deactivate:imageimplementor', this.ImageImplementor.deactivate, this);

            this.on('toolbar:create:imageimplementor-toolbar', this.ImageImplementor.createToolbar, this);
            this.on('content:render:upload_getty', this.ImageImplementor.uploadVideo , this);
        }

    });

})(jQuery);