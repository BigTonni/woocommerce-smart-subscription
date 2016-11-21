<?php

/* 
 * Helpers
 */

function vardump( $str ) {
    echo "<pre>";
    var_dump($str);
    echo "</pre>";
}

/**
* Getting all subscriptions for specific user
*
* @param int $user_id - user ID to take all subscription for, string $status - subscription status
*
* @return array - all subscriptions, bool false - if something went wrong or user id is not specified
*/
function wss_get_all_user_subscriptions( $user_id, $status = 'any' ) {
    if( !isset( $user_id ) || empty( $user_id ) ) 
        return false;

    $subscriptions_parameters = array(
        'author'    	 => $user_id,
        'orderby'  	     => 'post_date',
        'order'     	 => 'DESC',
        'post_type' 	 => 'shop_subscription',
        'post_status'	 => $status,
        'posts_per_page' => -1
    );

    $all_subscriptions = get_posts( $subscriptions_parameters );

    return $all_subscriptions;
}

/**
* Getting all products in subscription
*
* @param int $subscription_id - ID of the subscription
*
* @return array - all products in subscription, bool false - if something went wrong or subscription id is not specified
*/
function wss_get_all_subscription_products( $subscription_id ) {
	if( empty( $subscription_id ) )
		return false;

	$subscription = new WC_Order( $subscription_id );
	$all_products = $subscription->get_items();

	return $all_products;
}
/**
* Checking if arrays are equal
*
* @param array $array1, array $array2
*
* @return bool true - if arrays are equal, false - if not
*/
function wss_are_arrays_equal( $array1, $array2 ) {
	array_multisort( $array1 );
	array_multisort( $array2 );
	return ( serialize( $array1 ) === serialize( $array2 ) );
}

/**
* Compares 2 dates and checks if first one is greated than another one
*
* @param string $date_start ('Y-m-d') - start date
*
* @param string $date_end ('Y-m-d') - end date
*
* @return bool true - if dates are equal, false - if not or something went wrong
*/
function wss_are_dates_equal( $start_date, $end_date ) {
    if( empty( $start_date ) || empty( $end_date ) ) {
        return false;
    }

    $formatted_start_date = date( 'Y-m-d', strtotime( $start_date ) );
    $formatted_end_date = date( 'Y-m-d', strtotime( $end_date ) );

    return ( strtotime( $formatted_start_date ) == strtotime( $formatted_end_date ) );
}

/**
* Getting difference between 2 dates in days
*
* @param string date1 ('Y-m-d')
*
* @param string date2 ('Y-m-d')
*
* @return integer - number of days, or bool false if something went wrong
*/
function is_subscription_set_as_futured( $date1, $date2 ) {
    if( empty( $date1 ) || empty( $date2 ) ) {
        return false;
    }
    if( strtotime( $date2 ) < strtotime( $date1 ) )
        return false;

    $formatted_date1 = date( 'Y-m-d', strtotime( $date1 ) );
    $formatted_date2 = date( 'Y-m-d', strtotime( $date2 ) );

    $datetime1 = new DateTime( $formatted_date1 );
    $datetime2 = new DateTime( $formatted_date2 );
    $difference = $datetime2->diff( $datetime1 );

    $trial_days = $difference->d;

    return $trial_days;
}