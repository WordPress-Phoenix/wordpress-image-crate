/**
 * wp.media.view.StockPhotoThumb
 *
 * @augments wp.media.view.Attachment
 */
var StockPhotoThumb = wp.media.view.Attachment.extend( {
	render: function() {
		var options = _.defaults( this.model.toJSON(), {
			orientation: 'landscape',
			uploading: false,
			type: '',
			subtype: '',
			icon: '',
			filename: '',
			caption: '',
			title: '',
			dateFormatted: '',
			width: '',
			height: '',
			compat: false,
			alt: '',
			description: ''
		}, this.options );

		options.buttons = this.buttons;
		options.describe = this.controller.state().get( 'describe' );

		if ( 'image' === options.type ) {
			options.size = this.imageSize( 'thumbnail' );
		}

		options.can = {};
		if ( options.nonces ) {
			options.can.remove = !!options.nonces['delete'];
			options.can.save = !!options.nonces.update;
		}

		if ( this.controller.state().get( 'allowLocalEdits' ) ) {
			options.allowLocalEdits = true;
		}

		if ( options.uploading && !options.percent ) {
			options.percent = 0;
		}

		this.views.detach();
		this.$el.html( this.template( options ) );

		this.$el.toggleClass( 'uploading', options.uploading );

		if ( options.uploading ) {
			this.$bar = this.$( '.media-progress-bar div' );
		} else {
			delete this.$bar;
		}

		// Check if the model is selected.
		this.updateSelect();

		// Update the save status.
		this.updateSave();

		this.views.render();

		return this;
	},
} );

module.exports = StockPhotoThumb;
