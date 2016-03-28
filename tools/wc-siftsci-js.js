/*
 * Author: Nabeel Sulieman
 * Description: SiftScience client script based on
 * https://siftscience.com/developers/docs/javascript/javascript-api
 */

var _sift = window._sift = window._sift || [];
_sift.push( [ '_setAccount', _wc_siftsci_js_input_data.js_key  ]);
_sift.push( [ '_setUserId', _wc_siftsci_js_input_data.user_id ] );
_sift.push( [ '_setSessionId', _wc_siftsci_js_input_data.session_id ] );
_sift.push( [ '_trackPageview' ] );

(function( d, s, id ) {
	var e, f = d.getElementsByTagName( s )[0];
	if ( d.getElementById( id ) ) {return;}
	e = d.createElement( s ); e.id = id;
	e.src = 'https://cdn.siftscience.com/s.js';
	f.parentNode.insertBefore( e, f );
})( document, 'script', 'sift-beacon' );
