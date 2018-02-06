/**
 * wp.media.controller.ImageCrateController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var ImageCrateController = wp.media.controller.Library.extend( {
	defaults: _.defaults( {
		id: 'image-crate',
		title: imageCrate.page_title,
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
		priority: 8000,
		isImageCrate: true
	}, wp.media.controller.Library.prototype.defaults ),

	initialize: function() {

		if ( !this.get( 'library' ) ) {
			this.set( 'library', wp.media.query( { imageCrate: true } ) );
		}
		wp.media.controller.Library.prototype.initialize.apply( this, arguments );

	}
} );

module.exports = ImageCrateController;
