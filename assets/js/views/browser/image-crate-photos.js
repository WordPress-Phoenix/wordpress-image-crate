/* global require */

var StockPhotoThumb = require('./image-crate-photo.js'),
    coreAttachmentsInitialize  = wp.media.view.AttachmentsBrowser.prototype.initialize,
    coreAttachmentsCreateSingle  = wp.media.view.AttachmentsBrowser.prototype.createSingle;

var StockPhotosBrowser = wp.media.view.AttachmentsBrowser.extend({

    tagName: 'div',
    className: 'image-crate attachments-browser',
    events: {
        'keyup #media-search-input': 'debounceSearch'
    },
    defaults: _.defaults({
        filters: false,
        search: false,
        date: false,
        display: false,
        sidebar: true,
        // AttachmentView: wp.media.view.Attachment.Library
        AttachmentView: StockPhotoThumb
    }, wp.media.view.AttachmentsBrowser.prototype.defaults),

    debounceSearch: function () {
        if (this._searchTimeout) {
            window.clearTimeout(this._searchTimeout);
        }
        this._searchTimeout = window.setTimeout(this.search, 400);
    },

});

module.exports = StockPhotosBrowser;
