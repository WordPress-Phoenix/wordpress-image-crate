/**
 * wp.media.controller.GettyImagesController
 *
 * Custom Toolbar for downloading images
 *
 * @augments wp.media.controller.Library
 */
var ProviderToolbar = function( view ) {
	var controller = this,
		state = controller.state();

	this.selectionStatusToolbar( view );

	view.set( state.get( 'id' ), {
		style: 'primary',
		priority: 80,
		text: state.get( 'button' ),
		requires: { selection: true },

		/**
		 * @fires wp.media.controller.State#insert
		 */
		click: function() {
			var selection = state.get( 'selection' );
			var provider = state.get( 'id' );

			jQuery( '.media-toolbar-primary .media-button' )
				.text( 'Downloading...' )
				.before( '<span class="image-crate-spinner spinner is-active"></span>' )

			wp.media.ajax( {
				data: {
					action: 'image_crate_download',
					query: {
						download_url: selection.models[0].get( 'url' ),
						caption: selection.models[0].get( 'caption' ),
						provider: provider,
						filename: selection.models[0].get( 'filename' ),
						id: selection.models[0].get( 'id' )
					},
					_ajax_nonce: imagecrate.nonce
				}
			} ).done( function( attachment ) {

				// Swap back to insert state to manipulate collection.
				controller.setState( 'insert' );

				// Image may exist in the library, so we move it to the front of the line
				var browse = wp.media.frame.content.mode( 'browse' );

				// might not need this
				browse.get( 'gallery' ).collection.add( attachment );
				browse.get( 'selection' ).collection.add( attachment );

				browse.get( 'insert' ).collection.remove( attachment );
				browse.get( 'insert' ).collection.unshift( attachment );

				// This will trigger all mutation observer
				wp.Uploader.queue.add( attachment );
				wp.Uploader.queue.remove( attachment );

				browse.get( 'insert' ).$( 'li:first .thumbnail' ).click();
			} );
		}
	} );
};

module.exports = ProviderToolbar;