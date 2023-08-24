/**
 * Forms handling.
 *
 * @param oWidget
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Form = function(oWidget){

	const DEPENDS_ON_SEPARATOR = ' ';

	const aSelectors = {
		dataDependsOn: '[data-depends-on]',
		dataHideWhen: '[data-hide-when]',
		dataDisableWhen: '[data-disable-when]',
		dataBlockContainer: '[data-block="container"]',
		dataAttCode: '[data-att-code="{0}"]'
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
	 * updateForm.
	 *
	 * @param aData
	 * @param sUrl
	 * @param sMethod
	 * @returns {Promise<string>}
	 */
	async function updateForm(aData, sUrl, sMethod){
		const req = await fetch(sUrl, {
			method: sMethod,
			body: aData,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'charset': 'utf-8'
			}
		});

		return await req.text();
	}

	/**
	 * changeOptions.
	 *
	 * @param oEvent
	 * @param sId
	 * @returns {Promise<void>}
	 */
	async function changeOptions(oEvent, sId){

		// retrieve field that's need to be updated
		const oDependentField = document.getElementById(sId);
		const sName = oDependentField.getAttribute('name');
		const sAttCode = oDependentField.getAttribute('data-att-code');

		// retrieve parent form
		const oForm = oDependentField.closest('form');

		// retrieve field container
		const oContainer = oDependentField.closest(aSelectors.dataBlockContainer);

		// set field container loading state
		oContainer.classList.add('loading');

		// prepare request body
		const oFormData = new FormData(oForm);
		let sRequestBody = '';
		function encode(s){ return encodeURIComponent(s).replace(/%20/g,'+'); }
		for(let pair of oFormData.entries()){
			if(typeof pair[1]=='string'){
				sRequestBody += (sRequestBody?'&':'') + encode(pair[0])+'='+encode(pair[1]);
			}
		}
		sRequestBody += '&att_code=' + oDependentField.dataset.attCode;
		sRequestBody += '&dependency_att_code=' + oEvent.target.dataset.attCode;

		// update fom
		const sUpdateFormResponse = await updateForm(sRequestBody, oForm.dataset.reloadUrl, oForm.getAttribute('method'));
		const oHtml = oToolkit.parseTextToHtml(sUpdateFormResponse);
		let oSingle = oHtml.getElementById('object_single_attribute');
		oContainer.replaceWith(oSingle.firstChild);

		// remove loading state
		oContainer.classList.remove('loading');

		// update new dependent field
		const oNewDependentField = document.querySelector(`[id$="${sAttCode}"]`);
		oNewDependentField.setAttribute('name', sName);
		oNewDependentField.setAttribute('id', sId);
		oNewDependentField.setAttribute('data-att-code', sAttCode);

		// init dynamics
		initDependencies(oContainer);
		initDynamicsInvisible(oContainer);
		initDynamicsDisable(oContainer);

		// init widgets
		oWidget.handleElement(oContainer);
	}

	/**
	 * initDependencies.
	 *
	 *  @param oElement
	 */
	function initDependencies(oElement){

		// get all dependent fields
		const aDependentsFields = oElement.querySelectorAll(aSelectors.dataDependsOn);

		// iterate throw dependent fields...
		aDependentsFields.forEach(function (oDependentField) {

			// retrieve dependency data
			const sDependsOn = oDependentField.dataset.dependsOn;

			// may have multiple dependencies
			let aDependsEls = sDependsOn.split(DEPENDS_ON_SEPARATOR);

			// iterate throw dependencies...
			aDependsEls.forEach(function(sEl){

				// retrieve dependency
				const oDependsOnElement = oElement.querySelector(`[id$="${sEl}"]`);

				// listen for changes
				if(oDependsOnElement != null){
					oDependsOnElement.addEventListener('change', (event) => changeOptions(event, oDependentField.id));
				}
			});
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
		initDependencies(element);
		initDynamicsInvisible(element);
		initDynamicsDisable(element);
		oWidget.handleElement(element);
	}

	return {
		handleElement,
	}
};













