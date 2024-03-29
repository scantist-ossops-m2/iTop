import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView, ContextualBalloon, clickOutsideHandler } from '@ckeditor/ckeditor5-ui';
import FormView from './object-shortcut.form-view';
import './styles.css';

// plugin icon
const pluginIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-32 252c0 6.6-5.4 12-12 12h-92v92c0 6.6-5.4 12-12 12h-56c-6.6 0-12-5.4-12-12v-92H92c-6.6 0-12-5.4-12-12v-56c0-6.6 5.4-12 12-12h92v-92c0-6.6 5.4-12 12-12h56c6.6 0 12 5.4 12 12v92h92c6.6 0 12 5.4 12 12v56z"/></svg>';

export default class ObjectShortcutUI extends Plugin {
    static get requires() {
        return [ ContextualBalloon ];
    }

    _balloon: ContextualBalloon | undefined;
    formView: FormView | undefined;

    init() {
        const editor = this.editor;

        // Create the balloon and the form view.
        this._balloon = this.editor.plugins.get( ContextualBalloon );
        this.formView = this._createFormView();

        editor.ui.componentFactory.add( 'object-shortcut', () => {
            const button = new ButtonView();
            button.label = 'Insert Object Shortcut';
            button.tooltip = true;
            button.icon = pluginIcon;

            // Show the UI on button click.
            this.listenTo( button, 'execute', () => {
                this._showUI();
            } );

            return button;
        } );
    }

    _createFormView() {
        const editor = this.editor;
        const formView = new FormView( editor.locale );

        // Execute the command after clicking the "Save" button.
        this.listenTo( formView, 'submit', () => {
            // Grab values from the abbreviation and title input fields.

            let label = '';
            const labelElement = <HTMLInputElement>formView.labelInputView.fieldView.element;
            if(labelElement !== null) {
                label = labelElement.value;
            }

            let objectClass = 'object class';
            const classElement = <HTMLInputElement>formView.classInputView.fieldView.element;
            if(classElement !== null) {
                objectClass = classElement.value;
            }

            let objectReference = 'object reference';
            const referenceElement = <HTMLInputElement>formView.referenceInputView.fieldView.element;
            if(referenceElement !== null) {
                objectReference = referenceElement.value;
            }

            editor.model.change( writer => {
                const text = `[[${objectClass}:${objectReference}${label !== '' ? '|' + label : ''}]]`;
                editor.model.insertContent(writer.createText(text));
            } );

            // Hide the form view after submit.
            this._hideUI();
        } );

        // Hide the form view after clicking the "Cancel" button.
        this.listenTo( formView, 'cancel', () => {
            this._hideUI();
        } );


        const balloon = this._balloon;
        if(balloon !== undefined && balloon.view.element !== null){
            // Hide the form view when clicking outside the balloon.
            clickOutsideHandler( {
                emitter: formView,
                activator: () => balloon.visibleView === formView,
                contextElements: [ balloon.view.element ],
                callback: () => this._hideUI()
            } );
        }


        return formView;
    }

    _showUI() {

        // show balloon
        const pos = this._getBalloonPositionData();
        if(this._balloon !== undefined && this.formView !== undefined && pos !== null && pos.target !== null){
            this._balloon.add( {
                view: this.formView,
                position: {
                    target: pos.target
                }
            } );
        }

        // focus form view
        if(this.formView !== undefined){
            this.formView.focus();
        }

    }

    _hideUI() {
        if( this.formView !== undefined && this._balloon !== undefined){
            // @ts-ignore
            this.formView.labelInputView.set({value: null});
            // @ts-ignore
            this.formView.classInputView.set({value: null});
            // @ts-ignore
            this.formView.referenceInputView.set({value: null});

            if( this.formView.element !== null){
                (<HTMLFormElement>this.formView.element).reset();
            }

            // remove balloon
            this._balloon.remove( this.formView );

            // Focus the editing view
            this.editor.editing.view.focus();
        }
    }

    _getBalloonPositionData(){
        const view = this.editor.editing.view;
        const viewDocument = view.document;
        let target = null;
        const firstRange = viewDocument.selection.getFirstRange();
        if(firstRange !== null) {
            target = () => view.domConverter.viewRangeToDom(firstRange);
        }
        return {
            target
        };
    }
}