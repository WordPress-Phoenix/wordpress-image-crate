/* global wpaas_stock_photos */

var backBtn = wp.media.View.extend({

    tagName: 'h2',
    className: 'backBtn',

    events: {
        'click': 'goBack'
    },

    render: function () {

        //this.$el.text(wpaas_stock_photos.back_btn);
        this.$el.text('El Back Button');

        return this;

    },

    goBack: function () {

        this.unbind();
        this.remove();

        this.trigger('close');

        if (this.collection.StockPhotosProps.get('previewing')) {

            this.collection.StockPhotosProps.set('previewing', false);

        }

    }

});

module.exports = backBtn;