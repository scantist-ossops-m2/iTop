/**
 * 
 * @api
 * @since 3.2.0
 */
const CombodoCKEditorHandler = {
	instances: {},
	instances_promise: {},
	/**
	 * Make the oElem enter the fullscreen mode, meaning that it will take all the screen and be above everything else.
	 *
	 * @param {string} sElem The id object of the element
	 * @constructor
	 */
	CreateInstance: async function (sElem) {
		return this.instances_promise[sElem] = new Promise((resolve, reject) => {
			ClassicEditor.create($(sElem)[0])
			.then(editor => {
				this.instances[sElem] = editor;
				resolve(editor);
			})
			.catch( error => {
				console.error( error );
			} );
		});
	},
	DeleteInstance: async function(sElem){
		let oInstance = await this.GetInstance(sElem);
		if (oInstance) {
			oInstance.destroy().then(() => {
				CombodoJSConsole.Debug('CKEditor for #'+sId+' destroyed successfully');
			}).catch(error => {
				CombodoJSConsole.Error('Error during #'+sId+' editor destruction:' + error);
			});
		}
	},
	GetInstance: async function(sElem){
		if (this.instances[sElem]) {
			return this.instances[sElem];
		}
		else{
			let oEditor = null
			if(!this.instances_promise[sElem]){
				this.instances_promise[sElem] = new Promise((resolve, reject) => {
				});
			}
			await this.instances_promise[sElem].then((editor) => {
				oEditor = editor;
			});
			return oEditor;
		}
	},
	EnableImageUpload: async function(sElem, sUrl){
		const editor = await this.GetInstance(sElem);
				class SimpleUploadAdapter {
					constructor(loader) {
						this.loader = loader;
					}

					upload() {
						return this.loader.file
							.then(file => new Promise((resolve, reject) => {
								// Replace 'your-upload-url' with your server-side upload endpoint
								const uploadUrl = sUrl;

								const formData = new FormData();
								formData.append('upload', file);

								fetch(uploadUrl, {
									method: 'POST',
									body: formData,
								})
									.then(response => response.json())
									.then(responseData => {
										if (responseData.uploaded) {    
											resolve({ default: responseData.url });
										} else {
											reject(responseData.error.message || 'Upload failed');
										}
									})
									.catch(error => {
										reject('Upload failed due to a network error.');
									});
							}));
					}
				}

				// Enable the custom upload adapter
				editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
					return new SimpleUploadAdapter(loader);
				};
	}
}