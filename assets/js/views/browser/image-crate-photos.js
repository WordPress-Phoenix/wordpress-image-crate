/* global require */

var ImageCrateSearch = require('./search.js'),
    coreAttachmentsInitialize  = wp.media.view.AttachmentsBrowser.prototype.initialize,
    coreAttachmentsCreateSingle  = wp.media.view.AttachmentsBrowser.prototype.createSingle;

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

    }

});

module.exports = StockPhotosBrowser;
