/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md.
 */

import {
    View,
    LabeledFieldView,
    createLabeledInputText,
    ButtonView,
    submitHandler,
} from '@ckeditor/ckeditor5-ui';
import { icons } from '@ckeditor/ckeditor5-core';
import{ Locale } from '@ckeditor/ckeditor5-utils';

export default class FormView extends View {

    // input fields
    labelInputView: LabeledFieldView;
    classInputView: LabeledFieldView;
    referenceInputView: LabeledFieldView;

    // buttons
    saveButtonView: ButtonView;
    cancelButtonView: ButtonView;

    // child views
    childViews: any;

    constructor( locale: Locale ) {
        super( locale );

        // save button
        this.saveButtonView = this._createButton( 'Save', icons.check, 'ck-button-save' );
        this.saveButtonView.type = 'submit';

        // cancel button
        this.cancelButtonView = this._createButton( 'Cancel', icons.cancel, 'ck-button-cancel' );
        this.cancelButtonView.delegate( 'execute' ).to( this, 'cancel' );

        // create input fields
        this.labelInputView = this._createInput( 'Label' );
        this.classInputView = this._createInput( 'Object Class' );
        this.referenceInputView = this._createInput( 'Object Reference' );
        this.childViews = this.createCollection( [
            this.labelInputView,
            this.classInputView,
            this.referenceInputView,
            this.saveButtonView,
            this.cancelButtonView
        ] );

        this.setTemplate( {
            tag: 'form',
            attributes: {
                class: [ 'ck', 'ck-abbr-form' ],
                tabindex: '-1'
            },
            children: this.childViews
        } );
    }

    override render() {
        super.render();

        // Submit the form when the user clicked the save button or pressed enter in the input.
        submitHandler( {
            view: this
        } );
    }

    focus() {
        this.childViews.first.focus();
    }

    _createInput( label: string ) {
        const labeledInput = new LabeledFieldView( this.locale, createLabeledInputText );
        labeledInput.label = label;
        return labeledInput;
    }

    _createButton( label: string, icon: string, className: string ) {
        const button = new ButtonView();
        button.set( {
            label,
            icon,
            tooltip: true,
            class: className
        } );
        return button;
    }
}