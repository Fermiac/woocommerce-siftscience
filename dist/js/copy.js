function copyInfo( elementId ) {
  var element = document.getElementById( elementId );

  element.select();
  document.execCommand( 'copy' );
  
  element.setSelectionRange( 0, 0 ); // Clear selection.
}