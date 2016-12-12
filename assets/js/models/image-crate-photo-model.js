/* global require */
var StockPhotosQuery = require('./image-crate-photos-query');

var StockPhotos = wp.media.model.Attachments.extend({

    initialize: function (models, options) {
        wp.media.model.Attachments.prototype.initialize.call(this, models, options);
    },

    // todo: bug - page query/load on scroll
    // todo: bug - state to display when first opening frame
    _requery: function (refresh) {
        var props;

        if ( this.props.get('query') ) {
            props = this.props.toJSON();
            // console.log( props );
            props.cache = ( true !== refresh );
            this.mirror( StockPhotosQuery.get( props ) );
        }
    }
});

module.exports = StockPhotos;
