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
});

module.exports = StockPhotoThumb;
