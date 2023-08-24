<?php
if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

class MeprPayPalTroubleshooterHelper {
  public function getMethodById($id) {
    $mepr_options = MeprOptions::fetch();
    return $mepr_options->payment_method($id);
  }

  public function getPayPalIntegrations() {
    $mepr_options = MeprOptions::fetch();
    $integrations = $mepr_options->integrations;
    $paypal_gateways = [];

    foreach ($integrations as $integration) {
      if ($integration['gateway'] == 'MeprPayPalCommerceGateway') {
        $paypal_gateways[] = $integration;
      }
    }

    return $paypal_gateways;
  }

  public function getMethod($integrations, $id) {
    foreach ($integrations as $integration) {
      if ($integration['id'] == $id) {
        return $integration;
      }
    }

    return [];
  }

  public function getActiveCredential($paypal_settings) {
    if (!empty($paypal_settings['live_client_id']) && !empty($paypal_settings['live_client_secret'])) {
      return [
        'sandbox' => false,
        'client_id' => $paypal_settings['live_client_id'],
        'secret_id' => $paypal_settings['live_client_secret'],
      ];
    }

    if (!empty($paypal_settings['test_client_id']) && !empty($paypal_settings['test_client_secret'])) {
      return [
        'sandbox' => true,
        'client_id' => $paypal_settings['test_client_id'],
        'secret_id' => $paypal_settings['test_client_secret'],
      ];
    }

    return [];
  }
}