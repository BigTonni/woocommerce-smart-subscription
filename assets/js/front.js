/**
 * @author Anton Shulga
 * Description: For frontend
 */
jQuery.noConflict();

jQuery(document).ready(function ($) {

    jQuery('#wss_start_date').datepicker({
        numberOfMonths: 1,
        minDate: '0',
        dateFormat: 'yy-m-d'
    });

    $('#wss_show_subscription_fields').on('change', function(){
        if( $(this).is(':checked') ) {
            $(this).val('1');
            $('#wss_subscription_field').show();
        } else {
            $(this).val('0');
            $('#wss_subscription_field').hide();
        }
    });

    $('#wss_create_subscription').on('click', function (e) {
        e.preventDefault();
        var self = this;
        var data = {action: 'wss_create_subscription'};
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: WSSFrontParams.ajax_url,
            data: data,
            success: function (response) {

                if (response.result) {
                    //success
                } else {
                    //error
                }
            }
        });
    });

});