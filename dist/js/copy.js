sender = null;
function copyInfo( source, elementId ) {
	if( null === sender || void 0 === sender ) {
		source.title = 'Copied';
		sender = source;
	} else {
		sender.title = 'Copy to clipboard';
	}

	var element = document.getElementById( elementId );

	element.select();
	document.execCommand( 'copy' );

	element.setSelectionRange( 0, 0 ); // Clear selection.
	source.title = 'Copied';
}