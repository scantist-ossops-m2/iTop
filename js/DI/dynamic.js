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
		dataAttributeContainer: '[data-block="attribute_container"]',
		dataAllBlocks: '[data-block]'
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
			if(aHideWhenCondition === null){
				return;
			}

			// retrieve condition data
			const oHideWhenElement = oElement.querySelector(String.format(aSelectors.dataAttCode, aHideWhenCondition.att_code));
			if(oHideWhenElement === null){
				return;
			}

			// retrieve container
			const oContainer = oInvisibleField.closest(aSelectors.dataAllBlocks);

			// initial hidden state
			oContainer.hidden = (oHideWhenElement.value === aHideWhenCondition.value);

			// listen for changes
			oHideWhenElement.addEventListener('change', (e) => {
				oContainer.hidden = (e.target.value === aHideWhenCondition.value);
				oContainer.style.visibility = (e.target.value === aHideWhenCondition.value) ? 'hidden' : '';
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
			if(aDisableWhenCondition === null){
				return;
			}

			// retrieve condition data
			const oDisableWhenElement = oElement.querySelector(`[data-att-code="${aDisableWhenCondition.att_code}"]`);
			if(oDisableWhenElement === null){
				return;
			}

			// retrieve container
			const oContainer = oDisabledField.closest(aSelectors.dataAllBlocks);

			// initial disabled state
			oContainer.disabled = (oDisableWhenElement.value === aDisableWhenCondition.value);

			// listen for changes
			oDisableWhenElement.addEventListener('change', (e) => {
				oContainer.disabled  = (e.target.value === aDisableWhenCondition.value);
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













