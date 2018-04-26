/**
 * wp.media.model.StockPhotosQuery
 *
 * A collection of attachments.
 *
 * @class
 * @augments wp.media.model.Attachments
 */
var ProviderQuery = require('./query');

var ProviderAttachments = wp.media.model.Attachments.extend({
    /**
     * Override core _requery method to accept a custom query
     *
     * @param refresh
     * @private
     */
    _requery: function (refresh) {
        var props;

        if ( this.props.get('query') ) {
            props = this.props.toJSON();
            props.cache = ( true !== refresh );
            this.mirror( ProviderQuery.get( props ) );
        }
    }
});

module.exports = ProviderAttachments;
