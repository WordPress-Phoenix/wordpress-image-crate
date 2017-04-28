/**
 * wp.media.view.VerticalsFilter
 *
 * @augments wp.media.view.AttachmentFilters
 */
var VerticalsFilter = wp.media.view.AttachmentFilters.extend( {
    id: 'media-attachment-vertical-filters',

    createFilters: function () {
        var filters = {};
        var verticals = [
            { vertical: 'NFL', text: '- NFL' },
            { vertical: 'NBA', text: '- NBA' },
            { vertical: 'MLB', text: '- MLB' },
            { vertical: 'NHL', text: '- NHL' },
            { vertical: 'NCAA Basketball', text: '- NCAA: Basketball' },
            { vertical: 'NCAA Football', text: '- NCAA: Football' },
            { vertical: 'SOCCER', text: '- Soccer' },
            { vertical: 'ENT', text: 'Entertainment '}
        ];

        _.each(verticals || {}, function ( value, index ) {
            filters[ index ] = {
                text: value.text,
                props: {
                    vertical: value.vertical
                }
            };
        });

        filters.all = {
            text: 'All Sports',
            props: {
                vertical: false
            },
            priority: 10
        };
        this.filters = filters;
    }
});

module.exports = VerticalsFilter;