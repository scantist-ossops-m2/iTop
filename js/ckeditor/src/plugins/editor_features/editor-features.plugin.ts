import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import {Element} from "@ckeditor/ckeditor5-engine";

/**
 * EditorFeatures Plugin.
 *
 * - trigger update event when editor is ready
 * - dispatch submit event on the closest editor form when Ctrl+Enter pressed
 * - converter for mentions
 * - appends ibo-is-html-content class
 */
export default class EditorFeatures extends Plugin {

    static get pluginName() {
        return 'EditorFeatures';
    }

    init() {

        // retrieve editor instance
        const editor = this.editor;

        // trigger update event when editor is ready
        editor.ui.on( 'ready', () => {

            if(editor.ui.element !== null){
                const event = new Event("update");
                editor.ui.element.dispatchEvent(event);
                console.log('CKE5 - EditorFeatures - Dispatch update event on ready');
            }

            for (const element of document.getElementsByClassName('ck-body-wrapper')) {
                element.classList.add('ck-reset_all-excluded');
                console.log('CKE5 - EditorFeatures - Apply ck-reset_all-excluded to ck-body-wrapper on ready');
            }
        });

        // dispatch submit event on the closest editor form when Ctrl+Enter pressed
        editor.keystrokes.set( 'Ctrl+Enter', ( data, stop ) => {
            if(editor.ui.element !== null){
                const form = editor.ui.element.closest('form');
                if(form !== null){
                    const event = new Event("submit");
                    form.dispatchEvent(event);
                    console.log('CKE5 - EditorFeatures - Dispatch submit on Ctrl+Enter');
                }
            }
        });

        // convert view > model
        editor.conversion.for('upcast').elementToAttribute({
            view: {
                name: 'a',
                attributes: {
                    href: true,
                    'data-role': true,
                    'data-object-class': true,
                    'data-object-id': true
                }
            },
            model: {
                key: 'mention',
                value: (viewItem: Element) => {
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

        // convert model > view
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

        // appends ibo-is-html-content class
        editor.editing.view.change( writer => {
            const rootElement = editor.editing.view.document.getRoot();
            if(rootElement !== null){
                writer.addClass( 'ibo-is-html-content', rootElement);
                console.log('CKE5 - EditorFeatures - Apply ibo-is-html-content to editing view');
            }
        });
    }
}

