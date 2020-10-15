window._wc_siftsci_js_sender = null;
function copyInfo( source, elementId ) {
	
	var sender = window._wc_siftsci_js_sender;
	if( null === sender || void 0 === sender ) {
		sender       = source;
		source.title = 'Copied';
	} else {
		sender.title = 'Copy to clipboard';
	}

	var element = document.getElementById( elementId );

	element.select();
	document.execCommand( 'copy' );

	element.setSelectionRange( 0, 0 ); // Clear selection.
	source.title = 'Copied';
}