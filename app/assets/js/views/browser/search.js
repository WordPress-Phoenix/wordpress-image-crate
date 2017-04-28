/**
 * wp.media.view.ImageCrateSearch
 *
 * @augments wp.media.view.Search
 */
var ImageCrateSearch = wp.media.View.extend({
    tagName: 'input',
    className: 'search ic-search',
    id: 'media-search-input',

    attributes: {
        type: 'search',
        placeholder: 'Search Images'
    },

    events: {
        'input': 'search',
        'keyup': 'search'
    },

    initialize: function() {
        if ( this.model.get( 'search' ) === undefined ) {
            this.model.set( 'search', imagecrate.default_search );
        }
    },

    /**
     * @returns {wp.media.view.Search} Returns itself to allow chaining
     */
    render: function () {
        this.el.value = this.model.get( 'search' ) === undefined ? imagecrate.default_search : this.model.escape( 'search' );
        return this;
    },

    search: function (event) {
        this.deBounceSearch(event);
    },

    /**
     * There's a bug in core where searches aren't de-bounced in the media library.
     * Normally, not a problem, but with external api calls or tons of image/users, ajax
     * calls could effect server performance. This fixes that for now.
     */
    deBounceSearch: _.debounce(function (event) {
        if (event.target.value) {
            this.model.set('search', event.target.value);
        } else {
            this.model.unset('search');
        }
    }, 500)

});

module.exports = ImageCrateSearch;