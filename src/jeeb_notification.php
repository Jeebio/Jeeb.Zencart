<?php

require('includes/application_top.php');
require_once(dirname(__FILE__) . "/includes/modules/payment/Jeeb/init.php");
require_once(dirname(__FILE__) . "/includes/modules/payment/Jeeb/version.php");



global $db;

$postdata = file_get_contents("php://input");
$json = json_decode($postdata, true);

$signature     = MODULE_PAYMENT_JEEB_API_KEY;

if($json['signature'] == $signature){

  error_log("Entered into Notification");
  error_log("Response =>". var_export($json, TRUE));

  if ( $json['stateId']== 2 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId'].' status no = '.MODULE_PAYMENT_JEEB_PENDING_STATUS_ID);
  }
  else if ( $json['stateId']== 3 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId'].' status no = '.MODULE_PAYMENT_JEEB_PAID_STATUS_ID);
    $order_status = MODULE_PAYMENT_JEEB_PAID_STATUS_ID;
  }
  else if ( $json['stateId']== 4 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId']);

    $data = array(
      "token" => $json['token'],
    );

    $is_confirmed = \Jeeb\Merchant\Order::confirm_payment($signature, $data);

    if($is_confirmed){
      error_log('Payment confirmed by jeeb');
      $order_status = MODULE_PAYMENT_JEEB_CONFIRMED_STATUS_ID;

    }
    else {
      error_log('Payment rejected by jeeb');
    }
  }
  else if ( $json['stateId']== 5 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId']);
    $order_status = MODULE_PAYMENT_JEEB_EXPIRED_STATUS_ID;
  }
  else if ( $json['stateId']== 6 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId']);
    $order_status = MODULE_PAYMENT_JEEB_REFUNDED_STATUS_ID;
  }
  else if ( $json['stateId']== 7 ) {
    // error_log('Order Id received = '.$json['orderNo'].' stateId = '.$json['stateId']);
    $order_status = MODULE_PAYMENT_JEEB_REFUNDED_STATUS_ID;
  }
  else{
    error_log('Cannot read state id sent by Jeeb');
  }


  if ($order_status)
    $db->Execute("update ". TABLE_ORDERS. " set orders_status = " . $order_status . " where orders_id = ". intval($json['orderNo']));

  echo 'OK';

}
