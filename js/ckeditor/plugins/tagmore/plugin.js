/**
 * @file Horizontal Tag More
 */

// Register a plugin named "tagmore".
CKEDITOR.plugins.add( 'tagmore',
{
	init : function( editor )
	{
		// Register the command.
		editor.addCommand( 'tagmore', CKEDITOR.plugins.tagmoreCmd );

		// Register the toolbar button.
		editor.ui.addButton( 'TagMore',
			{
				label : editor.lang.tagmore,
				command : 'tagmore',
				icon: CKEDITOR.getUrl( this.path + 'tagmore.gif' )
			});

		// Add the style that renders our placeholder.
		editor.addCss(
			'img.tagmore' +
			'{' +
				'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/tagmore.gif' ) + ');' +
				'background-position: center center;' +
				'background-repeat: no-repeat;' +
				'clear: both;' +
				'display: block;' +
				'float: none;' +
				'width: 100%;' +
				'border-top: #999999 1px dotted;' +
				'border-bottom: #999999 1px dotted;' +
				'height: 5px;' +

			'}' );
	},

	afterInit : function( editor )
	{
		// Register a filter to displaying placeholders after mode change.

		var dataProcessor = editor.dataProcessor,
			dataFilter = dataProcessor && dataProcessor.dataFilter;

		if ( dataFilter )
		{
			dataFilter.addRules(
				{
					elements :
					{
						div : function( element )
						{
			
							var id = element.attributes.id,
								child = element.children.length == 1 && element.children[ 0 ],
								childStyle = child && ( child.name == 'span' ) && child.attributes.style;

							if ( childStyle && ( /display\s*:\s*none/i ).test( childStyle ) && ( /post-more/i ).test( id ) )
								return editor.createFakeParserElement( element, 'tagmore', 'div' );
						}
					}
				});
		}
	},

	requires : [ 'fakeobjects' ]
});

CKEDITOR.plugins.tagmoreCmd =
{
	exec : function( editor )
	{
        
        if((/<div(.*) id="post-more"(.*)>\n\t<span style="display: none;">(.*)<\/span><\/div>/).test(editor.getData()) == true)
            return;
		
		// Create the element that represents a print break.
		var breakObject = CKEDITOR.dom.element.createFromHtml( '<div id="post-more"><span style="display: none;">&nbsp;</span></div>' );

		// Creates the fake image used for this element.
		breakObject = editor.createFakeElement( breakObject, 'tagmore', 'div' );

		var ranges = editor.getSelection().getRanges();

		for ( var range, i = 0 ; i < ranges.length ; i++ )
		{
			range = ranges[ i ];

			if ( i > 0 )
				breakObject = breakObject.clone( true );

			range.splitBlock( 'p' );
			range.insertNode( breakObject );
		}
	}
};
