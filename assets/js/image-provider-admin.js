(function ($) {

    var media = wp.media,
        imageimplementor = imageimplementor || {},
        Library = media.controller.Library,
        Select = media.view.MediaFrame.Select,
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
            sortable: false,
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

    _.extend(media.view.MediaFrame.prototype, {
        ImageImplementor: {

            activate: function() {
                console.log( 'router on' );
                //console.log(this.get('id') + ' is clicked');

                var view = _.first(this.views.get('.media-frame-router')),
                    viewSettings = {};

                viewSettings.upload_getty = {text: 'Getty Images', priority: 20};
                viewSettings.upload_usat = {text: 'USA Today', priority: 40};

                // Intercept and clear all incoming uploads
                wp.Uploader.queue.on('add', this.ImageImplementor.disableUpload, this);


                // this.toolbar.set('dateFilterLabel', new wp.media.view.Label({
                //     value: 'Freaking work',
                //     attributes: {
                //         'for': 'media-attachment-date-filters'
                //     },
                //     priority: -75
                // }));

                view.set(viewSettings);
                this.content.mode('upload_getty');
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

            this.on('router:create:imageimplementor', this.createRouter, this);
            this.on('toolbar:create:imageimplementor-toolbar', this.ImageImplementor.createToolbar, this);
            this.on('router:render:upload_getty', this.ImageImplementor, this);

            // this.on('router:activate:upload_getty', this..activate, this);
        }

    });

})(jQuery);