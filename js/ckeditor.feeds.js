/**
 * CKEditor Feeds.
 *
 * @api
 * @since 3.2.0
 */
const CombodoCKEditorFeeds = {

	/**
	 * Get AJAX items.
	 *
	 * @param options
	 * @returns {function(*): Promise<*>}
	 */
	getAjaxItems: function( options ) {
		return async function(queryText) {
			return new Promise( resolve => {
				setTimeout( () => {
					fetch(options.url + queryText)
						.then(response => {
							return response.json();
						})
						.then(json => {
							// ckeditor mandatory data
							json.data['search_data'].forEach(e => {
								e['name'] = e['friendlyname'];
								e['id'] = options['marker']+e['friendlyname'];
							});
							// return searched data
							resolve( json.data['search_data']);
						});

				}, options.throttle);
			});
		}
	},

	/**
	 * Item Renderer.
	 *
	 * @param id
	 * @returns {function(*): *}
	 */
	customItemRenderer: function( id ) {
		return function(item){
			return CombodoGlobalToolbox.RenderTemplate(id + '_items_template', item, 'ibo-mention-item')[0];
		};
	}

}

/**
 * Mention plugin.
 *
 * @param editor
 * @constructor
 */
function MentionCustomization( editor ) {

	// view > model
	editor.conversion.for( 'upcast' ).elementToAttribute( {
		view: {
			name: 'a',
			attributes: {
				href: true,
				'data-role' : true,
				'data-object-class' : true,
				'data-object-id' : true
			}
		},
		model: {
			key: 'mention',
			value: viewItem => {
				return editor.plugins.get( 'Mention' ).toMentionAttribute( viewItem, {
					link: viewItem.getAttribute( 'href' ),
					id: viewItem.getAttribute( 'data-object-id' ),
					class_name: viewItem.getAttribute( 'data-object-class' ),
					mention: 'object-mention',
				} );
			}
		},
		converterPriority: 'high'
	} );

	// model > view
	editor.conversion.for( 'downcast' ).attributeToElement( {
		model: 'mention',
		view: ( modelAttributeValue, { writer } ) => {

			// Do not convert empty attributes (lack of value means no mention).
			if ( !modelAttributeValue ) {
				return;
			}

			return writer.createAttributeElement( 'a', {
				'data-role' : 'object-mention',
				'data-object-class' : modelAttributeValue.class_name,
				'data-object-id' : modelAttributeValue.id,
				'href': modelAttributeValue.link
			}, {
				priority: 20,
				id: modelAttributeValue.uid
			} );
		},
		converterPriority: 'high'
	} );
}