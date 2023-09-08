/**
 * Collections handling.
 *
 * @param oForm
 * @param objectFormUrl
 * @param objectSaveUrl
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Collection = function(oForm, objectFormUrl, objectSaveUrl){

	const MODAL_LOADING_HTML = 'loading...';

	// dom selectors
	const aSelectors = {
		addItem: '.add_item_link',
		createItem: '.create_item_link',
		removeItem: '.btn-remove-link',
		dataAttributeContainer: '[data-block="attribute_container"]',
		dataObjectContainer: '[data-block="object_container"]',
		dataAttCode: '[data-att-code]',
		dataAttCodeSpecific: '[data-att-code="{0}"]',
	};

	/**
	 * Listen for add item buttons.
	 *
	 * @param oContainer
	 */
	function listenAddItem (oContainer) {
		oContainer.querySelectorAll(aSelectors.addItem).forEach(btn => {
				btn.addEventListener('click', addFormToCollection)
			});
	}

	/**
	 * Listen for create item buttons.
	 *
	 * @param oContainer
	 */
	function listenCreateItem (oContainer) {
		oContainer.querySelectorAll(aSelectors.createItem).forEach(btn => {
				btn.addEventListener('click', createObject)
			});
	}

	/**
	 * Listen for remove item buttons.
	 *
	 * @param oContainer
	 */
	function listenRemoveItem(oContainer){
		oContainer.querySelectorAll(aSelectors.removeItem).forEach(btn => {
			btn.addEventListener('click', removeItem);
		});
	}

	/**
	 * Add form to the collection.
	 *
	 * @param e
	 */
	function addFormToCollection(e){

		// retrieve attribute container
		const oAttributeContainer = e.currentTarget.closest(aSelectors.dataAttributeContainer);

		// retrieve collection holder (replace ':' character otherwise the selector is invalid)
		const exp = e.currentTarget.dataset.collectionHolderClass.replaceAll(/:/g, '\\:');
		const collectionHolder = oAttributeContainer.querySelector('.' + exp);

		// compute template
		const text = collectionHolder
			.dataset
			.prototype
			.replace(
				/__name__/g,
				collectionHolder.dataset.index
			);

		// create item element
		const fragment = oToolkit.createElementFromHtml(text);
		collectionHolder.appendChild(fragment);

		// form handling
		const item = collectionHolder.querySelector('tr:last-child');
		listenRemoveItem(item);
		oForm.handleElement(item);

		// store new index
		collectionHolder.dataset.index++;

		// hide no data row
		oAttributeContainer.querySelector('.no_data').style.display = 'none';
	}

	/**
	 * Create an iTop object.
	 *
	 * @param e
	 */
	function createObject(e){

		// retrieve attribute container
		const oAttributeContainer = e.currentTarget.closest(aSelectors.dataAttributeContainer);

		// retrieve attribute field
		const oAttributeField = oAttributeContainer.querySelector(aSelectors.dataAttCode);

		// retrieve attribute object container
		const oObjectContainer = e.currentTarget.closest(aSelectors.dataObjectContainer);

		// open modal
		const oModalBody= document.querySelector('#object_modal .modal-body');
		oModalBody.innerHTML = MODAL_LOADING_HTML;
		const oModal = new bootstrap.Modal('#object_modal', {});
		oModal.show();

		// compute object form url
		const sUrl = objectFormUrl
			.replaceAll('object_class', e.currentTarget.dataset.objectClass)
			.replaceAll('form_name', 'new');

		// prepare request data
		const aLockedAttributes = {};
		if(!e.currentTarget.dataset.isIndirect) {
			aLockedAttributes[e.currentTarget.dataset.extKeyToMe] = oObjectContainer.dataset.objectId;
		}
		const aData = {
			locked_attributes: aLockedAttributes,
			att_code: oAttributeField.dataset.attCode
		}

		const sExtKeyToMe = e.currentTarget.dataset.extKeyToMe;

		// fetch url
		fetch(sUrl, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(aData)
		})
		.then((response) => response.json())
		.then((data) => {
			oModalBody.innerHTML = data.template;
			oForm.handleElement(oModalBody);
			handleElement(oModalBody);
			oApp.handleTooltips(oModalBody);
			listenSaveModalObject(oModal, oModalBody, oObjectContainer, oAttributeField.dataset.attCode, sExtKeyToMe, oAttributeContainer.dataset.objectClass);
		})
		.catch(function (error) {
			console.error(error);
		});
	}

	/**
	 *
	 */
	function listenSaveModalObject(oModal, oModalBody, oObjectContainer, sAttCode, sExtKeyToMe, sObjectClass)
	{
		const oSave = document.querySelector('[data-action="save_modal_object"]');

		oSave.addEventListener('click', function(e){

			const oForm = document.querySelector('form[name="new"]');

			// set loading state
			oModalBody.innerHTML = MODAL_LOADING_HTML;

			// save CK editors
			oApp.saveCkEditors();

			// prepare data
			const data = new URLSearchParams();
			for (const pair of new FormData(oForm)) {
				data.append(pair[0], pair[1]);
			}
			data.append('ext_key_to_me', sExtKeyToMe);
			data.append('object_class', sObjectClass);

			// compute object form url
			const url = objectSaveUrl
				.replaceAll('object_class', oForm.dataset.objectClass)
				.replaceAll('form_name', 'new');

			// fetch url
			fetch(url, {
				method: 'POST',
				body: data,
			})
				.then((response) => response.json())
				.then((data) => {

					// on success
					if(data.succeeded){

						// extract form content
						const reg = new RegExp(/<form .*?>(.*)<\/form>/gs);
						const res = reg.exec(data.template);

						// append new row
						const row = oToolkit.createElementFromHtml(res[1]);
						oObjectContainer.querySelector(`[data-att-code="${sAttCode}"] tbody`).appendChild(row);

						// hide modal
						oModal.hide();
					}
					else{
						console.error('Error while saving object');
					}

				})
				.catch(function (error) {

					console.error(error);
				});
		});
	}

	/**
	 * Remove an item.
	 *
	 * @param e
	 */
	function removeItem(e)
	{
		// retrieve attribute container
		const oAttributeContainer = e.currentTarget.closest(aSelectors.dataAttributeContainer);

		// remove row
		e.currentTarget.closest('tr').remove();

		// handle no data row visibility
		if(oAttributeContainer.querySelectorAll('tbody tr').length === 1) {
			oAttributeContainer.querySelector('.no_data').style.display = 'table-row';
		}
	}

	/**
	 * Handle collection on the provided container element.
	 *
	 * @param oContainer
	 */
	function handleElement(oContainer)
	{
		listenAddItem(oContainer);
		listenCreateItem(oContainer);
		listenRemoveItem(oContainer);
	}

	return {
		handleElement
	}
};



