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
		const sAttributeId = oAttributeField.getAttribute('id');

		// retrieve attribute object container
		const oObjectContainer = e.currentTarget.closest(aSelectors.dataObjectContainer);

		// form attributes
		const sModalId = `${sAttributeId}_modal`;
		const sFormName = `${sAttributeId}_form`;

		// crate a new modal
		const oModal = oToolkit.createFullScreenModal(sModalId, e.currentTarget.dataset.modalTitle);
		const oModalBody= oModal.querySelector('.modal-body');
		oModalBody.innerHTML = 'loading...';

		// bootstrap modal
		const oBootstrapModal = new bootstrap.Modal(`#${sModalId}`);
		oModal.addEventListener('hidden.bs.modal', event => {
			// cleanup
			oBootstrapModal.dispose();
			event.currentTarget.remove();
		});
		oBootstrapModal.show();

		// compute object form url
		const sUrl = objectFormUrl.replaceAll('object_class', e.currentTarget.dataset.objectClass);

		// prepare request data
		const aLockedAttributes = {};
		if(!e.currentTarget.dataset.isIndirect) {
			aLockedAttributes[e.currentTarget.dataset.extKeyToMe] = oObjectContainer.dataset.objectId;
		}
		const aData = {
			locked_attributes: aLockedAttributes,
			att_code: oAttributeField.dataset.attCode,
			form_name: sFormName
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

			// load modal content
			oModalBody.innerHTML = data.template;

			// listen
			oForm.handleElement(oModalBody);
			handleElement(oModalBody);
			oApp.handleTooltips(oModalBody);

			listenSaveModalObject(oModal, oBootstrapModal, oModalBody, oObjectContainer, oAttributeContainer, oAttributeField.dataset.attCode, sExtKeyToMe, oAttributeContainer.dataset.objectClass, sModalId, sFormName, oAttributeField.getAttribute('name'));
		})
		.catch(function (error) {
			console.error(error);
		});
	}

	/**
	 *
	 */
	function listenSaveModalObject(oModal, oBootstrapModal, oModalBody, oObjectContainer, oAttributeContainer, sAttCode, sExtKeyToMe, sObjectClass, sModalId, sFormName, sVarName)
	{
		const oSave = oModal.querySelector('[data-action="save_modal_object"]');

		oSave.addEventListener('click', function(e){

			const oForm = document.querySelector('form[name="' + sFormName + '"]')

			// set loading state
			oModalBody.innerHTML = 'Saving object...';

			// save CK editors
			oApp.saveCkEditors();

			// prepare form data
			const data = new URLSearchParams();
			for (const pair of new FormData(oForm)) {
				data.append(pair[0], pair[1]);
			}

			// append useful extra data
			data.append('ext_key_to_me', sExtKeyToMe);
			data.append('object_class', sObjectClass);
			data.append('form_name', sFormName);

			// compute object form url
			const url = objectSaveUrl.replaceAll('object_class', oForm.dataset.objectClass);

			// fetch url
			fetch(url, {
				method: 'POST',
				body: data,
			})
				.then((response) => response.json())
				.then((data) => {

					// on success
					if(data.succeeded){

						// retrieve collection holder (replace ':' character otherwise the selector is invalid)
						const collectionHolder = oAttributeContainer.querySelector('tbody');

						// extract form content
						const reg = new RegExp(/<form .*?>(.*)<\/form>/gs);
						const res = reg.exec(data.template);

						// append new row
						const row = oToolkit.createElementFromHtml(res[1]);
						row.querySelectorAll(aSelectors.dataAttCode).forEach(function(e){
							e.setAttribute('id', `${oAttributeContainer.querySelector(aSelectors.dataAttCode).getAttribute('id')}_${collectionHolder.dataset.index}_${e.dataset.attCode}`);
							e.setAttribute('name', `${oAttributeContainer.querySelector(aSelectors.dataAttCode).getAttribute('name')}[${collectionHolder.dataset.index}][${e.dataset.attCode}]`);
						});

						oObjectContainer.querySelector(`[data-att-code="${sAttCode}"] tbody`).appendChild(row);

						// listen
						listenRemoveItem(row);

						// hide modal
						oBootstrapModal.hide();

						collectionHolder.dataset.index++;
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



