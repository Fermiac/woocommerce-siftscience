/*
 * Author: Nabeel Sulieman
 * Description: Sift client script based on
 * https://sift.com/developers/docs/curl/javascript-api/overview
 */

var _wc_siftsci_js_input_data = window._wc_siftsci_js_input_data || [];
var _sift = window._sift = window._sift || [];
_sift.push( [ '_setAccount', _wc_siftsci_js_input_data.js_key  ]);
_sift.push( [ '_setUserId', _wc_siftsci_js_input_data.user_id ] );
_sift.push( [ '_setSessionId', _wc_siftsci_js_input_data.session_id ] );
_sift.push( [ '_trackPageview' ] );

(function() {
	function ls() {
		var e = document.createElement('script');
		e.src = 'https://cdn.sift.com/s.js';
		document.body.appendChild(e);
	}
	if (window.attachEvent) {
		window.attachEvent('onload', ls);
	} else {
		window.addEventListener('load', ls, false);
	}
})();
