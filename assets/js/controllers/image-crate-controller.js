var ImageCrateController = wp.media.controller.Library.extend({
    defaults: _.defaults({
        id: 'image-crate',
        title: 'Image Source',
        multiple: false,
        menu: 'default',
        router: 'image-crate',
        toolbar: 'image-crate-toolbar',
        searchable: true,
        filterable: false,
        sortable: false,
        autoSelect: true,
        describe: false,
        contentUserSetting: true,
        syncSelection: false,
        priority: 800,
        isImageCrate: true
    }, wp.media.controller.Library.prototype.defaults ),

    initialize: function () {
        if (!this.get('library')) {
            this.set('library', wp.media.query({ ii: true }) );
        }
        wp.media.controller.Library.prototype.initialize.apply(this, arguments);
    }

});

module.exports = ImageCrateController;