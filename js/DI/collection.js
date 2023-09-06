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
		linkSetContainer: '.link_set_widget_container',
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

		// retrieve link set container
		const oContainer = e.currentTarget.closest('.link_set_widget_container');

		// retrieve collection holder (replace ':' character otherwise the selector is invalid)
		const exp = e.currentTarget.dataset.collectionHolderClass.replaceAll(/:/g, '\\:');
		const collectionHolder = oContainer.querySelector('.' + exp);

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

		// remove no data row
		oContainer.querySelector('.no_data').style.display = 'none';
	}

	/**
	 * Create an iTop object.
	 *
	 * @param e
	 */
	function createObject(e){

		let objectId = e.currentTarget.closest('form').dataset.objectId;

		// set modal loading state
		$('#object_modal .modal-body').html('loading...');

		const cont = e.currentTarget.closest('.link_set_widget_container');

		// open modal
		const myModalAlternative = new bootstrap.Modal('#object_modal', {});
		myModalAlternative.show();

		// compute object form url
		const url = objectFormUrl
			.replaceAll('object_class', e.currentTarget.dataset.objectClass)
			.replaceAll('form_name', 'new');

		// prepare request data
		const aLockedAttributes = {};
		aLockedAttributes[e.currentTarget.dataset.extKeyToMe] = objectId;
		const aData = {
			locked_attributes: aLockedAttributes,
			att_code: cont.dataset.attCode
		}

		// fetch url
		fetch(url, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(aData)
		})
		.then((response) => response.json())
		.then((data) => {
			const oModalBody = $('#object_modal .modal-body');
			oModalBody.html(data.template);
			oModalBody[0].querySelectorAll('form').forEach((formEl) => {
				oForm.handleElement(formEl);
				handleElement(formEl);
				oApp.handleTooltips(formEl);
			});

			listenSaveModalObject(myModalAlternative);
		})
		.catch(function (error) {
			console.error(error);
		});
	}

	/**
	 * Remove an item.
	 *
	 * @param e
	 */
	function removeItem(e)
	{
		// retrieve link set container
		const oContainer = e.currentTarget.closest(aSelectors.linkSetContainer);

		// remove row
		e.currentTarget.closest('tr').remove();

		// handle no data row visibility
		if(oContainer.querySelectorAll('tbody tr').length === 1) {
			oContainer.querySelector('.no_data').style.display = 'table-row';
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

	/**
	 *
	 */
	function listenSaveModalObject(myModalAlternative)
	{
		const oSave = document.querySelector('[data-action="save_modal_object"]');

		oSave.addEventListener('click', function(e){

			const oForm = document.querySelector('form[name="new"]');

			for(let instanceName in CKEDITOR.instances) {
				CKEDITOR.instances[instanceName].updateElement();
			}

			const data = new URLSearchParams();
			for (const pair of new FormData(oForm)) {
				data.append(pair[0], pair[1]);
			}
			data.append('locked_attributes', '');

			// compute object form url
			const url = objectSaveUrl
				.replaceAll('object_class', oForm.dataset.objectClass)
				.replaceAll('form_name', 'new');

			// fetch url
			fetch(url, {
				method: 'POST',
				body: new URLSearchParams(new FormData(oForm))
			})
			.then((response) => response.json())
			.then((data) => {

				if(data.succeeded){

					let form = $(data.template);

					console.log(form);
					//
					// console.log(oForm.dataset.attCode);
					//
					// const fragment = oToolkit.createElementFromHtml(data.template);
					// const inner = fragment.querySelector('form').innerHTML;
					// const el = oToolkit.createElementFromHtml(inner);
					// console.log(el);
					//
					myModalAlternative.hide();

					console.log($(`[data-att-code="${oForm.dataset.attCode}"] tbody`));

					$(`[data-att-code="${oForm.dataset.attCode}"] tbody`).append($(form.innerHTML));

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


	return {
		handleElement
	}
};



