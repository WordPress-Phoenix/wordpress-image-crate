/* global require */

var StockPhotoThumb = require('./image-provider-photo.js'),
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
        sidebar: false,
        //AttachmentView: wp.media.view.Attachment.Library
        AttachmentView: StockPhotoThumb
    }, wp.media.view.AttachmentsBrowser.prototype.defaults),

});

module.exports = StockPhotosBrowser;
