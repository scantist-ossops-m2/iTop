/**
 * Application handling.
 *
 * @returns {{init: init, handleTooltips: handleTooltips}}
 * @constructor
 */
const App = function(){

	// dom selectors
	const aSelectors = {
		darkModeButton: '#dark_mode'
	};

	/**
	 * initialization.
	 *
	 */
	function init(){

		// dark theme button
		$(aSelectors.darkModeButton).on('click', function(){
			$('body').attr('data-bs-theme', this.ariaPressed === 'true' ? 'dark' : 'light');
		});

		// dark theme button state
		if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
			$('body').attr('data-bs-theme', 'dark');
			$(aSelectors.darkModeButton).attr('aria-pressed', 'true');
			$(aSelectors.darkModeButton).toggleClass('active', true);
		}

		// handle tooltips
		handleTooltips(document);
	}

	/**
	 * Bootstrap tooltip initialization.
	 *
	 * @param oElement
	 */
	function handleTooltips(oElement){
		const tooltips = oElement.querySelectorAll("[data-bs-toggle='tooltip']");
		tooltips.forEach((el) => {
			new bootstrap.Tooltip(el);
		});
	}

	/**
	 * ckeditor save editors.
	 *
	 */
	function saveCkEditors(){
		for(let instanceName in CKEDITOR.instances) {
			CKEDITOR.instances[instanceName].updateElement();
		}
	}

	return {
		init,
		handleTooltips,
		saveCkEditors
	}
};













