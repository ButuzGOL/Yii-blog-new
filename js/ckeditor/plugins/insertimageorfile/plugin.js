/**
 * @file Horizontal Tag More
 */

// Register a plugin named "tagmore".
CKEDITOR.plugins.add( 'insertimageorfile',
{
	init : function( editor )
	{
		// Register the command.
		editor.addCommand( 'insertimageorfile', CKEDITOR.plugins.insertimageorfileCmd );

		// Register the toolbar button.
		editor.ui.addButton( 'InsertImageOrFile',
			{
				label : editor.lang.insertimageorfile,
				command : 'insertimageorfile',
				icon: CKEDITOR.getUrl( this.path + 'insertimageorfile.gif' )
			});

	},
});

CKEDITOR.plugins.insertimageorfileCmd =
{
	exec : function( editor )
	{
        tb_show(editor.lang.insertimageorfile, insertimageorfile, null);
	}
};
