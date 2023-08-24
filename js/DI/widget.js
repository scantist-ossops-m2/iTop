/**
 * Widgets handling.
 *
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Widget = function(){

	/**
	 * initWidgets.
	 *
	 * @param oElement
	 */
	function initWidgets(oElement){

		// get all widgets
		const aWidgetFields = oElement.querySelectorAll('[data-widget]');

		// iterate throw widgets...
		aWidgetFields.forEach(function (widgetField) {

			// initialize widget
			const sWidgetName = widgetField.dataset.widget;
			const oWidget = eval(`$(widgetField).${sWidgetName}()`);
			console.log('Init widget: ' + sWidgetName);
			console.log(oWidget);
		});

	}

	/**
	 * handleElement.
	 *
	 * @param element
	 */
	function handleElement(element){
		initWidgets(element);
	}

	return {
		handleElement,
	}
};













