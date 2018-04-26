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
            { vertical: 'ENTERTAINMENT', text: 'ENTERTAINMENT' },
			{ vertical: 'TRENDING TOPICS', text: 'TRENDING TOPICS' },
			{ vertical: 'EXTRA', text: 'EXTRA' },
			{ vertical: 'LOCAL', text: 'LOCAL' },
			{ vertical: 'NFL', text: 'NFL' },
			{ vertical: 'NBA', text: 'NBA' },
			{ vertical: 'MLB', text: 'MLB' },
			{ vertical: 'NHL', text: 'NHL' },
			{ vertical: 'SOCCER', text: 'SOCCER' },
			{ vertical: 'NCAABB', text: 'NCAABB' },
			{ vertical: 'NCAAF', text: 'NCAAF' },
			{ vertical: 'LIFESTYLE', text: 'LIFESTYLE' }
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
            text: 'All Verticals',
            props: {
                vertical: ''
            },
            priority: 10
        };
        this.filters = filters;
    }
});

module.exports = VerticalsFilter;