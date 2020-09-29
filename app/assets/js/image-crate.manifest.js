(function ($) {
	$(function () {

        /**
         * Image Crate Manifest - Adding custom controllers to the WordPress media modal.
         *
         * The main effort of this project is to add multiple image providers in a native WordPress way. This is
         * executed by extending the Post MediaFrame {VVV/www/wordpress-develop/src/wp-includes/js/media/views/frame/post.js}
         *
         */
		var imagecrate = imagecrate || {};

        // Store the core post view.
		var corePost = wp.media.view.MediaFrame.Post;

		// Controllers
		imagecrate.ImageExchangeController = require('./controllers/image-exchange.js');
		imagecrate.GettyImagesController = require('./controllers/getty-images.js');
		imagecrate.ImagnController = require('./controllers/imagn.js');

		// Attachment Models
        imagecrate.ProviderAttachments = require('./models/attachments.js');

		// Views
		imagecrate.ProviderToolbar = require('./views/toolbars/provider.js');
        imagecrate.ProviderPhotosBrowser = require('./views/browser/attachments.js');

		/**
		 * Add controllers to the media modal Post Frame
		 */
		wp.media.view.MediaFrame.Post = corePost.extend({

            /**
             * If you want to extend the function body from a parent object you need to call prototype.functionName.
             *
             * This is similar to using `parent::__construct();` in php.
             */
            createStates: function () {
				corePost.prototype.createStates.apply(this, arguments);

                /**
                 * Adding states adds menu items to the left menu on the media modal.
                 */
				this.states.add([
					new imagecrate.GettyImagesController,
					new imagecrate.ImageExchangeController,
					new imagecrate.ImagnController,
				]);
			},

            /**
             * Assign handlers to controllers.
             *
             * `content:create:provider` is a listener assignment for an event that is triggered when a provider
             * controller is clicked. When this event is triggered, the callback is fired and any listeners subscribed
             * to the event, will update their views.
             */
            bindHandlers: function () {
				corePost.prototype.bindHandlers.apply(this, arguments);

				this.on('toolbar:create:image-provider', this.createToolbar, this);
				this.on('toolbar:render:image-provider', imagecrate.ProviderToolbar, this);

                this.on('router:create:image-provider', this.createRouter, this);
                this.on('router:render:image-provider', this.providerRouter, this);

                this.on('content:create:provider', this.providerContent, this);
			},

            /**
             * Load images from an external source.
             *
             * @param contentRegion
             */
            providerContent: function( contentRegion ) {
                var state = this.state(),
                    id = state.get('id'),
                    collection = state.get('image_crate_photos'),
                    selection = state.get('selection');

                if (_.isUndefined(collection)) {
                    collection = new imagecrate.ProviderAttachments(
                        null,
                        {
                            /**
                             * Passing the props from the controller is important here. The provider type is set when
                             * the controller is instantiated. When the ajax call is sent, provider type passed as a
                             * request param. That value is then used to create new object to get images from the
                             * requested provider.
                             */
                            props: state.get('library').props.toJSON()
                        }
                    );

                    // Reference the state if needed later
                    state.set('image_crate_photos', collection);
                }

                /**
                 * Set main content view to display external images.
                 *
                 * @see /assets/js/views/browser/attachments.js
                 */
                contentRegion.view = new imagecrate.ProviderPhotosBrowser({
                    tagName: 'div',
                    className: id + ' image-crate attachments-browser',
                    controller: this,
                    collection: collection,
                    selection: selection,
                    model: state,
                    filters: true,
                    search: true,
                });
            },

            /**
             * When the router listener is fired, the view updates the tabs located above the image browser.
             *
             * If only one object is passed, the tab view will not display. Priority controls render order.
             */
            providerRouter: function (routerView) {
                routerView.set({

                    /*
                     * The naming of this object is important here. When this router is rendered,
                     * 'content:create:provider' is trigger and the content is updated.
                     */
                    provider: {
                        text: 'Provider',
                        priority: 20
                    }
                });
            }
		});
	});
})(jQuery);
