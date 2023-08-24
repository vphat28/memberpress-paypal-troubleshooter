<?php
if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

class MeprPayPalTroubleshooterApi
{
  protected $client_id;
  protected $secret;
  protected $sandbox;

  public function __construct($client_id, $secret, $sandbox = false)
  {
    $this->client_id = $client_id;
    $this->secret = $secret;
    $this->sandbox = $sandbox;
  }

  public function getBaseUrl()
  {
    return $this->sandbox ? 'https://api.sandbox.paypal.com/' : 'https://api-m.paypal.com/';
  }

  public function getAuthentication()
  {
    return base64_encode($this->client_id . ':' . $this->secret);
  }

  /**
   * @param DateTime $start
   * @param DateTime $end
   * @return array
   */
  public function getLogs($start, $end)
  {
    $url = $this->getBaseUrl() . 'v1/notifications/webhooks-events?end_time=' . $end->format('Y-m-d\TH:i:s\Z') . '&start_time=' . $start->format('Y-m-d\TH:i:s\Z');
    $headers = array(
      'headers' => array(
        'Authorization' => 'Basic ' . $this->getAuthentication(),
      )
    );
    $response = wp_remote_get(
      $url,
      $headers
    );

    $response = wp_remote_retrieve_body($response);

    return json_decode($response, true);
  }
}