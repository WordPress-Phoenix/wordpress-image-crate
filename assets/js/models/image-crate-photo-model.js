/**
 * wp.media.model.StockPhotosQuery
 *
 * A collection of attachments.
 *
 * @class
 * @augments wp.media.model.Attachments
 */
var StockPhotosQuery = require('./image-crate-photos-query');

var StockPhotos = wp.media.model.Attachments.extend({

    initialize: function (models, options) {
        wp.media.model.Attachments.prototype.initialize.call(this, models, options);
    },
    // todo: bug - state to display when first opening frame
    _requery: function (refresh) {
        var props;

        if ( this.props.get('query') ) {
            props = this.props.toJSON();
            props.cache = ( true !== refresh );
            this.mirror( StockPhotosQuery.get( props ) );
        }
    }
});

module.exports = StockPhotos;
