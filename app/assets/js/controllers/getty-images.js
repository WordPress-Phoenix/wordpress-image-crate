/**
 * wp.media.controller.GettyImagesController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var Library = wp.media.controller.Library,
	GettyImagesController;

GettyImagesController = Library.extend( {

	/**
	 * Extend the core defaults and add modify listener key values. These values are referenced when
	 * the controller is triggered.
	 */
	defaults: _.defaults( {
		id: 'getty-images',
		title: 'Getty Images',
		priority: 300,
		content: 'provider',
		router: 'image-provider',
		toolbar: 'image-provider',
		button: 'Download Getty Image',
		verticalFilter: false,
		waitForSearch: true,

		/**
		 * Any data that needs to be passed from this controller via ajax, should be passed with this object.
		 *
		 * The provider key is parsed on the backend to determine which object to use. The chosen object is then used
		 * to retrieve images from a external service.
		 */
		library: wp.media.query( { provider: 'getty-images' } )

	}, Library.prototype.defaults ),

	activate: function() {
		this.set( 'mode', this.id );
	}
} );

module.exports = GettyImagesController;