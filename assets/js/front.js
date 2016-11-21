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

});