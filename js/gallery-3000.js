/* global gallery3000Vars */

;(function ($) {
	'use strict';

	var gallery3000 = {

		/**
		 * Set up variables and init gallery.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			if ( 'undefined' === typeof gallery3000Vars ) {
				return;
			}

			this.el = $( gallery3000Vars.galleryWrapEl );

			if ( ! $( this.el ).length ) {
				return;
			}

			this.fileFrame    = false;
			this.gallery      = $( gallery3000Vars.galleryEl );
			this.items        = [];
			this.options      = gallery3000Vars;
			this.selectedItem = 0;
			this.template     = $( gallery3000Vars.template );

			this.initGallery();
		},

		/**
		 * Set up gallery and render existing items if any.
		 *
		 * @since 1.0.0
		 */
		initGallery: function() {

			var items = JSON.parse( this.options.items );

			if ( items.length ) {
				this.renderItems( items );
			} else {
				this.el.addClass( this.options.classNameEmpty );
			}

			this.makeSortable();
			this.eventHandlers();
		},

		/**
		 * Render multiple items.
		 *
		 * @since 1.0.0
		 * @param {array} items List of item objects.
		 */
		renderItems: function( items ) {

			var count = items.length;

			for ( var i = 0; i < count; i++ ) {

				if ( i === 0 ) {
					this.el.removeClass( this.options.classNameEmpty );
				}
				
				this.renderItem( items[i] );
			}
		},

		/**
		 * Render a new item or replace an existing item.
		 *
		 * @since 1.0.0
		 * @param {object} item Item object.
		 * @param {int}    id   (Optional) ID of element to replace.
		 */
		renderItem: function( item, id ) {

			var _this = this,
				template = this.template.html().trim(),
				$template;

			var index = this.items.indexOf( parseInt( item.id, 10 ) );

			if ( index > -1 ) {
				return;
			}

			$.each( item, function ( key, value ) {
				var regex = new RegExp( '{{' + key + '}}', 'g' );
				template = template.replace( regex, value );
			});

			$template = $( template );

			if ( 'undefined' !== typeof id ) {
				// Todo: No use having data attributes in options if we're hardcoding it here.
				$( '[data-gallery-3000-item="' + id + '"]' ).replaceWith( $template );
			} else {
				$template.appendTo( this.gallery );
			}

			$template
				.on( 'click', this.options.buttonDelete, function( e ) {
					e.preventDefault();
					$( this ).blur();
					_this.deleteItem( item.id );
				})
				.on( 'click', this.options.buttonEdit, function( e ) {
					e.preventDefault();
					$( this ).blur();
					_this.editItem( item.id );
				});

			this.items.push( parseInt( item.id, 10 ) );
			this.el.removeClass( this.options.classNameEmpty );
		},

		/**
		 * Delete a gallery item.
		 *
		 * @since 1.0.0
		 * @param {int} id Attachment post ID.
		 */
		deleteItem: function( id ) {

			var index = this.items.indexOf( parseInt( id, 10 ) );

			if ( index > -1 ) {
			    this.items.splice( index, 1 );
			}

			this.gallery.find( '[data-gallery-3000-item="' + id + '"]').remove();

			if ( 0 === this.items.length ) {
				this.el.addClass( this.options.classNameEmpty );
			}
		},

		/**
		 * Edit an existing item.
		 *
		 * Sets selectedItem so openFileFrame knows which item to select.
		 *
		 * @since 1.0.0
		 * @param {int} id Item post id.
		 */
		editItem: function( id ) {

			this.selectedItem = id;
			this.openFileFrame();
		},

		/**
		 * Make gallery items sortable.
		 *
		 * @since 1.0.0
		 */
		makeSortable: function() {

			this.gallery.sortable({
				delay: 150,
				placeholder: 'sortable-placeholder',
				forcePlaceholderSize: true,
				start: function( e, ui ){
     				ui.placeholder.height( ui.helper.innerHeight() );
     				ui.placeholder.width( ui.helper.innerWidth() );
				}
			});
		},

		/**
		 * Open a file frame for selecting or editing images.
		 *
		 * Note: Somewhat copy/pasted code which could likely do with improvement.
		 *
		 * @since 1.0.0
		 */
		openFileFrame: function() {

			var _this = this,
			    title,
			    buttonText,
			    multiple,
			    exclude,
			    itemId = this.selectedItem;

			if ( this.fileFrame ) {

				// Todo: Set options on the fly or create multiple views so we don't need to call this.fileFrame.close()?
				// this.fileFrame.open();
				// return;

				this.fileFrame.close();
				this.fileFrame = null;
			}

			if ( _this.selectedItem > 0 ) {

				title = _this.options.fileFrameTitleUpdate,
				buttonText = _this.options.fileFrameButtonLabelUpdate;
				multiple = false;

				// Exclude all items in the gallery except the one we're editing.
				exclude = _this.items.slice();

				var index = exclude.indexOf( parseInt( _this.selectedItem, 10 ) );

				if ( index > -1 ) {
				    exclude.splice( index, 1 );
				}

			} else {
				title = _this.options.fileFrameTitleAdd,
				buttonText = _this.options.fileFrameButtonLabelAdd;
				multiple = true;
				exclude = _this.items;
			}

			this.fileFrame = wp.media.frames.fileFrame = wp.media({
				title: title,
				button: {
					text: buttonText
				},
				multiple: multiple,
				library: {
					exclude: exclude,
					type: 'image'
				}
			});

			this.fileFrame.on( 'select', function() {

				var attachments = _this.fileFrame.state().get( 'selection' ).toJSON();

				if ( parseInt( itemId, 10 ) > 0 ) {

					// Todo: Move to own function.
					var item = {
						id: attachments[0].id,
						url: attachments[0].sizes.thumbnail.url,
						height: attachments[0].sizes.thumbnail.height,
						width: attachments[0].sizes.thumbnail.width,
						ratio: ( attachments[0].sizes.thumbnail.height / attachments[0].sizes.thumbnail.width ) * 100
					};

					_this.renderItem( item, itemId );

				} else {
					_this.addFileFrameItems( attachments );
				}
			});

			this.fileFrame.on( 'open', function() {

				if ( exclude.length ) {
					// Hackety hack: http://wordpress.stackexchange.com/questions/78230/trigger-refresh-for-new-media-manager-in-3-5
					if ( wp.media.frames.fileFrame.content.get() !== null ) {
						_this.fileFrame.content.get().collection.props.set({ ignore: (+ new Date()) });
					} else {
						_this.fileFrame.library.props.set({ ignore: (+ new Date()) });
					}
				}

				if ( _this.selectedItem > 0 ) {
					var selection = _this.fileFrame.state().get( 'selection' );
					selection.add( wp.media.attachment( _this.selectedItem ) );
				}
			});

			this.fileFrame.on( 'close', function() {
				_this.selectedItem = 0;
			});

			this.fileFrame.open();
		},

		/**
		 * Render items from a list of attachments from the file frame.
		 *
		 * @since 1.0.0
		 * @param {array} attachments List of attachment objects from the wp.media file frame.
		 */
		addFileFrameItems: function( attachments ) {

			var item,
				items = [];

			$.each( attachments, function( i, attachment ) {

				item = {
					id: attachment.id,
					url: attachment.sizes.thumbnail.url,
					height: attachment.sizes.thumbnail.height,
					width: attachment.sizes.thumbnail.width,
					ratio: ( attachment.sizes.thumbnail.height / attachment.sizes.thumbnail.width ) * 100
				};

				items.push( item );
			});

			this.renderItems( items );
		},

		/**
		 * Meta box event handlers.
		 *
		 * @since 1.0.0
		 */
		eventHandlers: function() {

			var _this = this;

			$( this.options.buttonAdd ).on( 'click', function( e ) {
				e.preventDefault();
				$( this ).blur();
				_this.openFileFrame();
			});
		}
	};

	$(function () {
		gallery3000.init();
	});

}(jQuery));
