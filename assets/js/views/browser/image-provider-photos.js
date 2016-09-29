/* global require */

var StockPhotoThumb = require('./image-provider-photo.js');
    //StockPhotosFilter = require('./image-provider-photos-filter.js');

// var coreCreateAttachments  = wp.media.view.AttachmentsBrowser.prototype.createAttachments;
var coreAttachmentsInitialize  = wp.media.view.AttachmentsBrowser.prototype.initialize;
var coreAttachmentsCreateSingle  = wp.media.view.AttachmentsBrowser.prototype.createSingle;

var StockPhotosBrowser = wp.media.view.AttachmentsBrowser.extend({
// var StockPhotosBrowser = wp.media.view.Frame.extend({

    tagName: 'div',
    className: 'image-crate attachments-browser',
    defaults: _.defaults({
        filters: false,
        search: false,
        date: false,
        display: false,
        sidebar: false,
        AttachmentView: wp.media.view.Attachment.Library
    }, wp.media.view.AttachmentsBrowser.prototype.defaults),

    //
    // createToolbar: function () {
    //
    //     var toolbarOptions;
    //
    //     toolbarOptions = {
    //         controller: this.controller
    //     };
    //
    //     this.toolbar = new wp.media.view.Toolbar(toolbarOptions);
    //
    //     this.views.add(this.toolbar);
    //
    //     // This is required to prevent a js warning
    //     this.toolbar.set('spinner', new wp.media.view.Spinner({
    //         priority: -60
    //     }));
    //
    //     // "Filters" will return a <select>, need to render
    //     // screen reader text before
    //     // this.toolbar.set('filtersLabel', new wp.media.view.Label({
    //     //     value: wpaas_stock_photos.filter_label,
    //     //     attributes: {
    //     //         'for': 'media-attachment-filters'
    //     //     },
    //     //     priority: -80
    //     // }).render());
    //     //
    //     // // Let's put the actual category filter
    //     // this.toolbar.set('filters', new StockPhotosFilter({
    //     //     controller: this.controller,
    //     //     model: this.collection.props,
    //     //     StockPhotosProps: this.collection.StockPhotosProps,
    //     //     priority: -80
    //     // }).render());
    //
    // },
    //
    // createAttachments: function () {
    //
    //     this.attachments = new wp.media.view.Attachments({
    //         controller: this.controller,
    //         collection: this.collection,
    //         AttachmentView: StockPhotoThumb
    //     });
    //
    //     this.views.add(this.attachments);
    //
    //     this.attachmentsNoResults = new wp.media.View({
    //         controller: this.controller,
    //         tagName: 'div',
    //         className: 'uploader-inline'
    //     });
    //
    //     this.attachmentsNoResults.$el.addClass('hidden');
    //     this.attachmentsNoResults.$el.html(
    //         '<div class="uploader-inline-content has-upload-message">' +
    //         '<h2 class="upload-message">' +
    //         'No Images' +
    //         '</h2></div>'
    //     );
    //
    //     this.views.add(this.attachmentsNoResults);
    //
    // },
    //
    // disposeSingle: function () {
    //     var sidebar = this.sidebar;
    //     sidebar.unset('details');
    //     sidebar.unset('compat');
    //     sidebar.unset('display');
    //     // Hide the sidebar on mobile
    //     sidebar.$el.removeClass('visible');
    // }

});

module.exports = StockPhotosBrowser;
