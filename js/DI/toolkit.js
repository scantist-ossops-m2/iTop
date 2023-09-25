/**
 * Toolkit.
 *
 * @returns {{init: init, parseTextToHtml: (function(*): Document), createElementFromHtml: (function(*): DocumentFragment)}}
 * @constructor
 */
const Toolkit = function(){

	function init(){
		installStringFormatFunction();
	}

	/**
	 * installStringFormatFunction.
	 *
	 * String formatter utility.
	 */
	function installStringFormatFunction(){
		if (!String.format) {
			String.format = function(format) {
				const args = Array.prototype.slice.call(arguments, 1);
				return format.replace(/{(\d+)}/g, function(match, number) {
					return typeof args[number] != 'undefined' ? args[number] : match;
				});
			};
		}
	}

	/**
	 * parseTextToHtml.
	 *
	 * @param sText
	 * @returns {Document}
	 */
	function parseTextToHtml(sText){
		const oParser = new DOMParser();
		return oParser.parseFromString(sText, 'text/html');
	}

	/**
	 *
	 * @param html
	 * @returns {DocumentFragment}
	 */
	function createElementFromHtml(html) {
		const t = document.createElement('template');
		t.innerHTML = html;
		return t.content;
	}

	function createFullScreenModal(sId, sTitle){

		// retrieve modal template
		const oModalTemplate = document.getElementById('full_screen_modal_template');

		// clone template content
		const oModalFragment = oModalTemplate.content.cloneNode(true);

		// append modal to container
		const oModalContainer = document.getElementById('modal_container');
		const oModal = oModalContainer.appendChild(oModalFragment.lastElementChild);
		oModal.setAttribute('id', sId);
		oModal.querySelector('.modal-title').textContent = sTitle;

		return oModal;
	}

	return {
		init,
		parseTextToHtml,
		createElementFromHtml,
		createFullScreenModal
	}
};













