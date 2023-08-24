<?php if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
} ?>
<?php
$page = isset($_GET['page']) ? \sanitize_key(\wp_unslash($_GET['page'])) : '';

if (count($paypal_gateways) > 0) {
  $selected = $paypal_gateways[0]['id'];
} else {
  $selected = null;
}
$config = [
  'activeGateway' => $selected,
  'ipn_from' => $ipn_from ?? '',
  'ipn_to' => $ipn_to ?? '',
  'webhook_url' => $webhook_url ?? '',
  'webhooks' => $ipns ?  (($ipns['events'])) : '',
  'webhook_count' => $ipns['count'] ??  0,
];
?>
<script>
  const MeprPPTroubleshooterJS = {
      processWHK: (webhook, methodId, webhook_url) => {
          jQuery.ajax({
              url: webhook_url,
              data: JSON.stringify(webhook),
              type: "post",
              headers: {
                  "content-type": "application/json;charset=UTF-8"
              },
              beforeSend: function (){
                  console.log("Waiting...");
              },
              error: function() {
                  alert("Error");
              },
              success: function (data){
                  alert('Success');
              }
          });
      }
  };
</script>
<div class="wrap" x-data='<?php echo (json_encode($config)); ?>'>
  <div class="icon32"></div>
  <h2><?php _e('MemberPress PayPal Troubleshooter', 'memberpress'); ?></h2>

  <form method="get" action="">
    <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>">
    <?php if (count($paypal_gateways) > 0) { ?>
      <label><?php _e('Choose PayPal method', 'memberpress'); ?></label>&nbsp;
      <select x-model="activeGateway" name="selected_method">
        <?php foreach ($paypal_gateways as $gateway) { ?>
          <option value="<?php echo $gateway['id']; ?>"><?php echo $gateway['label']; ?></option>
        <?php } ?>
      </select>
      <br>
    <?php } ?>
    <h3><?php _e('Webhook logs', 'memberpress'); ?></h3>

    <?php _e('From', 'memberpress'); ?><input x-model='ipn_from' name="ipn-from" type="datetime-local"/>
    <?php _e('To', 'memberpress'); ?><input x-model='ipn_to' name="ipn-to" type="datetime-local"/>
    <p x-show="ipn_from">
      <?php _e('Found', 'memberpress'); ?> <b x-text="parseInt(webhook_count)"></b>
    </p>
    <table border="1" cellspacing="0" width="100%">
      <tr>
        <th><?php _e('Time', 'memberpress'); ?></th>
        <th><?php _e('Event', 'memberpress'); ?></th>
        <th><?php _e('Summary', 'memberpress'); ?></th>
        <th><?php _e('Resource', 'memberpress'); ?></th>
        <th><?php _e('Action', 'memberpress'); ?></th>
      </tr>
    <template x-for="webhook in webhooks" :key="webhook.id">
      <tr>
        <td x-text="webhook.create_time"></td>
        <td x-text="webhook.event_type"></td>
        <td x-text="webhook.summary"></td>
        <td><textarea style="width:100%; height: 100px" x-text="JSON.stringify(webhook.resource)"></textarea></td>
        <td>
          <button type="button" x-on:click="MeprPPTroubleshooterJS.processWHK(webhook, activeGateway, webhook_url)" title="Replay this webhook to MemberPress PayPal gateway">Process</button>
        </td>
      </tr>
    </template>
    </table>
    <br>
    <button type="submit" name="search-ipn" value="1"><?php _e('Search', 'memberpress'); ?></button>
  </form>
</div> <!-- End wrap -->
