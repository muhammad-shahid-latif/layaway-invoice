<?php
/**********************order invoice settings tab*************************/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function laya_invc_layaway_invoice_settings_sub_menu_func() {

    // Check if the current user has permission to manage options.

if (current_user_can('manage_options')) {     
      //get payment mode and api login ids from payment mode settings form
     if (!empty(esc_html(sanitize_text_field($_POST['payment_mode_integration_form']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['payment_mode_integration_form_nonce_field'])), 'payment_mode_integration_form_nonce')) {
        

        $test_api_login_id      = sanitize_text_field(wp_unslash($_POST['test_api_login_id']));
        $test_transaction_key   = sanitize_text_field(wp_unslash($_POST['test_transaction_key']));
        $live_api_login_id      = sanitize_text_field(wp_unslash($_POST['live_api_login_id']));
        $live_transaction_key   = sanitize_text_field(wp_unslash($_POST['live_transaction_key']));

         // Save the data into the database using update_option() function
         update_option('laya_invc_test_api_login_id',     $test_api_login_id);
         update_option('laya_invc_test_transaction_key',  $test_transaction_key);
         update_option('laya_invc_live_api_login_id',     $live_api_login_id);
         update_option('laya_invc_live_transaction_key',  $live_transaction_key);
          
          
          // Output a success message when given data successfully saved
         echo '<div id="message" class="updated "><p><strong>'.esc_html__('Payment mode settings saved.', 'layaway-invoice').'</strong></p></div>';
     }
  }

     /****************************************************************/
       //get admin email to recieved email when on new order from email settings form
if (current_user_can('manage_options')) {
     if (!empty(esc_html(sanitize_text_field($_POST['admin_email_for_new_order_and_mail_text']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['admin_email_for_new_order_and_mail_text_nonce_field'])), 'admin_email_for_new_order_and_mail_text_nonce')) {


        $admin_new_order_email             = esc_html(sanitize_email($_POST['admin_new_order_email']));
        $admin_new_order_email_subject     = sanitize_text_field(wp_unslash($_POST['admin_new_order_email_subject']));
        $admin_new_order_email_heading     = sanitize_text_field(wp_unslash($_POST['admin_new_order_email_heading']));
        $admin_new_order_email_additional_content = sanitize_textarea_field(wp_unslash($_POST['admin_new_order_email_additional_content']));
        $customer_order_email_sender_name  = sanitize_text_field(wp_unslash($_POST['customer_email_sender_name']));
        $customer_new_invoice_mail_subj    = sanitize_text_field(wp_unslash($_POST['customer_new_invoice_mail_subj']));
        
        
         // Save the data into the database using update_option() function
         update_option('laya_invc_admin_new_order_email',                    $admin_new_order_email);
         update_option('laya_invc_admin_new_order_email_subject',            $admin_new_order_email_subject);
         update_option('laya_invc_admin_new_order_email_heading',            $admin_new_order_email_heading);
         update_option('laya_invc_customer_email_sender_name',               $customer_order_email_sender_name);
         update_option('laya_invc_customer_new_incoice_mail_subj',           $customer_new_incoice_mail_subj);
         update_option('laya_invc_admin_new_order_email_additional_content', $admin_new_order_email_additional_content);


            // Output a success message when given data successfully saved
         echo '<div id="message" class="updated "><p><strong>'.esc_html__('Email settings saved.', 'layaway-invoice').'</strong></p></div>';

     } 
}

     /****************************************************************/
  if (current_user_can('manage_options')) {    
       //get plugin typography
     if (!empty(esc_html(sanitize_text_field($_POST['plugin_typography']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['plugin_typography_nonce_field'])), 'plugin_typography_nonce')) {

         $layaway_base_color            =esc_html(sanitize_text_field($_POST['base_color']));
         $layaway_privacy_policy_link   = esc_url(sanitize_text_field($_POST['layaway_privacy_policy_link']));

       // Save the data into the database using update_option() function
         update_option('laya_invc_layaway_base_color',    $layaway_base_color);
         update_option('laya_invc_layaway_privacy_policy_page_link',    $layaway_privacy_policy_link);

           // Output a success message when given data successfully saved
         echo '<div id="message" class="updated "><p><strong>'.esc_html__('Typography saved.', 'layaway-invoice').'</strong></p></div>';
      
     }
}
      /****************************************************************/
 if (current_user_can('manage_options')) {      
       //get plugin partail payment
     if (!empty(esc_html(sanitize_text_field($_POST['partaily__payment']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['partaily__payment_nonce_field'])), 'partaily__payment_nonce')) {

       $partialy_payment_set = esc_html(sanitize_text_field($_POST['val_partaily__payment']));
       // Save the data into the database using update_option() function
         update_option('laya_invc_partialy_payment_in_percentage',    $partialy_payment_set);

           // Output a success message when given data successfully saved
         echo '<div id="message" class="updated "><p><strong>'.esc_html__('partial payment settings saved.', 'layaway-invoice').'</strong></p></div>';
      
     }
}

 if (current_user_can('manage_options')) {     
      //Get and Saved  payment mode into database by on check payemnt mode checkbox
 if (!empty(esc_html(sanitize_text_field($_POST['partaily__payment']))) && !empty(esc_html(sanitize_text_field($_POST['layaway_choose_partial_payment'])))) {

   $selected_partial_payment =esc_html(sanitize_text_field($_POST['layaway_choose_partial_payment']));
    update_option('laya_invc_layaway_choose_partial_payment', $selected_partial_payment); 
}

}
 //*****************************************************************************/ 
 if (current_user_can('manage_options')) {
       //get layaway invoice expired days and saved into database
     if (!empty(esc_html(sanitize_text_field($_POST['layaway_invoice_expiry_days']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['layaway_invoice_expiry_days_nonce_field'])), 'layaway_invoice_expiry_days_nonce')) {


       $selected_expiry_days = esc_html(sanitize_text_field($_POST['invoice_expiry_days']));

       // Save the data into the database using update_option() function
         update_option('laya_invc_layaway__invoice_expired_days',    $selected_expiry_days);

           // Output a success message when given data successfully saved
         echo '<div id="message" class="updated "><p><strong>'.esc_html__('Layaway invoice expired days saved.', 'layaway-invoice').'</strong></p></div>';
      
     }
 }else{
        $check_expiry_days = get_option('laya_invc_layaway__invoice_expired_days');
            if(!isset($check_expiry_days)){

             // Save the data into the database using update_option() function
            update_option('laya_invc_layaway__invoice_expired_days',    '14 days');

           }
     
     }
 
 
 /*****************************************************************************/ 
  if (current_user_can('manage_options')) {
  //Get and Saved  payment mode into database by on check payemnt mode checkbox
   if (!empty(esc_html(sanitize_text_field($_POST['payment_mode']))) && sanitize_text_field(wp_unslash($_POST['payment_mode'] === 'test'))) {
     
       $test_payment_mode     = esc_html(sanitize_text_field($_POST['payment_mode']));
       update_option('laya_invc_payment_mode',  $test_payment_mode);

   }
}
 if (current_user_can('manage_options')) {
    if (!empty(esc_html(sanitize_text_field($_POST['payment_mode']))) && esc_html(sanitize_text_field(wp_unslash($_POST['payment_mode'] === 'live')))) {
     
       $live_payment_mode     = esc_html(sanitize_text_field(wp_unslash($_POST['payment_mode'])));
       update_option('laya_invc_payment_mode',  $live_payment_mode);
   }
}
   //plugin base color
       $base_color         = get_option('laya_invc_layaway_base_color');

      ?>

<!--********************************layaway settings*********************************************-->
    <section class="main-full-container">
    <div class="main_heading_area">
        <h1><?php echo esc_html('Authorize.net', 'layaway-invoice'); ?></h1>
    </div>
    <div  style="background-color:<?php echo esc_attr($base_color) ?>; border: 1px solid <?php echo esc_attr($base_color) ?>;" class="tab">
        <button class="tablinks" id="paymentmode_tab"  onclick="integrate_mode(event, 'payment_mode')"><?php echo esc_html('Payment Mode', 'layaway-invoice');?></button>
        <button class="tablinks"  onclick="integrate_mode(event, 'admin_email')"><?php echo esc_html('Emails & Settings', 'layaway-invoice');?></button>
        <button class="tablinks"  onclick="integrate_mode(event, 'typography')"><?php echo esc_html('Typography and Others', 'layaway-invoice');?></button>
        <button class="tablinks" onclick="integrate_mode(event, 'partail_payment')"><?php echo esc_html('Partailly Payment', 'layaway-invoice');?></button>
        <button class="tablinks" onclick="integrate_mode(event, 'layaway_expired_days')"><?php echo esc_html('Layaway Invoice Expired Days', 'layaway-invoice');?></button>
    </div>
  
      <!--Start Payment integrtion form -->
    <form method="post">
        <div id="payment_mode" class="tabcontent" style="border: 1px solid <?php echo esc_attr($base_color) ?>">
             <h2><?php echo esc_html('Test Mode', 'layaway-invoice'); ?></h2>
        <div class="input-main">
            <label><?php echo esc_html('Api Login id', 'layaway-invoice'); ?></label>
            <input type="text" placeholder="<?php echo esc_attr('Api Login id'); ?>" id="consumer_key_fields" name="test_api_login_id" value="<?php echo esc_attr(get_option('laya_invc_test_api_login_id')); ?>">
        </div>

        <div class="input-main">
            <label><?php echo esc_html('Transaction Key', 'layaway-invoice'); ?></label> 
            <input type="text" placeholder="<?php echo esc_html('Transaction Key', 'layaway-invoice'); ?>" id="consumer_secret_fields" name="test_transaction_key" value="<?php echo esc_attr(get_option('laya_invc_test_transaction_key')); ?>">
        </div>

         <label><?php echo esc_html('Enable Test mode', 'layaway-invoice'); ?></label>

             <?php 
            //check enable payment mode 
            if (get_option('laya_invc_payment_mode') == 'test') {

                 echo'<input type="checkbox" id="test_mode" name="payment_mode" value="test" checked>';
            }
            else
            {
               

               echo' <input type="checkbox" id="test_mode" name="payment_mode" value="test">';
            }

        ?>

          <hr>

          <h2><?php echo esc_html('Live Mode', 'layaway-invoice'); ?></h2>
        <div class="input-main">
            <label><?php echo esc_html('Api Login id', 'layaway-invoice'); ?></label>
            <input type="text" placeholder="<?php echo esc_html('Api Login id', 'layaway-invoice'); ?>" id="consumer_key_fields" name="live_api_login_id" value="<?php echo esc_attr(get_option('laya_invc_live_api_login_id')); ?>">
        </div>
        <div class="input-main">
            <label><?php echo esc_html('Transaction Key', 'layaway-invoice'); ?></label>
            <input type="text" placeholder="<?php echo esc_html('Transaction Key', 'layaway-invoice'); ?>" id="consumer_secret_fields" name="live_transaction_key" value="<?php echo esc_attr(get_option('laya_invc_live_transaction_key')); ?>">
        </div>
        <label><?php echo esc_html('Enable Live mode', 'layaway-invoice'); ?></label>
        <?php 
            //check enable payment mode 
             if (get_option('laya_invc_payment_mode') == 'live') {

                 echo'<input type="checkbox" id="live_mode" name="payment_mode" value="live" checked >';
            }
            else
            {
                
               echo'<input type="checkbox" id="live_mode" name="payment_mode" value="live">';
            }
           // Add nonce field
            wp_nonce_field('payment_mode_integration_form_nonce', 'payment_mode_integration_form_nonce_field');
        ?>

        <div class="button-main">
            <input style="background-color:<?php echo esc_attr($base_color); ?>;border: 1px solid <?php echo esc_attr($base_color); ?>" type="submit" name="payment_mode_integration_form" class="submit-btn-1" value="<?php echo esc_attr('Save'); ?>">
        </div>

       </div>
    </form>
     <!-- End payment integration form settings -->
  <!--/******************************************************************************************/-->
      <!-- Start Email settings tab -->
    <form method="post">
     <div id="admin_email" class="tabcontent" style="border: 1px solid <?php echo esc_attr($base_color); ?>">
    <h2><?php echo esc_html('New order', 'layaway-invoice'); ?></h2>
    <p><?php echo esc_html('New order email are sent to chosen recipents(s) when a new order is received.', 'layaway-invoice'); ?></p>
        <div class="input-main">
        <label><?php echo esc_html('Admin Email', 'layaway-invoice'); ?></label>
        <input type="text" placeholder="<?php echo esc_attr('Enter New Order email'); ?>" id="consumer_key_fields" name="admin_new_order_email" value="<?php echo esc_attr(get_option('laya_invc_admin_new_order_email')); ?>">
       </div>
        

      <div class="input-main">
      <label><?php echo esc_html('Subject', 'layaway-invoice'); ?></label>
      <input type="text"  placeholder="<?php echo esc_attr('[{site_title}]: New order #{order_number}'); ?>" id="consumer_key_fields" name="admin_new_order_email_subject"  value="<?php echo esc_attr(get_option('laya_invc_admin_new_order_email_subject')); ?>">
      </div>

      <div class="input-main">
      <label><?php echo esc_html('Email heading', 'layaway-invoice'); ?></label>
      <input type="text"  placeholder="<?php echo esc_attr('New Order: #{order_number}'); ?>" id="consumer_key_fields" name="admin_new_order_email_heading"  value="<?php echo esc_attr(get_option('laya_invc_admin_new_order_email_heading')); ?>">
      </div>
      
      <div class="input-main">
      <label><?php echo esc_html('Additional content', 'layaway-invoice'); ?></label>
      <input type="text"  placeholder="<?php echo esc_attr('Congratulations on new Order.'); ?>" id="consumer_key_fields" name="admin_new_order_email_additional_content"  value="<?php echo esc_attr(get_option('laya_invc_admin_new_order_email_additional_content')); ?>">
      </div>

    <!-- Text that show in customer email -->
      <h2><?php echo esc_html('Customer Email Text:', 'layaway-invoice'); ?></h2>
      <div class="input-main">
      <label><?php echo esc_html('"From" name', 'layaway-invoice'); ?></label>
       <input type="text"  placeholder="<?php echo esc_attr('Sender name appear in customer email'); ?>" id="consumer_key_fields" name="customer_email_sender_name"  value="<?php echo esc_attr(get_option('laya_invc_customer_email_sender_name')); ?>">
      </div>
   <!-- Email submit when new invoice generated -->
      <h2><?php echo esc_html('New Invoice', 'layaway-invoice'); ?></h2>
      <div class="input-main">
      <label><?php echo esc_html('Subject', 'layaway-invoice'); ?></label>
       <input type="text"  placeholder="<?php echo esc_attr('Ex: Layaway Invoice'); ?>" id="consumer_key_fields" name="customer_new_incoice_mail_subj"  value="<?php echo esc_attr(get_option('laya_invc_customer_new_incoice_mail_subj')); ?>">
      </div>

       <?php
        // Add nonce field
        wp_nonce_field('admin_email_for_new_order_and_mail_text_nonce', 'admin_email_for_new_order_and_mail_text_nonce_field');
       ?>
        <div class="button-main">
        <input style="background-color:<?php echo esc_attr($base_color); ?>;border: 1px solid <?php echo esc_attr($base_color); ?>" type="submit" name="admin_email_for_new_order_and_mail_text" class="submit-btn-1" value="<?php echo esc_attr('Save'); ?>" >
        </div>

    </div>
    </form>
    <!-- End Email settings tab -->
    <!--/******************************************************************************************/-->

     <!-- Start Typography  and others tab -->
    <form method="post">
     <div id="typography" class="tabcontent" style="border: 1px solid <?php echo esc_attr($base_color); ?>">
      <h2><?php echo esc_html('Typography', 'layaway-invoice'); ?></h2>
      <div class="input-main">
        <label><?php echo esc_html('Base Color', 'layaway-invoice'); ?></label>
        <input type="text" placeholder="#4FB200" id="consumer_key_fields" name="base_color" value="<?php echo esc_attr(get_option('laya_invc_layaway_base_color')); ?>">
      </div>

        <div class="input-main">
        <label><?php echo esc_html('Privacy Policy page link', 'layaway-invoice'); ?></label>
        <input type="text" placeholder="/layaway-terms-and-conditions/" id="consumer_key_fields" name="layaway_privacy_policy_link" value="<?php echo esc_attr(get_option('laya_invc_layaway_privacy_policy_page_link')); ?>">
       </div>
        <?php
        // Add nonce field
        wp_nonce_field('plugin_typography_nonce', 'plugin_typography_nonce_field');
       ?>
        <div class="button-main">
        <input style="background-color:<?php echo esc_attr($base_color); ?>;border: 1px solid <?php echo esc_attr($base_color); ?>" type="submit" name="plugin_typography" class="submit-btn-1" value="<?php echo esc_attr('Save'); ?>" >
        </div>
    </div>

    </form>
    <!-- End Typography and others tab -->
<!--/******************************************************************************************/-->
         <!-- Start Partial payment tab -->
    <form method="post">
     <div id="partail_payment" class="tabcontent" style="border: 1px solid <?php echo esc_attr($base_color); ?>">
      <h2><?php echo esc_html('Partial Payment', 'layaway-invoice'); ?></h2>
        <div class="input-main">
         <label><?php echo esc_html('Enter partial payment in percentage', 'layaway-invoice'); ?></label>
         <input type="text" placeholder="70%" id="consumer_key_fields" name="val_partaily__payment" value="<?php echo esc_attr(get_option('laya_invc_partialy_payment_in_percentage')); ?>">
       </div>
             <h3>  <?php echo esc_html('Do you want enable partial payment?', 'layaway-invoice'); ?></h3>
             <?php
             if (get_option('laya_invc_layaway_choose_partial_payment') == 'enable_partial_payment') {

                 echo'<input type="radio" id="enable_partial_payment" name="layaway_choose_partial_payment" value="enable_partial_payment" checked>Yes</input><br><br>';
            }
            else
            {
                
                echo'<input type="radio" id="enable_partial_payment" name="layaway_choose_partial_payment" value="enable_partial_payment">Yes</input><br><br>';
            }

            //check disable partail payment 
             if (get_option('laya_invc_layaway_choose_partial_payment') == 'disable_partial_payment') {

                 echo'<input type="radio" id="disable_partial_payment" name="layaway_choose_partial_payment" value="disable_partial_payment"checked>No</input>';
            }
            else
            {
                
                echo'<input type="radio" id="disable_partial_payment" name="layaway_choose_partial_payment" value="disable_partial_payment">No</input>';
            }
          
          // Add nonce field
          wp_nonce_field('partaily__payment_nonce', 'partaily__payment_nonce_field');

            ?>
     
        <div class="button-main">
        <input style="background-color:<?php echo esc_attr($base_color); ?>;border: 1px solid <?php echo esc_attr($base_color); ?>" type="submit" name="partaily__payment" class="submit-btn-1" value="<?php echo esc_attr('Save'); ?>" >
        </div>

    </div>
    </form>
    <!-- End Partial payment tab -->
<!--/******************************************************************************************/-->
      <!-- Start Layaway invoice expiry date tab -->
    <form method="post">
     <div id="layaway_expired_days" class="tabcontent" style="border: 1px solid <?php echo esc_attr($base_color); ?>">
      <h2><?php echo esc_html('Layaway invoice expiry date', 'layaway-invoice'); ?></h2>
        <div class="input-main">
            <label><?php echo esc_html('Select expiry days', 'layaway-invoice'); ?></label>

                 <select name="invoice_expiry_days" id="consumer_key_fields">
                <?php if(get_option('laya_invc_layaway__invoice_expired_days')){echo'<option value="'.esc_attr(get_option('laya_invc_layaway__invoice_expired_days')).'">'.esc_attr(get_option('laya_invc_layaway__invoice_expired_days')).'</option>';} ?>
                    <option value="14 days">14 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="21 days">21 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="45 days">45 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="60 days">60 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="90 days">90 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="120 days">120 <?php echo esc_html('days', 'layaway-invoice');?></option>
                    <option value="365 days">365 <?php echo esc_html('days', 'layaway-invoice');?></option>
                 </select>

        </div>
         <?php
  
        // Add nonce field
        wp_nonce_field('layaway_invoice_expiry_days_nonce', 'layaway_invoice_expiry_days_nonce_field');
 
       ?>
        <div class="button-main">
        <input style="background-color:<?php echo esc_attr($base_color); ?>;border: 1px solid <?php echo esc_attr($base_color); ?>" type="submit" name="layaway_invoice_expiry_days" class="submit-btn-1" value="<?php echo esc_attr('Save'); ?>" >
        </div>
   
    </div>
    </form>
    <!-- End Layaway invoice expiry date tab -->
</section>

<?php 
}

 



