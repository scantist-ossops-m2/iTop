/**
 * Collections handling.
 *
 * @param oForm
 * @param objectFormUrl
 * @returns {{handleElement: handleElement}}
 * @constructor
 */
const Collection = function(oForm, objectFormUrl){

	/**
	 * Listen for add item buttons.
	 *
	 * @param oContainer
	 */
	function listenAddItem (oContainer) {

		oContainer.querySelectorAll('.add_item_link').forEach(btn => {
				btn.addEventListener("click", addFormToCollection)
			});

	}

	/**
	 * Listen for create item buttons.
	 *
	 * @param oContainer
	 */
	function listenCreateItem (oContainer) {

		oContainer.querySelectorAll('.create_item_link').forEach(btn => {
				btn.addEventListener("click", createObject)
			});

	}

	/**
	 * Listen for remove item buttons.
	 *
	 * @param oContainer
	 */
	function listenRemoveItem(oContainer){

		oContainer.querySelectorAll('.btn-remove-link').forEach(btn => {
			btn.addEventListener("click", (e) => {

				const oContainer = e.currentTarget.closest('.link_set_widget_container');

				btn.closest('tr').remove()

				if(oContainer.querySelectorAll('tbody tr').length === 1) {
					oContainer.querySelector('.no_data').style.display = 'table-row';
				}
			})
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

		// retrieve collection holder
		const collectionHolder = oContainer.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

		// compute template
		const text = collectionHolder
			.dataset
			.prototype
			.replace(
				/__name__/g,
				collectionHolder.dataset.index
			);

		// create item element
		const item = oToolkit.createElementFromHtml(text);
		listenRemoveItem(item);
		oForm.handleElement(item);
		collectionHolder.appendChild(item);

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

		// set modal loading state
		$('#object_modal .modal-body').html('loading...');

		// open modal
		const myModalAlternative = new bootstrap.Modal('#object_modal', []);
		myModalAlternative.show();

		// compute object form url
		const url = objectFormUrl
			.replaceAll('object_class', e.currentTarget.dataset.objectClass)
			.replaceAll('form_name', 'new');

		// prepare request data
		const aLockedAttributes = {};
		aLockedAttributes[e.currentTarget.dataset.extKeyToMe] = 0;
		const aData = {
			locked_attributes: aLockedAttributes
		}

		// fetch url
		fetch(url, {
			method: 'post',
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
			});
		})
		.catch(function (error) {
			console.error(error);
		});
	}

	/**
	 * Handle collection on the provided container element.
	 *
	 * @param oContainer
	 */
	const handleElement = function(oContainer)
	{
		listenAddItem(oContainer);
		listenCreateItem(oContainer);
		listenRemoveItem(oContainer);
	}

	return {
		handleElement
	}
};



