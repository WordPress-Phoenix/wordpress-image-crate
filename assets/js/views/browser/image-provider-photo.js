/* global module, wpaas_stock_photos */

var StockPhotoThumb = wp.media.view.Attachment.extend({

    // events: {
    //     // 'click': 'previewImage'
    // },
    // template: wp.template('attachment'),

    initialize: function () {

        wp.media.view.Attachment.prototype.initialize.apply(this, arguments);

       // this.listenTo(this.collection.StockPhotosProps, 'change:importing', this.toggleState);

    },

    render: function () {

        wp.media.view.Attachment.prototype.render.apply(this, arguments);

        // this.toggleState();

        return this;

    },

    // overwrite the image size pulling function
    imageSize: function (size) {

        wp.media.view.Attachment.prototype.imageSize.apply(this, arguments);

        var sizes = this.model.get('sizes');

        // reset to thumbnail as default
        size = size || 'thumbnail';

        // Use the provided image size if possible.
        if (sizes) {
            if (sizes[size]) {
                matched = sizes[size];
            } else if (sizes.large) {
                matched = sizes.large;
            } else if (sizes.thumbnail) {
                matched = sizes.thumbnail;
            } else if (sizes.full) {
                matched = sizes.full;
            }

            if (matched) {
                return _.clone(matched);
            }
        }

        return {
            url: this.model.get('url'),
            width: this.model.get('width'),
            height: this.model.get('height'),
            orientation: this.model.get('orientation')
        };
    },

    // previewImage: function (event) {
    //
    //     event.preventDefault();
    //
    //     this.collection.StockPhotosProps.set('previewing', this.model);
    //
    // },

    downloadImage: function () {

        var t = this;

        wp.media.ajax({
            data: {
                action: 'wpaas_stock_photos_download',
                filename: this.model.get('filename'),
                id: this.model.get('id'),
                nonce: this.model.get('nonces').download
            }
        }).done(function (attachment) {

            var browse = wp.media.frame.content.mode('browse');

            browse.get('gallery').collection.add(attachment);
            browse.get('selection').collection.add(attachment);

            // This will trigger all mutation observer
            wp.Uploader.queue.add(attachment);
            wp.Uploader.queue.remove(attachment);

            // @todo find a better way
            browse.get('gallery').$('li:first .thumbnail').click();

        }).fail(function () {

            // @todo

        }).always(function () {

            t.collection.StockPhotosProps.set('importing', false);
            t.toggleState();
            t.$el.blur();

        });

    }
});

module.exports = StockPhotoThumb;
