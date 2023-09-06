/**
 * Application handling.
 *
 * @returns {{init: init}}
 * @constructor
 */
const App = function(){

	// dom selectors
	const aSelectors = {
		darkModeButton: '#dark_mode'
	};

	/**
	 * init.
	 *
	 */
	function init(){

		$(aSelectors.darkModeButton).on('click', function(){
			$('body').attr('data-bs-theme', this.ariaPressed === 'true' ? 'dark' : 'light');
		});

		if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
			$('body').attr('data-bs-theme', 'dark');
			$(aSelectors.darkModeButton).attr('aria-pressed', 'true');
			$(aSelectors.darkModeButton).toggleClass('active', true);
		}

		const tooltips = document.querySelectorAll("[data-bs-toggle='tooltip']");
		tooltips.forEach((el) => {
			new bootstrap.Tooltip(el);
		});

	}

	return {
		init
	}
};













