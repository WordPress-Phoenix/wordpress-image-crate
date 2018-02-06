/**
 * wp.media.view.VerticalsFilter
 *
 * @augments wp.media.view.AttachmentFilters
 */
var SortFilter = wp.media.view.AttachmentFilters.extend( {
	id: 'media-attachment-sort-filters',

	createFilters: function() {
		var filters = {};
		var sortTypes = [
			{ sort: 'best_match', text: 'Best Match' },
			{ sort: 'most_popular', text: 'Most Popular' },
			{ sort: 'newest', text: 'Newest' }
		];

		_.each( sortTypes || {}, function( value, index ) {
			filters[index] = {
				text: value.text,
				props: {
					sort: value.sort
				}
			};
		} );

		filters.all = {
			text: 'Best Match',
			props: {
				sort: 'best_match'
			},
			priority: 10
		};
		this.filters = filters;
	}
} );

module.exports = SortFilter;