var NoResults = wp.media.View.extend( {
	tagName: 'div',
	className: 'no-results-found',

	/**
	 * @returns {wp.media.view.Search} Returns itself to allow chaining
	 */
	render: function() {

		jQuery( this.el ).empty();

		jQuery( this.el ).append(
			jQuery(
				'<div class="uploader-inline image-crate-no-results">' +
				'<div class="uploader-inline-content">' +
				'<h2 class="upload-message">Sorry. No images found.</h2>' +
				'</div>' +
				'</div>'
			)
		)

		return this;
	}

} );

module.exports = NoResults;