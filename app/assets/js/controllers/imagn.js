/**
 * wp.media.controller.ImagnController
 *
 * A state for downloading images from an external image source
 *
 * @augments wp.media.controller.Library
 */
var Library = wp.media.controller.Library,
	ImagnController;

ImagnController = Library.extend( {

	/**
	 * Extend the core defaults and add modify listener key values. These values are referenced when
	 * the controller is triggered.
	 */
	defaults: _.defaults( {
		id: 'imagn',
		title: 'Imagn Images (Use First)',
		priority: 280,
		content: 'provider',
		router: 'image-provider',
		toolbar: 'image-provider',
		button: 'Download Imagn Image',
		verticalFilter: false,
		waitForSearch: true,

		/**
		 * Any data that needs to be passed from this controller via ajax, should be passed with this object.
		 *
		 * The provider key is parsed on the backend to determine which object to use. The chosen object is then used
		 * to retrieve images from a external service.
		 */
		library: wp.media.query( { provider: 'imagn' } )

	}, Library.prototype.defaults ),

	activate: function() {
		this.set( 'mode', this.id );
	}
} );

module.exports = ImagnController;