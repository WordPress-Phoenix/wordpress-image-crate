/* global require */

var StockPhotoThumb = require('./image-crate-photo.js'),
    ImageCrateSearch = require('./search.js'),
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
        // AttachmentView: wp.media.view.Attachment.Library
        AttachmentView: StockPhotoThumb
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
