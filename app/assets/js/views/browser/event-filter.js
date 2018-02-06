/**
 * wp.media.view.EventFilter
 *
 * @augments wp.media.view.AttachmentFilters
 */
var EventFilter = wp.media.view.AttachmentFilters.extend( {
    id: 'media-attachment-type-filter',

    createFilters: function () {
        var filters = {};

        filters.all = {
            text: 'All',
            props: {
				type: 'all'
            },
            priority: 10
        };
        this.filters = filters;
    }
});

module.exports = EventFilter;