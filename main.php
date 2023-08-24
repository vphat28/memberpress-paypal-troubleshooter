<?php
/*
Plugin Name: MemberPress PayPal Troubleshooter
Plugin URI: http://memberpress.com
Description: A MemberPress helper tool for PayPal gateways
Version: 1.0.0
Author: Caseproof, LLC
Author URI: http://caseproof.com
Text Domain: memberpress
*/

if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (is_plugin_active('memberpress/memberpress.php')) {
  define('MEPRPAYPALTOOL_PLUGIN_SLUG', plugin_basename(__FILE__));
  define('MEPRPAYPALTOOL_PLUGIN_NAME', dirname(MEPRPAYPALTOOL_PLUGIN_SLUG));
  define('MEPRPAYPALTOOL_PATH', WP_PLUGIN_DIR . '/' . MEPRPAYPALTOOL_PLUGIN_NAME);
  define('MEPRPAYPALTOOL_URL', plugins_url('/' . MEPRPAYPALTOOL_PLUGIN_NAME));
  define('MEPRPAYPALTOOL_EDITION', 'memberpress-paypal-troubleshooter');

  add_action('admin_enqueue_scripts', 'meprpaypaltool_enqueue_scripts');
  add_action('mepr_menu', 'meprpaypaltool_add_menu_page');
  add_action('admin_init', 'meprpaypaltool_maybe_replay_webhook');

  function meprpaypaltool_maybe_replay_webhook() {
    if (isset($_GET['mepr_paypal_troubleshooter_webhook_replay']) && $_GET['mepr_paypal_troubleshooter_webhook_replay'] == 1) {
      include_once MEPRPAYPALTOOL_PATH . '/classes/helper.php';
      $helper = new MeprPayPalTroubleshooterHelper();
      /** @var MeprPayPalCommerceGateway $gateway */
      $gateway = $helper->getMethodById(sanitize_text_field($_POST['methodId']));
      $response = wp_remote_post($gateway->notify_url('webhook'), [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $_POST['webhook'],
      ]);

      var_dump(($response));
      var_dump(wp_remote_retrieve_body($response));
      exit;
    }
  }

  function meprpaypaltool_enqueue_scripts($hook) {
    if(strstr($hook, MEPRPAYPALTOOL_EDITION) !== false) {
      wp_enqueue_script('alpinejs', MEPR_JS_URL . '/vendor/alpine.min.js', array(), MEPR_VERSION, true);
    }
  }

  function meprpaypaltool_add_menu_page() {
    add_submenu_page('memberpress', __('Paypal Troubleshooter', 'memberpress'), __('Paypal Troubleshooter', 'memberpress'), 'administrator', MEPRPAYPALTOOL_EDITION, 'meprpaypaltool_show_menu_page');
  }

  function meprpaypaltool_show_menu_page() {
    include_once MEPRPAYPALTOOL_PATH . '/classes/helper.php';
    $helper = new MeprPayPalTroubleshooterHelper();
    $paypal_gateways = $helper->getPayPalIntegrations();

    if (isset($_GET['search-ipn'])) {
      include_once MEPRPAYPALTOOL_PATH . '/classes/paypal-api.php';
      $method = $helper->getMethod($paypal_gateways, $_GET['selected_method'] ?? '');
      $creds = $helper->getActiveCredential($method);
      /** @var MeprPayPalCommerceGateway $gateway */
      $gateway = $helper->getMethodById($method['id']);
      $webhook_url = $gateway->notify_url('webhook');

      if (!empty($creds)) {
        $api = new MeprPayPalTroubleshooterApi($creds['client_id'], $creds['secret_id'], $creds['sandbox']);
        $ipn_from = sanitize_text_field($_GET['ipn-from'] ?? '');
        $ipn_from_obj = !empty($ipn_from) ? new DateTime($ipn_from) : null;
        $ipn_to = sanitize_text_field($_GET['ipn-to'] ?? '');
        $ipn_to_obj = !empty($ipn_to) ? new DateTime($ipn_to) : null;
        $ipns = $api->getLogs($ipn_from_obj, $ipn_to_obj);
      }
    }

    require(MEPRPAYPALTOOL_PATH.'/views/admin_page.php');
  }
} //End if (is plugin active)
