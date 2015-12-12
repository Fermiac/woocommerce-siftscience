/*
 * Author: Nabeel Sulieman
 * Description: This class handles API calls and managing the icons in the list of orders.
 * License: GPL2
 */

var SiftScienceOrder = 
{
    callApi: function (id, type)
    {
        jQuery('#siftsci_spinner_' + id)[0].style.display = 'block';
        jQuery.ajax({
            url: _wc_siftsci_order_input_data.url + '?id=' + id + '&action=' + type,
            dataType: 'json',
            success: function (data){
                SiftScienceOrder.handleResponse(id, data);
            },
            error: function (e,s,j){
                SiftScienceOrder.setError(id);
            }
        });
    },
    
    handleResponse: function(id, data){
        jQuery.each(data, function(k, v){
            var div = jQuery('#siftsci_' + k + '_' + id)[0];
            if (v.hasOwnProperty('display'))
                div.style.display = v.display;
            if (v.hasOwnProperty('value'))
                div.children[0].children[0].innerHTML = v.value;
            if (v.hasOwnProperty('color'))
                div.children[0].children[0].style.backgroundColor = v.color;
        });
    },
    
    setError: function (id){
        var icons = ['spinner', 'error', 'score', 'backfill', 'good', 'good_gray', 'bad', 'bad_gray'];
        jQuery.each(icons, function(k, v){SiftScienceOrder.hideDiv(v, id);});
        jQuery('#siftsci_error_' + id)[0].style.display = 'block';
    },
    
    hideDiv: function(v, id){
        jQuery('#siftsci_' + v + '_' + id)[0].style.display = 'none';
    }
};

jQuery('.siftsci_spinner').each(
    function(i){SiftScienceOrder.callApi(this.id.split('_')[2], 'get');}
);

