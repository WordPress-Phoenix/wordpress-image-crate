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
            priority: 200,
            searchable: false,
            sortable: false,
            state: 'imageimplementor'
        }, Library.prototype.defaults),

        // this is fired when 'Add Media' is pressed
        initialize: function () {

            // console.log(this.get('router') );

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
                this.content.mode('browse');


            },

            createToolbar: function(){
                var controller = this;
                this.toolbar.set(new media.view.Toolbar({
                    controller: this,
                    items: {
                        insert: {
                            style: 'primary',
                            text: 'Implement Image',
                            priority: 80,
                            requires: {
                                library: true,
                                selection: true
                            },

                            click: function () {
                                var state = controller.state(),
                                    selection = state.get('selection');
                                //
                                // controller.close();
                                // state.trigger('videopress:insert', selection).reset();
                            }
                        }
                    }
                }));
            },




        }
    });

    media.view.MediaFrame.ImageImplementor = Post.extend({

        bindHandlers: function () {
            Post.prototype.bindHandlers.apply(this, arguments);

            this.on('router:create:imageimplementor', this.createRouter, this);
            this.on('router:activate:imageimplementor', this.ImageImplementor.activate, this);
            this.on('router:deactivate:imageimplementor', this.ImageImplementor.deactivate, this);

            this.on('toolbar:create:imageimplementor-toolbar', this.ImageImplementor.createToolbar, this);
        },

        createStates: function () {
            Post.prototype.createStates.apply(this, arguments);
            this.states.add([
                new media.controller.ImageImplementor()
            ]);
        },

        browseRouter: function (routerView) {

            // routerView.set({
            //     upload: {
            //         text: 'UUUULLOOO',
            //         priority: 20
            //     },
            //     browse: {
            //         text: 'GET IMAGE',
            //         priority: 40
            //     }
            // });

        },

    });


})(jQuery);