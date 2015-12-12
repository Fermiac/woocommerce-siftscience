/*
 * Author: Nabeel Sulieman
 * Description: This class handles initializing the SiftSience javascript.
 * License: GPL2
 */

var _sift = _sift || [];
_sift.push(['_setAccount', _wc_siftsci_js_input_data.js_key]);
_sift.push(['_setUserId', _wc_siftsci_js_input_data.user_id]);
_sift.push(['_setSessionId', _wc_siftsci_js_input_data.session_id]);
_sift.push(['_trackPageview']);

(
    function() 
    {
        function ls() 
        {
            var e = document.createElement('script');
            e.type = 'text/javascript';
            e.async = true;
            e.src = ('https:' == document.location.protocol ? 'https://' : 'http://') 
                    + 'cdn.siftscience.com/s.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(e, s);
        }
        if (window.attachEvent) 
        {
            window.attachEvent('onload', ls);
        } 
        else 
        {
            window.addEventListener('load', ls, false);
        }
    }
)();
