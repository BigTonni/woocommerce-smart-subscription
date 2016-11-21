<?php
/* 
 * Desc: File for AJAX.
 * Author: Anton Shulga
 */

add_action( 'wp_ajax_wss_create_subscription', 'wss_create_subscription');
add_action( 'wp_ajax_nopriv_wss_create_subscription', 'wss_create_subscription');
function wss_create_subscription(){
    $data = array('result' => false);
    
    //Test for creating subscription
    // Vers #1
//    $args['order_id']         = 0; // new WC_Order()
//    $args['billing_interval'] = ( ! empty( $_POST['billing_interval'] ) ) ? $_POST['billing_interval'] : '';
    $args['start_date'] = ( ! empty( $_POST['start_date'] ) ) ? $_POST['start_date'] : '';
//    $args['billing_period']   = ( ! empty( $_POST['period'] ) ) ? $_POST['period'] : '';

    $res = wcs_create_subscription( $args );
    
    // Vers #2
//    WC_Subscriptions_Checkout::create_subscription( $order, $recurring_cart );
    
    if ( is_wp_error($res) ){
        $data['message']         = __( 'Error.', WSS_TEXT_DOMAIN );
    }else{
        $data['result'] = true;
        $data['message']         = __( 'Subscription generated successfully.', WSS_TEXT_DOMAIN );
    }
    
    exit(json_encode($data));
}