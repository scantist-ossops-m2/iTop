/**
 * Example of jquery widget.
 *
 */
$.widget( "itop.text_widget",
{
	// default options
	options:
		{
		},

	// the constructor
	_create: function()
	{
		editor = CKEDITOR.replace(this.element.attr('id'), {
			language: 'fr',
			uiColor: '#9ec5fe88',
			toolbarStartupExpanded: true
		});


	}

});

