<?php
namespace Jeeb\Merchant;


use Jeeb\Merchant;

class Order extends Merchant
{

  const PLUGIN_NAME = 'zencart';
  const PLUGIN_VERSION = '3.0';
  const BASE_URL = "https://core.jeeb.io/api/";

  public static function convert_base_to_bitcoin($amount, $signature, $baseCur) {
      error_log("Entered into Convert Base To Target");

      // return Jeeb::convert_irr_to_btc($url, $amount, $signature);
      $ch = curl_init(self::BASE_URL.'currency?'.$signature.'&value='.$amount.'&base='.$baseCur.'&target=btc');
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'User-Agent:'.self::PLUGIN_NAME . '/' . self::PLUGIN_VERSION)
    );

    $result = curl_exec($ch);
    $data = json_decode( $result , true);
    error_log('Response =>'. var_export($data, TRUE));
    // Return the equivalent bitcoin value acquired from Jeeb server.
    return (float) $data["result"];

    }


    public static function create_payment($options = array(), $signature) {

        $post = json_encode($options);

        $ch = curl_init(self::BASE_URL.'payments/' . $signature . '/issue/');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post),
            'User-Agent:'.self::PLUGIN_NAME . '/' . self::PLUGIN_VERSION)
        );

        $result = curl_exec($ch);
        $data = json_decode( $result ,true );
        error_log('Response =>'. var_export($data, TRUE));

        return $data['result']['token'];

    }


    public static function confirm_payment($signature, $options = array()) {

        $post = json_encode($options);
        $ch = curl_init(self::BASE_URL . 'payments/' . $signature . '/confirm/');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'User-Agent:' . self::PLUGIN_NAME . '/' . self::PLUGIN_VERSION,
        ));
        $result = curl_exec($ch);
        $data = json_decode($result, true);
        error_log('Response =>'. var_export($data, TRUE));
        return (bool) $data['result']['isConfirmed'];

    }

    public static function redirect_payment($token) {
      error_log("Entered into auto submit-form");
      // Using Auto-submit form to redirect user with the token
      echo "<form id='form' method='post' action='".self::BASE_URL."payments/invoice'>".
              "<input type='hidden' autocomplete='off' name='token' value='".$token."'/>".
             "</form>".
             "<script type='text/javascript'>".
                  "document.getElementById('form').submit();".
             "</script>";
    }

}
