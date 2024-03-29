import { Plugin } from '@ckeditor/ckeditor5-core';
import { ContextualBalloon } from '@ckeditor/ckeditor5-ui';
import FormView from './object-shortcut.form-view';
import './styles.css';
export default class ObjectShortcutUI extends Plugin {
    static get requires(): (typeof ContextualBalloon)[];
    _balloon: ContextualBalloon | undefined;
    formView: FormView | undefined;
    init(): void;
    _createFormView(): FormView;
    _showUI(): void;
    _hideUI(): void;
    _getBalloonPositionData(): {
        target: (() => Range) | null;
    };
}
