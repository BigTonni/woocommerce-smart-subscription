<?php

/**
 * WSS_Checkout
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WSS_Checkout.
 */
class WSS_Checkout {

    /**
     * Initialize the admin actions.
     */
    public function __construct() {
        add_action( 'woocommerce_after_order_notes', array( $this, 'wss_subscription_fields' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'wss_checkout_subscription_process' ) );
        add_action( 'woocommerce_payment_complete', array( $this, 'wss_create_subscription' ), 10 );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'wss_save_subscription_fields' ) );
    }

    /**
    * Process the checkout
    */
    public function wss_checkout_subscription_process() {
        // Check if our fields are set and if they are not add an error
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if( isset( $_POST['wss_show_subscription_fields'] ) && $_POST['wss_show_subscription_fields'] == 1 ) {
                if( !isset( $_POST['wss_billing_interval'] ) || empty( $_POST['wss_billing_interval'] ) ) {
                    wc_add_notice( __( 'Please select subscription recurring' ), 'error' );
                }
                if( !isset( $_POST['wss_billing_period'] ) || empty( $_POST['wss_billing_period'] ) ) {
                    wc_add_notice( __( 'Please select subscription period' ), 'error' );
                }
                if( !isset( $_POST['wss_start_date'] ) || empty( $_POST['wss_start_date'] ) ) {
                    wc_add_notice( __( 'Please select subscription start date' ), 'error' );
                }
                if( !isset( $_POST['wss_start_hours'] ) || empty( $_POST['wss_start_hours'] ) ) {
                    wc_add_notice( __( 'Please select subscription hours' ), 'error' );
                }
                if( !isset( $_POST['wss_start_minutes'] ) || empty( $_POST['wss_start_minutes'] ) ) {
                    wc_add_notice( __( 'Please select subscription minutes' ), 'error' );
                }
            }
        }
    }

    public function wss_subscription_fields( $checkout ) {
      
        woocommerce_form_field( 'wss_show_subscription_fields', array(
            'type'      => 'checkbox',
            'class'     => array( 'wss-form-field show-subscription-fields' ),
            'label'     => __( 'Do you want to subscribe?', WSS_TEXT_DOMAIN ),
            'required'  => false,
            'value'     => true,
            'default'   => false
        ), false );

        echo '<div id="wss_subscription_field"><h2>' . __( 'Subscription Details', WSS_TEXT_DOMAIN ) . '</h2>';
        // Recurring field
        woocommerce_form_field( 'wss_billing_interval', array(
            'type'      => 'select',
            'options'   => array(
                1 => 'every',
                2 => 'every 2nd',
                3 => 'every 3rd',
                4 => 'every 4th',
                5 => 'every 5th',
                6 => 'every 6th',
            ),
            'class'         => array( 'billing-interval wss-form-fields' ),
            'label'         => __( 'Recurring' ),
            'required'      => true,            
        ), $checkout->get_value( 'wss_billing_interval' ) );
        // Period field
        woocommerce_form_field( 'wss_billing_period', array(
            'type'      => 'select',
            'options'   => array(
                'day'   => 'day',
                'week'  => 'week',
                'month' => 'month',
                'year'  => 'year'
            ),
            'class'         => array( 'billing-period wss-form-field' ),
            'label'         => 'Period',
            'required'      => true,
        ), $checkout->get_value( 'wss_billing_period' ) );
        // Start date
        woocommerce_form_field( 'wss_start_date', array(
            'type'          => 'text',
            'class'         => array( 'wss-start-date wss-form-field' ),
            'label'         => __( 'Start Date' ),
            'placeholder'   => __( 'YYYY-MM-DD' ),
            'required'      => true,
        ), current_time( 'Y-m-d' ) );
        echo '@';
        // Start hours
        woocommerce_form_field( 'wss_start_hours', array(
            'type'          => 'number',
            'class'         => array( 'wss-start-hours wss-number-fields wss-form-field' ),
            'label'         => __( '' ),
            'placeholder'   => __( '' ),
            'required'      => true
        ), current_time( 'H' ) );
        echo ':';
        // Start minutes
        woocommerce_form_field( 'wss_start_minutes', array(
            'type'          => 'number',
            'class'         => array( 'wss-start-minutes wss-number-fields wss-form-field' ),
            'label'         => __( '' ),
            'placeholder'   => __( '' ),
            'required'      => true
        ), current_time( 'i' ) );
        echo '</div>';
    }

    /**
    * Update order meta with subscription details data
    */
    public function wss_save_subscription_fields( $order_id ) {
        if( !empty( sanitize_text_field( $_POST['wss_show_subscription_fields'] ) ) ) {
            $subscription_billing_interval = sanitize_text_field(  $_POST['wss_billing_interval'] );
            $subscription_billing_period = sanitize_text_field( $_POST['wss_billing_period'] );
            $subscription_start_date = sanitize_text_field( $_POST['wss_start_date'] );
            $subscription_start_hours = sanitize_text_field( $_POST['wss_start_hours'] );
            $subscription_start_minutes = sanitize_text_field( $_POST['wss_start_minutes'] );
            $all_subscription_fields = array(
                '_wss_subscribing'      => sanitize_text_field( $_POST['wss_show_subscription_fields'] ),
                '_wss_billing_interval' => $subscription_billing_interval,
                '_wss_billing_period'   => $subscription_billing_period,
                '_wss_start_date'       => $subscription_start_date,
                '_wss_start_hours'      => $subscription_start_hours,
                '_ws_start_minutes'     => $subscription_start_minutes
            );

            foreach( $all_subscription_fields as $post_meta_name => $post_meta_value ) {
                update_post_meta( $order_id, $post_meta_name, $post_meta_value );
            }
        }
    }

    /**
    * Creates subscription based on order
    * 
    * @param int $order_id - order id that is taken as base
    *
    * @param boll $subscribing - is subscription option enabled
    */
    public function wss_create_subscription( $order_id ) {
        $order_details = new WC_Order( $order_id );
        $order_meta = get_post_meta( $order_id, '', true );

        $user_subscribes = ( isset( $order_meta['_wss_subscribing'] ) && $order_meta['_wss_subscribing'][0] == 1 )? true : false;

        if( $user_subscribes ) {
            $order_owner_details = get_user_by( 'id', $order_details->post->post_author );
            $all_ordered_goods = $order_details->get_items();

            $subscription_id = wp_insert_post( array(
                'post_type'     => 'shop_subscription',
                'post_title'    => 'Subscription',
                'post_content'  => '',
                'post_author'   => $order_details->post->post_author,
                'post_status'   => 'wc-pending'
            ));

            $is_subscription_set_as_futured = wss_are_dates_equal( date( 'Y-m-d', strtotime( $order_details->post->post_date ) ), date( 'Y-m-d', strtotime( $order_meta['_wss_start_date'][0] ) ) );

            $trial_period_end_date = '';
            if( $is_subscription_set_as_futured == false ) {
                $trial_period_duration = is_subscription_set_as_futured( date( 'Y-m-d', strtotime( $order_details->post->post_date ) ), date( 'Y-m-d', strtotime( $order_meta['_wss_start_date'][0] ) ) );
                $trial_period_end_date = date( 'Y-m-d', strtotime( $order_details->post->post_date . ' +' . $trial_period_duration . ' days' ) );
                $trial_period_end_date = date( 'Y-m-d H:s', strtotime( $trial_period_end_date . ' +' . $order_meta['_wss_start_hours'][0] . ' hours' ) );
                $trial_period_end_date = date( 'Y-m-d H:s', strtotime( $trial_period_end_date . ' +' . $order_meta['_ws_start_minutes'][0] . ' minutes' ) );
            }

            $subscription_details = array(
                '_billing_interval'         => $order_meta['_wss_billing_interval'][0],
                '_billing_period'           => $order_meta['_wss_billing_period'][0],
                '_schedule_end'             => '',
                '_schedule_trial_end'       => $trial_period_end_date,
                '_schedule_next_payment'    => 0,
                '_customer_user'            => $order_details->post->post_author,
                '_billing_first_name'       => $order_meta['_billing_first_name'][0],
                '_billing_last_name'        => $order_meta['_billing_last_name'][0],
                '_billing_company'          => $order_meta['_billing_company'][0],
                '_billing_address_1'        => $order_meta['_billing_address_1'][0],
                '_billing_address_2'        => $order_meta['_billing_address_2'][0],
                '_billing_city'             => $order_meta['_billing_city'][0],
                '_billing_postcode'         => $order_meta['_billing_postcode'][0],
                '_billing_country'          => $order_meta['_billing_country'][0],
                '_billing_state'            => $order_meta['_billing_state'][0],
                '_billing_email'            => $order_meta['_billing_email'][0],
                '_billing_phone'            => $order_meta['_billing_phone'][0],
                '_requires_manual_renewal'  => false,
                '_order_total'              => $order_details->get_total()
            );

            foreach( $subscription_details as $meta_name => $meta_value ) {
                update_post_meta( $subscription_id, $meta_name, $meta_value );
            }

            foreach( $all_ordered_goods as $order_item_id => $good_details ) {
                $subscription_item_id = woocommerce_add_order_item( $subscription_id, array(
                    'order_item_name'       => $good_details['name'],
                    'order_item_type'       => 'line_item',
                ));
                if( $subscription_item_id ) {
                    woocommerce_add_order_item_meta( $subscription_item_id, '_qty', $good_details['item_meta']['_qty'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_tax_class', $good_details['item_meta']['_tax_class'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_product_id', $good_details['item_meta']['_product_id'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_variation_id', $good_details['item_meta']['_variation_id'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_line_subtotal', $good_details['item_meta']['_line_subtotal'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_line_subtotal_tax', $good_details['item_meta']['_line_subtotal_tax'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_line_total', $good_details['item_meta']['_line_total'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_line_tax', $good_details['item_meta']['_line_tax'][0] );
                    woocommerce_add_order_item_meta( $subscription_item_id, '_line_tax_data', $good_details['item_meta']['_line_tax_data'][0] );
                }
            }
        }
    }
}

new WSS_Checkout();
