/**
 * Forms handling.
 *
 * @param oWidget
 * @param oDynamic
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Form = function(oWidget, oDynamic){

	const DEPENDS_ON_SEPARATOR = ' ';

	const LOADING_MESSAGE = 'loading';

	const aSelectors = {
		dataDependsOn: '[data-depends-on]',
		dataAttributeContainer: '[data-block="attribute_container"]',
		dataFieldsetContainer: '[data-block="fieldset_container"]',
		dataAttCodeSpecific: '[data-att-code="{0}"]'
	};

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
	 * updateField.
	 *
	 * @param oEvent
	 * @param oObjectContainer
	 * @param oElement
	 * @param aDependentAttCodes
	 * @returns {Promise<void>}
	 */
	async function updateField(oEvent, oObjectContainer, oElement, aDependentAttCodes){

		const aDependenciesAttCodes = [];

		/////////////////////////////////
		// I - CONSTRUCT DEPENDENCIES (The original event target but also other required dependencies)

		// the value of the field change, sAttCodes needs to be updated
		aDependentAttCodes.forEach((sAttCode) => {

			// field to update
			const oDependsOnElement = oElement.querySelector(String.format(aSelectors.dataAttCodeSpecific, sAttCode));

			// retrieve field container
			const oContainer = oDependsOnElement.closest(aSelectors.dataAttributeContainer);

			// set field container loading state
			oContainer.classList.add(LOADING_MESSAGE);

			// retrieve dependency data
			const sDependsOn = oDependsOnElement.dataset.dependsOn;

			// may have multiple dependencies
			let aDependsEls = sDependsOn.split(DEPENDS_ON_SEPARATOR);

			aDependsEls.forEach((sAtt) => {
				if(!aDependenciesAttCodes.includes(sAtt)){
					aDependenciesAttCodes.push(sAtt);
				}
			});
		});

		const aAllAttCodes = aDependentAttCodes.concat(aDependenciesAttCodes);

		/////////////////////////////////
		// II - PREPARE RELOAD REQUEST

		// prepare quest data
		let sRequestBody = '';
		let $bFirst = true;

		// iterate throw dependencies...
		aAllAttCodes.forEach(function(sAtt) {

			const oDependsOnElement = oElement.querySelector(String.format(aSelectors.dataAttCodeSpecific, sAtt));

			if(!$bFirst){
				sRequestBody += '&';
			}
			sRequestBody += 'partial_object['+oDependsOnElement.dataset.attCode + ']=' + oDependsOnElement.value;
			$bFirst = false;
		});

		sRequestBody += '&att_codes=' + aDependentAttCodes.join(',');
		sRequestBody += '&dependency_att_codes=' + aAllAttCodes.join(',');

		/////////////////////////////////
		// III - UPDATE THE FORM

		// update fom

		const sReloadResponse = await updateForm(sRequestBody, oObjectContainer.dataset.reloadUrl, 'POST');

		const oReloadedElement = oToolkit.parseTextToHtml(sReloadResponse);

		let oPartial = oReloadedElement.getElementById('partial_object');

		aDependentAttCodes.forEach((sAtt) => {

			// dependent element
			const oDependentElement = oElement.querySelector(String.format(aSelectors.dataAttCodeSpecific, sAtt));
			const oContainer = oDependentElement.closest(aSelectors.dataAttributeContainer);
			const sId = oDependentElement.getAttribute('id');
			const sName = oDependentElement.getAttribute('name');

			// new element
			const oNewElement = oPartial.querySelector(String.format(aSelectors.dataAttCodeSpecific, sAtt));
			const oNewContainer = oNewElement.closest(aSelectors.dataAttributeContainer);
			oNewElement.setAttribute('id', sId);
			oNewElement.setAttribute('name', sName);

			// replace element
			oContainer.replaceWith(oNewContainer);
		});

	}

	/**
	 * initDependencies.
	 *
	 *  @param oElement
	 */
	function initDependencies(oElement){

		// compute dependencies map
		let aMapDependencies = {};

		// get all field with dependencies
		const aDependentsFields = oElement.querySelectorAll(aSelectors.dataDependsOn);

		// iterate throw dependent fields...
		aDependentsFields.forEach(function (oDependentField) {

			// retrieve object container
			const oObjectContainer = oDependentField.closest('[data-block="object_container"]');

			// retrieve dependency data
			const sDependsOn = oDependentField.dataset.dependsOn;

			// may have multiple dependencies
			let aDependsEls = sDependsOn.split(DEPENDS_ON_SEPARATOR);

			// iterate throw the dependencies...
			aDependsEls.forEach(function(sEl){

				const sId = oObjectContainer.dataset.containerId;

				if(!(sId in aMapDependencies)) {
					aMapDependencies[sId] = [];
				}

				// add dependency att code to map
				if(!(sEl in aMapDependencies[sId])){
					aMapDependencies[sId][sEl] = [];
				}

				if(!aMapDependencies[sId][sEl].includes(oDependentField.dataset.attCode)){
					aMapDependencies[sId][sEl].push(oDependentField.dataset.attCode);
				}


			});

		});

		// iterate throw dependencies map...
		for(let sContainerId in aMapDependencies) {

			// retrieve object container
			let oObjectContainer = null;
			if(oElement.dataset !== undefined && oElement.dataset.containerId === sContainerId){
				oObjectContainer = oElement;
			}
			else{
				oObjectContainer = oElement.querySelector(`[data-container-id="${sContainerId}"]`);
			}

			const aMapContainer = aMapDependencies[sContainerId];

			// iterate throw dependencies map...
			for (let sAttCode in aMapContainer) {

				// retrieve corresponding field
				const oDependsOnElement = oObjectContainer.querySelector(String.format(aSelectors.dataAttCodeSpecific, sAttCode));

				// listen changes
				if (oDependsOnElement !== null) {
					oDependsOnElement.addEventListener('change', (event) => updateField(event, oObjectContainer, oElement, aMapContainer[sAttCode]));
				}
			}

		}

	}

	/**
	 * handleElement.
	 *
	 * @param element
	 */
	function handleElement(element){
		initDependencies(element);
		oWidget.handleElement(element);
		oDynamic.handleElement(element);
	}

	return {
		handleElement,
	}
};













