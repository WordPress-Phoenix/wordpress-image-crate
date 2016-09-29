var ImageProviderController = wp.media.controller.Library.extend({
    defaults: _.defaults({
        id: 'ii',
        title: 'Image Source',
        multiple: false,
        // content: 'getty',
        menu: 'default',
        router: 'ii',
        toolbar: 'ii-toolbar',
        searchable: true,
        filterable: false,
        sortable: false,
        autoSelect: true,
        describe: false,
        contentUserSetting: true,
        syncSelection: false,
        priority: 800
    }, wp.media.controller.Library.prototype.defaults ),

    initialize: function () {
        if (!this.get('library')) {
            this.set('library', wp.media.query({ ii: true }) );
        }
        wp.media.controller.Library.prototype.initialize.apply(this, arguments);
    }

});

module.exports = ImageProviderController;