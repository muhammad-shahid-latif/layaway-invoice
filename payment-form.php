<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<form method="post">
  <label for="amount"><?php esc_html_e('Amount', 'layaway-invoice'); ?>:</label>
  <input type="text" name="amount" id="amount" required><br>

  <label for="card_number"><?php esc_html_e('Card Number', 'layaway-invoice'); ?>:</label>
  <input type="text" name="card_number" id="card_number" required><br>

  <label for="expiration_month"><?php esc_html_e('Expiration Month', 'layaway-invoice'); ?>:</label>
  <input type="text" name="expiration_month" id="expiration_month" required><br>

  <label for="expiration_year"><?php esc_html_e('Expiration Year', 'layaway-invoice'); ?>:</label>
  <input type="text" name="expiration_year" id="expiration_year" required><br>

  <label for="cvv"><?php esc_html_e('CVV', 'layaway-invoice'); ?>:</label>
  <input type="text" name="cvv" id="cvv" required><br>

  <input type="submit" name="authorize_net_payment_submit" value="<?php esc_html_e('Submit Payment', 'layaway-invoice'); ?>">
</form>
