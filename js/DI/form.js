
const DIForm = function(reload_url){

	const SELECTOR = '[data-block="container"]';

	/**
	 * hideEmptyContainers.
	 *
	 * The purpose of this function is to hide empty containers.
	 * Ex: FieldSetType with no children
	 *
	 */
	const hideEmptyContainers = function(oElement){
		$('.combodo-field-set', oElement).each(function(e){
			$(this).parent().toggle($(this).children().length !== 0);
		});
	};

	/**
	 * parseTextToHtml.
	 *
	 * @param sText
	 * @returns {Document}
	 */
	const parseTextToHtml = (sText) => {
		const oParser = new DOMParser();
		return oParser.parseFromString(sText, 'text/html');
	};

	/**
	 * updateForm.
	 *
	 * @param aData
	 * @param sUrl
	 * @param sMethod
	 * @returns {Promise<string>}
	 */
	const updateForm = async (aData, sUrl, sMethod) => {
		const req = await fetch(sUrl, {
			method: sMethod,
			body: aData,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'charset': 'utf-8'
			}
		});

		return await req.text();
	};

	/**
	 * changeOptions.
	 *
	 * @param oEvent
	 * @param sId
	 * @returns {Promise<void>}
	 */
	const changeOptions = async (oEvent, sId) => {

		// retrieve field that's need to be updated
		const oDependentField = document.getElementById(sId);
		const sName = oDependentField.getAttribute('name');
		const sAttCode = oDependentField.getAttribute('data-att-code');

		// retrieve parent form
		const oForm = oDependentField.closest('form');

		// retrieve field container
		const oContainer = oDependentField.closest('[data-block="container"]');

		// set field container loading state
		oContainer.classList.add('loading');

		// prepare quest data
		let sRequestBody = 'object_single_attribute['+oEvent.target.dataset.attCode + ']=' + oEvent.target.value;
		sRequestBody += '&att_code=' + oDependentField.dataset.attCode;
		sRequestBody += '&dependency_att_code=' + oEvent.target.dataset.attCode;

		// update fom
		const sUpdateFormResponse = await updateForm(sRequestBody, reload_url, oForm.getAttribute('method'));
		const oHtml = parseTextToHtml(sUpdateFormResponse);
		let oSingle = oHtml.getElementById('object_single_attribute');
		oContainer.innerHTML = oSingle.innerHTML;

		// remove loading state
		oContainer.classList.remove('loading');

		// update new dependent field
		const oNewDependentField = document.querySelector(`[id$="${sAttCode}"]`);
		oNewDependentField.setAttribute('name', sName);
		oNewDependentField.setAttribute('id', sId);
		oNewDependentField.setAttribute('data-att-code', sAttCode);

		// init dynamics
		initDynamicsInvisible(oContainer);
		initDynamicsDisable(oContainer);

		// init widgets
		initWidgets(oContainer);
	};

	/**
	 * initDependencies.
	 *
	 *  @param oElement
	 */
	const initDependencies = function(oElement){

		// get all dependent fields
		const aDependentsFields = oElement.querySelectorAll('[data-depends-on]');

		// iterate throw dependent fields...
		aDependentsFields.forEach(function (oDependentField) {

			// retrieve dependency data
			const sDependsOn = oDependentField.dataset.dependsOn;

			// may have multiple dependencies
			let aDependsEls = sDependsOn.split(' ');

			// iterate throw dependencies...
			aDependsEls.forEach(function(sEl){

				// retrieve dependency
				const oDependsOnElement = document.querySelector(`[id$="${sEl}"]`);

				// listen for changes
				if(oDependsOnElement != null){
					oDependsOnElement.addEventListener('change', (event) => changeOptions(event, oDependentField.id));
				}
			});
		});
	};

	/**
	 * initDynamicsInvisible.
	 *
	 *  @param oElement
	 */
	const initDynamicsInvisible = function(oElement){

		// get all dynamic hide fields
		const aInvisibleFields = oElement.querySelectorAll('[data-hide-when]');

		// iterate throw fields...
		aInvisibleFields.forEach(function (oInvisibleField) {

			// retrieve condition
			const aHideWhenCondition = JSON.parse(oInvisibleField.dataset.hideWhen);

			// retrieve condition data
			const oHideWhenElement = document.querySelector(`[data-att-code="${aHideWhenCondition.att_code}"]`);

			// initial hidden state
			oInvisibleField.closest(SELECTOR).hidden = (oHideWhenElement.value === aHideWhenCondition.value);

			// listen for changes
			oHideWhenElement.addEventListener('change', (e) => {
				oInvisibleField.closest(SELECTOR).hidden = (e.target.value === aHideWhenCondition.value);
				oInvisibleField.closest(SELECTOR).style.visibility = (e.target.value === aHideWhenCondition.value) ? 'hidden' : '';
			});
		});

	};

	/**
	 * initDynamicsDisable.
	 *
	 * @param oElement
	 */
	const initDynamicsDisable = function(oElement){

		// get all dynamic hide fields
		const aDisabledFields = oElement.querySelectorAll('[data-disable-when]');

		// iterate throw fields...
		aDisabledFields.forEach(function (oDisabledField) {

			// retrieve condition
			const aDisableWhenCondition = JSON.parse(oDisabledField.dataset.disableWhen);

			// retrieve condition data
			const oDisableWhenElement = document.querySelector(`[data-att-code="${aDisableWhenCondition.att_code}"]`);

			// initial disabled state
			oDisabledField.closest(SELECTOR).disabled = (oDisableWhenElement.value === aDisableWhenCondition.value);

			// listen for changes
			oDisableWhenElement.addEventListener('change', (e) => {
				oDisabledField.closest(SELECTOR).disabled  = (e.target.value === aDisableWhenCondition.value);
			});
		});
	};

	/**
	 * initWidgets.
	 *
	 * @param oElement
	 */
	const initWidgets = function(oElement){

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

	};

	/**
	 * handleElement.
	 *
	 * @param element
	 */
	const handleElement = function(element){
		hideEmptyContainers(element);
		initDependencies(element);
		initWidgets(element);
		initDynamicsInvisible(element);
		initDynamicsDisable(element);
	}

	return {
		handleElement
	}
};













