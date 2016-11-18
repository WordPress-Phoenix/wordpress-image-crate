/**
 * There's a bug in core where searches aren't debounced in the media library.
 * Normally, not a problem, but with external api calls or tons of image/users, ajax
 * calls could effect server performance. This fixes that for now.
 */

var ImageCrateSearch = wp.media.View.extend({
    tagName: 'input',
    className: 'search ic-search',
    id: 'media-search-input',

    attributes: {
        type: 'search',
        placeholder: 'Search Provider'
    },

    events: {
        'input': 'search',
        'keyup': 'search',
    },

    /**
     * @returns {wp.media.view.Search} Returns itself to allow chaining
     */
    render: function () {
        this.el.value = this.model.escape('search');
        return this;
    },

    search: function (event) {
        this.deBounceSearch(event);
    },

    deBounceSearch: _.debounce(function (event) {
        if (event.target.value) {
            this.model.set('search', event.target.value);
        } else {
            this.model.unset('search');
        }
    }, 500)

});

module.exports = ImageCrateSearch;