/*
 * Author: Nabeel Sulieman
 * Description: Asynchronously send events data back to SiftScience
 * License: GPL2
 */

jQuery.each(_wc_siftsci_events_input_data.posts,
    function (i, post){
        jQuery.ajax(
            {
                url: post.url,
                method: "POST",
                data: post
            }
        )
    }
);
