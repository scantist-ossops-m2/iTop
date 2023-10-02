
const FileTest = function(sUrl){


	function init(){
		$('#create_task').on('click', function(e){
			createTask(e);
		});
	}

	function createTask(e){

		// form attributes
		const sModalId = `file_modal`;
		const sFormName = `file_form`;

		// crate a new modal
		const oModal = oToolkit.createFullScreenModal(sModalId, 'Create task');
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

		// fetch url
		fetch(sUrl, {
			method: 'POST',
		})
			.then((response) => response.json())
			.then((data) => {

				// load modal content
				oModalBody.innerHTML = data.template;

				listenSaveModalObject(oModal, oBootstrapModal);

			})
			.catch(function (error) {
				console.error(error);
			});
	}

	/**
	 *
	 */
	function listenSaveModalObject(oModal, oBootstrapModal)
	{
		const oForm = oModal.querySelector('form');

		oForm.addEventListener('submit', function(e){

			e.preventDefault();
			e.stopPropagation();

			console.log('save');

			// // prepare form data
			// const data = new URLSearchParams();
			// for (const pair of new FormData(oForm)) {
			// 	data.append(pair[0], pair[1]);
			// }

			// fetch url
			fetch(sUrl, {
				method: 'POST',
				body: new FormData(oForm)
			})
				.then((response) => response.json())
				.then((data) => {

					// on success
					if(data.succeeded){

						// hide modal
						oBootstrapModal.hide();

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
		init
	}
};



