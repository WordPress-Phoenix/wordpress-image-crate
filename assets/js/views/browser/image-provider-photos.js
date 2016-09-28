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
