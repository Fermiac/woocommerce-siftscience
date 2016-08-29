/*
 * Author: Nabeel Sulieman
 * Description: Asynchronously send events data back to SiftScience
 * License: GPL2
 */

function sendEventData( i, post ){
	jQuery.ajax( {
		url: post.url,
		method: "POST",
		data: post,
	} );
}

jQuery.each( _wc_siftsci_events_input_data.posts, sendEventData );
