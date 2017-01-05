/**
 * wp.media.view.StockPhotosBrowser
 *
 * @class
 * @augments wp.media.view.AttachmentsBrowser
 */
var ImageCrateSearch = require('./search.js'),
    coreAttachmentsInitialize  = wp.media.view.AttachmentsBrowser.prototype.initialize;

var NoResults = wp.media.view.UploaderInline.extend({
    tagName: 'div',
    className: 'image-crate-no-results',
    template: wp.template('image-crate-no-results'),
});

var StockPhotosBrowser = wp.media.view.AttachmentsBrowser.extend({
    tagName: 'div',
    className: 'image-crate attachments-browser',

    defaults: _.defaults({
        filters: false,
        search: false,
        date: false,
        display: false,
        sidebar: true,
    }, wp.media.view.AttachmentsBrowser.prototype.defaults),

    initialize: function () {
        coreAttachmentsInitialize.apply(this, arguments);
        this.toolbar.set('search', new ImageCrateSearch({
            controller: this.controller,
            model: this.collection.props,
            priority: 60
        }).render())
    },

    createUploader: function () {
        // TODO: THIS IS CAUSING A CLASS NAME ERROR
        this.uploader = new NoResults({
            controller: this.controller,
            status: false,
            message: 'No Images boi'
            // message: this.controller.isModeActive('grid') ? '' : l10n.noItemsFound,
            // canClose: this.controller.isModeActive('grid')
        });

        this.uploader.hide();
        this.views.add(this.uploader);
    },
});

module.exports = StockPhotosBrowser;