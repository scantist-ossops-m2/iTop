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
		CKEDITOR.replace( this.element.attr('id'));
	}

});

