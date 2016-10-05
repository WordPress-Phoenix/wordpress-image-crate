/* global module, wpaas_stock_photos */
var StockPhotoThumb = wp.media.view.Attachment.extend({

    initialize: function () {

        wp.media.view.Attachment.prototype.initialize.apply(this, arguments);
        _.extend(this.events, {
            'click': 'imageCrateActiveMode'
        });

    },

    imageCrateActiveMode: function() {
        console.log('is dumb');
    },

    render: function () {

        var options = this.options || {};
        if ('image' === options.type) {
            options.size = this.imageSize('thumbnail');
        }

        wp.media.view.Attachment.prototype.render.apply(this, arguments);

        return this;
    }
    // downloadImage: function () {
    //
    //     var t = this;
    //
    //     wp.media.ajax({
    //         data: {
    //             action: 'image_crate_download',
    //             filename: this.model.get('filename'),
    //             id: this.model.get('id'),
    //             nonce: this.model.get('nonces').download
    //         }
    //     }).done(function (attachment) {
    //
    //         var browse = wp.media.frame.content.mode('browse');
    //
    //         browse.get('gallery').collection.add(attachment);
    //         browse.get('selection').collection.add(attachment);
    //
    //         // This will trigger all mutation observer
    //         wp.Uploader.queue.add(attachment);
    //         wp.Uploader.queue.remove(attachment);
    //
    //         // @todo find a better way
    //         browse.get('gallery').$('li:first .thumbnail').click();
    //
    //     }).fail(function () {
    //
    //         // @todo
    //
    //     }).always(function () {
    //
    //         // t.collection.StockPhotosProps.set('importing', false);
    //         // t.toggleState();
    //         t.$el.blur();
    //
    //     });
    //
    // }
});

module.exports = StockPhotoThumb;
