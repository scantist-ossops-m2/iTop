/**
 * Dynamics handling.
 *
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Dynamic = function(){

	const aSelectors = {
		dataHideWhen: '[data-hide-when]',
		dataDisableWhen: '[data-disable-when]',
		dataAttCode: '[data-att-code="{0}"]',
		dataBlockContainer: '[data-block="container"]',
	};

	/**
	 * hideEmptyContainers.
	 *
	 * The purpose of this function is to hide empty containers.
	 * Ex: FieldSetType with no children
	 *
	 */
	function hideEmptyContainers(oElement){
		$('.combodo-field-set', oElement).each(function(){
			$(this).parent().toggle($(this).children().length !== 0);
		});
	}

	/**
	 * initDynamicsInvisible.
	 *
	 *  @param oElement
	 */
	function initDynamicsInvisible(oElement){

		// get all dynamic hide fields
		const aInvisibleFields = oElement.querySelectorAll(aSelectors.dataHideWhen);

		// iterate throw fields...
		aInvisibleFields.forEach(function (oInvisibleField) {

			// retrieve condition
			const aHideWhenCondition = JSON.parse(oInvisibleField.dataset.hideWhen);

			// retrieve condition data
			const oHideWhenElement = oElement.querySelector(String.format(aSelectors.dataAttCode, aHideWhenCondition.att_code));

			// initial hidden state
			oInvisibleField.closest(aSelectors.dataBlockContainer).hidden = (oHideWhenElement.value === aHideWhenCondition.value);

			// listen for changes
			oHideWhenElement.addEventListener('change', (e) => {
				oInvisibleField.closest(aSelectors.dataBlockContainer).hidden = (e.target.value === aHideWhenCondition.value);
				oInvisibleField.closest(aSelectors.dataBlockContainer).style.visibility = (e.target.value === aHideWhenCondition.value) ? 'hidden' : '';
			});
		});

	}

	/**
	 * initDynamicsDisable.
	 *
	 * @param oElement
	 */
	function initDynamicsDisable(oElement){

		// get all dynamic hide fields
		const aDisabledFields = oElement.querySelectorAll(aSelectors.dataDisableWhen);

		// iterate throw fields...
		aDisabledFields.forEach(function (oDisabledField) {

			// retrieve condition
			const aDisableWhenCondition = JSON.parse(oDisabledField.dataset.disableWhen);

			// retrieve condition data
			const oDisableWhenElement = oElement.querySelector(`[data-att-code="${aDisableWhenCondition.att_code}"]`);

			// initial disabled state
			oDisabledField.closest(aSelectors.dataBlockContainer).disabled = (oDisableWhenElement.value === aDisableWhenCondition.value);

			// listen for changes
			oDisableWhenElement.addEventListener('change', (e) => {
				oDisabledField.closest(aSelectors.dataBlockContainer).disabled  = (e.target.value === aDisableWhenCondition.value);
			});
		});
	}

	/**
	 * handleElement.
	 *
	 * @param element
	 */
	function handleElement(element){
		hideEmptyContainers(element);
		initDynamicsInvisible(element);
		initDynamicsDisable(element);
	}

	return {
		handleElement,
	}
};













