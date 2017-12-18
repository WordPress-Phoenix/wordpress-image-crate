/**
 * wp.media.view.VerticalsFilter
 *
 * @augments wp.media.view.AttachmentFilters
 */
var TypeFilter = wp.media.view.AttachmentFilters.extend( {
    id: 'media-attachment-type-filter',

    createFilters: function () {
        var filters = {};
        var types = [
            { type: 'adv', text: 'Advanced' }
        ];

        _.each(types || {}, function ( value, index ) {
            filters[ index ] = {
                text: value.text,
                props: {
					type: value.type
                }
            };
        });

        filters.all = {
            text: 'Simple',
            props: {
				type: 'simple'
            },
            priority: 10
        };
        this.filters = filters;
    }
});

module.exports = TypeFilter;