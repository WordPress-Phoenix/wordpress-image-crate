/**
 * wp.media.view.NoResults
 *
 * @augments wp.media.view.UploaderInline
 */
var UploaderInline = wp.media.view.UploaderInline,
    NoResults;

NoResults = UploaderInline.extend({
    tagName: 'div',
    className: 'image-crate-no-results uploader-inline',
    template: wp.template('image-crate-no-results'),

    ready: function () {
        var $browser = this.options.$browser,
            $placeholder;

        if (this.controller.uploader) {
            $placeholder = this.$('.browser');

            // Check if we've already replaced the placeholder.
            if ($placeholder[0] === $browser[0]) {
                return;
            }

            $browser.detach().text($placeholder.text());
            $browser[0].className = 'browser button button-hero';
            $placeholder.replaceWith($browser.show());
        }

        this.refresh();
        return this;
    }
});

module.exports = NoResults;
