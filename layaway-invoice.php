<?php

/*
Plugin Name:       Layaway Invoice 
Plugin URI:        https://wplabworks.com/
Author:            muhammad_shahid_latif team
Author URI:        https://profiles.wordpress.org/alexbot24/
Text Domain:       layaway-invoice
Requires PHP:      7.0
Requires at least: 6.0
License: GPLv2 or later
Requires Plugins:  woocommerce
Version:           1.0.0
Description:       Layaway Plugin is used as a bill pay plugin. Simply gather your customers name and email, send them an invoice to pay directly within Woocoomerce>MyAccount via Authorize.net.
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/*******************files include **************************/
//Authorize.net payment related files
require_once 'vendor/autoload.php';
include('order_invoice_frontend.php');
include('order_invoice_settings.php');

function laya_invc_enqueue_admin() {
 
         wp_enqueue_style('order_invoice', plugins_url('/assets/order_invoice.css',__FILE__, false, time() ));
         wp_enqueue_script( 'order_invoice', plugins_url( '/assets/order_invoice.js', __FILE__ ), array('jquery'), time(), true);
}
add_action('admin_enqueue_scripts','laya_invc_enqueue_admin');

function laya_invc_enqueue_frontend() {

         wp_enqueue_style('order_invoice_f', plugins_url('/assets/order_invoice_f.css',__FILE__, false, time() ));

         //localize js and create ajax object 
         wp_enqueue_script( 'partialy_change_total', plugins_url( '/assets/partialy_change_total.js', __FILE__ ), array('jquery'), time(), true);

         wp_localize_script( 'partialy_change_total', 'frontend_ajax_object',
         array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' =>  wp_create_nonce('partialy_change_total_nonce'), 
          
        )
    );
         
}
add_action('wp_enqueue_scripts','laya_invc_enqueue_frontend');

/****************Create menu into dashboard***************/

    add_action( 'admin_menu', 'laya_invc_create_main_menu' ); 
    function laya_invc_create_main_menu(){
        if (current_user_can('manage_options')) {
         $page_title = esc_html__('Layaway Invoices', 'layaway-invoice');  
         $menu_title = 'Layaway Invoices'; 
         $capability = 'manage_options';  
         $menu_slug  = 'layaway-invoice.php';  
         $function   = 'order_invoice_main_func';
         $icon_url   = plugins_url( '/assets/img/invoice_dashi_icon.png', __FILE__ ); 
         $position   = 2;  
         add_menu_page( $page_title,  $menu_title,  $capability,  $menu_slug,  $function,   $icon_url,  $position );
      }
   }

/****************Add submenu into Layaway Invoices menu***************/

         add_action( 'admin_menu', 'laya_invc_order_invoice_sub_menu', 20 );
         function laya_invc_order_invoice_sub_menu(){
             if (current_user_can('manage_options')){
         add_submenu_page( 'layaway-invoice.php', 'settings', 'Settings', 'manage_options','order_invoice_settings.php','laya_invc_layaway_invoice_settings_sub_menu_func');

        //Remove order_invoice posttype page from dashbord 
         remove_menu_page('edit.php?post_type=laya_invc_order');
        }
    }
/****************Add custom post invoice order*****************/

function laya_invc_create_order_invoice_post_type() {

if (current_user_can('manage_options')) {
$args = array( 
    'post_type'    => 'laya_invc_order',
    'post_status'  => 'publish' 

);
$loop = new WP_Query( $args );


$existing_order_invoice_count = $loop->post_count;

            // set up labels name which is show on frontent
           $labels = array(
                'name'               => esc_html__('Layaway Invoices', 'layaway-invoice'),
                'singular_name'      => esc_html__('Layaway Invoices', 'layaway-invoice'),
                'add_new'            => esc_html__('Add New invoice', 'layaway-invoice'),
                'add_new_item'       => esc_html__('Add New invoice', 'layaway-invoice'),
                'edit_item'          => esc_html__('Edit invoice', 'layaway-invoice'),
                'new_item'           => esc_html__('New invoice', 'layaway-invoice'),
                'all_items'          => esc_html__('All invoices', 'layaway-invoice'),
                'view_item'          => esc_html__('View invoice', 'layaway-invoice'),
                'search_items'       => esc_html__('Search invoice', 'layaway-invoice'),
                'not_found'          => esc_html__('No order Found', 'layaway-invoice'),
                'not_found_in_trash' => esc_html__('No order found in Trash', 'layaway-invoice'), 
                'parent_item_colon'  => '',
                'menu_name'          => esc_html__('Layaway Invoices', 'layaway-invoice'),
            );
    
      if($existing_order_invoice_count < 10){

            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'has_archive'        => true,
                'show_ui'            => true,
                'show_in_menu'       => 'layaway-invoice.php',
                'capability_type'    => 'post',
                'hierarchical'       => true,
                'rewrite'            => array('slug' => 'laya_invc_order'),
                'query_var'          => true,
                'menu_icon'          => 'dashicons-editor-ul',
                'supports'           => array(
                    'title',
                    'custom-fields',
                    'revisions',
                    'author',
                    'page-attributes'
                )
            );
        }else{
                
            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'has_archive'        => true,
                'show_ui'            => true,
                'show_in_menu'       => 'layaway-invoice.php',
                'capability_type'    => 'post',
                'hierarchical'       => true,
                'rewrite'            => array('slug' => 'laya_invc_order'),
                'query_var'          => true,
                'menu_icon'          => 'dashicons-editor-ul',
                'capabilities'       => array( 'create_posts' => false ),
                'map_meta_cap' => true,
                'supports'           => array(
                    'title',
                    'custom-fields',
                    'revisions',
                    'author',
                    'page-attributes'
                )
            );

           //show invoice limit message only postype order invoice page
           if($_SERVER['QUERY_STRING']=='post_type=laya_invc_order')
           {
               
                // Show message when free version limit (10 invoices) completed.
                echo '<div id="message" class="error update_error"><p><strong>'.esc_html__('You have reached free version limit (10 invoices), Please ', 'layaway-invoice').'<a href="https://wplabworks.com">'.esc_html__('upgrade', 'layaway-invoice').'</a>'.esc_html__('if you want to create more invoices.', 'layaway-invoice').'</strong></p></div>';
           }

        }
            register_post_type( 'laya_invc_order', $args );
   }
}
add_action( 'init', 'laya_invc_create_order_invoice_post_type' );

/********************** Add metabox with replace acf fields *************************/
/******************Add meta box to select instructors post with product ************/

add_action( 'add_meta_boxes', 'laya_invc_add_order_invoice_meta_box' );
function laya_invc_add_order_invoice_meta_box(){

if (current_user_can('manage_options')) {
    add_meta_box(
    'layaway_invoice_fields_keys',
    esc_html__( 'Layaways invoice fields', 'layaway-invoice' ),
    'laya_invc_fields_func',
    'laya_invc_order',
    'normal',
    'default'
    );
   }
}

// Custom metabox content in admin product pages
function laya_invc_fields_func( $post ){
     
    //get layaway invoice saved fields data 
    $layaway_first_name = esc_html(sanitize_text_field(get_post_meta($post->ID, 'first_name', true)));
    $layaway_last_name = esc_html(sanitize_text_field(get_post_meta($post->ID, 'last_name', true)));
    $layaway_phone_number = esc_html(sanitize_text_field(get_post_meta($post->ID, 'phone_number', true)));
    $layaway_email = esc_html(sanitize_email(get_post_meta($post->ID, 'email', true)));
    $layaway_address = esc_html(sanitize_textarea_field(get_post_meta($post->ID, 'address', true)));
    $layaway_amount = esc_html(sanitize_text_field(get_post_meta($post->ID, 'amount', true)));
    $layaway_customer_payment = esc_html(sanitize_text_field(get_post_meta($post->ID, 'customer_payment', true)));
    $layaway_purpose_of_invoice = esc_html(sanitize_text_field(get_post_meta($post->ID, 'purpose_of_invoice', true)));
    $layaway_invoice_status =esc_html(sanitize_text_field(get_post_meta($post->ID, 'status', true)));?>


    <div class="layaway_invoice_fields">
        <label for="first_name"><?php  echo esc_html('First Name', 'layaway-invoice');?></label>
        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($layaway_first_name); ?>" required>
        
        <label for="last_name"><?php echo esc_html('Last Name', 'layaway-invoice');?></label>
        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($layaway_last_name); ?>">
        
        <label for="phone_number"><?php echo esc_html('Phone Number', 'layaway-invoice');?></label>
        <input type="tel" id="phone_number" name="phone_number" value="<?php echo esc_attr($layaway_phone_number); ?> ">
        
        <label for="email"><?php echo esc_html('Email', 'layaway-invoice');?></label>
        <input type="email" id="email" name="email" value="<?php echo esc_attr($layaway_email); ?>" required>
        
        <label for="address"><?php echo esc_html('Address', 'layaway-invoice');?></label>
        <textarea id="address" name="address"><?php echo esc_textarea($layaway_address); ?></textarea>
        
        <label for="amount"><?php echo esc_html('Amount', 'layaway-invoice');?></label>
        <input type="text" id="amount" name="amount" value="<?php echo esc_attr($layaway_amount); ?>" required>
        
        <label for="customer_payment"><?php echo esc_html('Customer Payment', 'layaway-invoice');?></label>
        <input type="text" id="customer_payment" name="customer_payment" value="<?php echo esc_attr($layaway_customer_payment); ?>">
        
        <label for="purpose_of_invoice"><?php  echo esc_html('Purpose of Invoice', 'layaway-invoice');?></label>
        <input type="text" id="purpose_of_invoice" name="purpose_of_invoice" value="<?php echo esc_attr($layaway_purpose_of_invoice); ?>">
    </div>


<p><?php esc_html__('Status', 'layaway-invoice');?></p>
<?php if (isset($layaway_invoice_status) and $layaway_invoice_status ==='active') { ?>
      <input type="radio" id="status" name="status" value="active" checked>
    <label for="html"><?php echo esc_html('Active', 'layaway-invoice');?></label>
    <input type="radio" id="status" name="status" value="completed">
    <label for="css"><?php echo esc_html('Completed', 'layaway-invoice');?></label>
</div>
<?php }elseif(isset($layaway_invoice_status) and $layaway_invoice_status ==='completed'){ ?>
      <input type="radio" id="status" name="status" value="active" >
    <label for="html"><?php echo esc_html('Active', 'layaway-invoice');?></label>
    <input type="radio" id="status" name="status" value="completed" checked>
    <label for="css"><?php echo esc_html('Completed', 'layaway-invoice');?></label>
</div>

<?php }else{ ?>
    <input type="radio" id="status" name="status" value="active">
    <label for="html"><?php echo esc_html('Active', 'layaway-invoice');?></label>
    <input type="radio" id="status" name="status" value="completed">
    <label for="css"><?php echo esc_html('Completed', 'layaway-invoice');?></label>
</div>
<?php }
 
}

add_action( 'save_post', 'laya_invc_fields__meta_box', 10, 1 );
function laya_invc_fields__meta_box( $post_id ) {

 if (current_user_can('manage_options')) {
  
    // Check if layaway invoice fields are set to be saved into the database.
    if (!empty(esc_html(sanitize_text_field($_POST['first_name']))) && !empty(esc_html(sanitize_email($_POST['email']))) && !empty(esc_html(sanitize_text_field($_POST['amount']))))
    {

        update_post_meta($post_id, 'first_name', esc_html(sanitize_text_field($_POST['first_name'])));
        update_post_meta($post_id, 'last_name', esc_html(sanitize_text_field($_POST['last_name'])));
        update_post_meta($post_id, 'phone_number', esc_html(sanitize_text_field($_POST['phone_number'])));
        update_post_meta($post_id, 'email', esc_html(sanitize_email($_POST['email'])));
        update_post_meta($post_id, 'address', esc_html(sanitize_textarea_field($_POST['address'])));
        update_post_meta($post_id, 'amount', esc_html(sanitize_text_field($_POST['amount'])));
        update_post_meta($post_id, 'customer_payment', esc_html(sanitize_text_field($_POST['customer_payment'])));
        update_post_meta($post_id, 'purpose_of_invoice', esc_html(sanitize_text_field($_POST['purpose_of_invoice'])));
        update_post_meta($post_id, 'status', esc_html(sanitize_text_field($_POST['status'])));
    }

  }  
}

/*********************Send email to user when new order invoice created*************/
function laya_invc_display_text_on_new_post($post_id, $post) {
    
 if (current_user_can('manage_options')) {

    // Only set for post_type = post!
    if ( 'laya_invc_order' == $post->post_type && $post->post_status == 'publish') {
        
        //get invoice expiration days from settings tab
         $invoice_expiration_days         = get_option('laya_invc_layaway__invoice_expired_days');

         // Saved invoice expiration date into post meta
        $invoice_created_date = get_the_date('Y-m-d',$post_id);
        
        $invoice_expiration_date= gmdate('Y-m-d', strtotime($invoice_created_date. ' + '.$invoice_expiration_days.''));
        update_post_meta( $post_id, '_invoice_expiration_date', $invoice_expiration_date);

        //send mail to customer after created new invoice 
        $sender_name                   = get_option('laya_invc_customer_email_sender_name');
        $customer_email                = get_post_meta($post_id,'email', true);
        $customer_new_incoice_mail_subj  = get_option('laya_invc_customer_new_incoice_mail_subj');
        $site_admin_email              =get_option( 'admin_email' );
        

        $subject = $customer_new_incoice_mail_subj;
        $header  = "FROM:".$sender_name."<".$site_admin_email.">\r\n";
        $header .= "MIME-Version: 1.0 \r\n";  
        $header .= "Content-type: text/html;charset=UTF-8 \r\n";  
        $message ='<p>'.esc_html__('To pay, after clicking the ','layaway-invoice').'<a href="'.site_url().'/my-account/layaway-invoice">'.esc_html__('“Pay Order”','layaway-invoice').'</a>'.esc_html__('  link, simply login with your ','layaway-invoice').'<a href="'.site_url().'">'.get_bloginfo( 'name' ).'</a>'.esc_html__(' account or create a new account with the e-mail address
you provided to us.','layaway-invoice').'</p>

·
</p>'.esc_html__('Login to your ','layaway-invoice').'<a href="'.site_url().'">'.get_bloginfo( 'name' ).'</a>'.esc_html__(' account at any time to make payments
and view the remaining amount due on the total invoice.','layaway-invoice').'</p>';
              
        // Send the email
        wp_mail($customer_email, $subject, $message, $header);
         return;      
    
    }
  }
}
add_action('wp_insert_post', 'laya_invc_display_text_on_new_post', 10, 2);


/*************************************************/
//unset author name and date from invoice column 

function laya_invc_unset_author_and_date($columns) {
 
 if (current_user_can('manage_options')) {

    unset($columns['author']);
    unset($columns['date']);
  }
    return $columns;
}
add_filter('manage_laya_invc_order_posts_columns', 'laya_invc_unset_author_and_date');

/*************************************Display content on order invoice page****************************/
add_action( 'woocommerce_account_layaway-invoice_endpoint', 'laya_invc_order_invoice_show_content' );
function  laya_invc_order_invoice_show_content()
{
 if (current_user_can('manage_options')) {

     //plugin base color
       $base_color         = get_option('laya_invc_layaway_base_color');

     //Display content on page
         $result='';
         $result .= '<table class="custom-table-main">
        <tr style="background-color:'.$base_color.'!important">
          <th>'.esc_html__('Title', 'layaway-invoice').'</th>
          <th>'.esc_html__('Name', 'layaway-invoice').'</th>
          <th>'.esc_html__('Email', 'layaway-invoice').'</th>
          <th>'.esc_html__('Amount', 'layaway-invoice').'</th>            
          <th>'.esc_html__('Payment history', 'layaway-invoice').'</th>  
          <th>'.esc_html__('Rem..Amount', 'layaway-invoice').'</th>         
          <th>'.esc_html__('Expiration date', 'layaway-invoice').'</th>
          <th>'.esc_html__('Phone Number', 'layaway-invoice').'</th>
          <th>'.esc_html__('Address', 'layaway-invoice').'</th>
          <th>'.esc_html__('Date', 'layaway-invoice').'</th>
          <th>'.esc_html__('Status', 'layaway-invoice').'</th>
          <th>'.esc_html__('Action', 'layaway-invoice').'</th>
        </tr>';
       
     //get order invoice post 
     $args =( array(
                    'post_type'=> 'laya_invc_order',
                    'posts_per_page' => '-1',      
        )
      );
                  
    $query = new WP_Query($args);

    if($query->have_posts()) :
       
        while($query->have_posts()) :
  
            $query->the_post() ;

            $order_title        = get_the_title();
            $author_name        = get_the_author();
            $order_created_date = get_the_date();
            $order_invoice_id   = get_the_ID();

            $order_status       = get_post_meta($order_invoice_id,'status', true);
            $customer_fname     = get_post_meta($order_invoice_id,'first_name', true);
            $customer_lname     = get_post_meta($order_invoice_id,'last_name', true);
            $customer_email     = get_post_meta($order_invoice_id,'email', true);
            $customer_amount    = get_post_meta($order_invoice_id,'amount', true);
            $address            = get_post_meta($order_invoice_id,'address', true);
            $phone_number       = get_post_meta($order_invoice_id,'phone_number', true);
            $purpose_of_invoice = get_post_meta($order_invoice_id,'purpose_of_invoice', true);


            //plugin base color
            $base_color         = get_option('laya_invc_layaway_base_color');

            if(strtolower(wp_get_current_user()->user_email)==strtolower($customer_email))
            {

            //customer remaining amount 
                $remaining_amount  = get_post_meta($order_invoice_id,'customer_payment', true);

                if ($order_status=='active') {
                   

                 //check invoice expiration date 
                $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);
             

                if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {
                    
                $result .= '<tr>
                        <td>' . esc_html($order_title) . '</td>
                        <td>' . esc_html($customer_fname . ' ' . $customer_lname) . '</td>
                        <td>' . esc_html($customer_email) . '</td>
                        <td>$' . esc_html($customer_amount) . '</td> 
                        <td></td> 
                        <td>$' . esc_html($customer_amount) . '</td> 
                        <td>' . esc_html($invoice_expired_date) . '</td>            
                        <td>' . esc_html($phone_number) . '</td>
                        <td>' . esc_html($address) . '</td>
                        <td>' . esc_html($order_created_date) . '</td>
                        <td>' . esc_html($order_status) . '</td>
                        <td>'.esc_html__('Expired','layaway-invoice').'</td>
                    </tr>';
                }
                else
                {
                         $result .= '<tr>
                  <td>' . esc_html($order_title) . '</td>
                  <td>' . esc_html($customer_fname . ' ' . $customer_lname) . '</td>
                  <td>' . esc_html($customer_email) . '</td>
                  <td>$' . esc_html($customer_amount) . '</td> 
                  <td></td> 
                  <td>$' . esc_html($customer_amount) . '</td> 
                  <td>' . esc_html($invoice_expired_date) . '</td>            
                  <td>' . esc_html($phone_number) . '</td>
                  <td>' . esc_html($address) . '</td>
                  <td>' . esc_html($order_created_date) . '</td>
                  <td>' . esc_html($order_status) . '</td>
                  <td>

                  <form method="post" action="'.site_url().'/my-account/layaway-invoice?order_invoice_number='.$order_invoice_id.'&amount='.$customer_amount.'&purpose_of_invoice='.$purpose_of_invoice.'"> 
                  <div class="button-main">
                    <input style="background-color:'.$base_color.'!important; border: 1px solid '.$base_color.'!important;" type="submit" name="payment_action" class="submit-btn-1" value="Pay">
                </div>
                </form>
                </td>
                </tr>';
                }
                 
                }
                elseif (empty($remaining_amount) && !($order_status=='active')) {
                       

                    //get customer last payment 
                  $last_trasaction_date_and_amount_in__json  = get_post_meta($order_invoice_id, '_new_transaction_date_and_amount', true);
                  $last_trasaction_date_and_amount_in__decode =json_decode($last_trasaction_date_and_amount_in__json,true);
                         
                      //check invoice expiration date 
                      $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);

                      //Incase of full amount

                      if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {
                          
                           $result .= '<tr>
                           <td>' . esc_html($order_title) . '</td>
                           <td>' . esc_html($customer_fname,$customer_lname) .'</td>
                           <td>' . esc_html($customer_email) . '</td>
                           <td>$' . esc_html($customer_amount) . '</td>
                           <td>';
                         foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             $result .='<div><span id="trasc_date_f">'.$trasaction_date_without_time. '</span>&#x279B;<span style="color:'.$base_color.'!important" id="trasc_amount_f">$' . $trasaction_amount. '</span></div>'; 
                            }                     
                           $result .='</td>
                           <td>' . esc_html($remaining_amount) . '</td>
                           <td>' . esc_html($invoice_expired_date) . '</td>
                           <td>' . esc_html($phone_number) . '</td>
                           <td>' . esc_html($address) . '</td>
                           <td>' . esc_html($order_created_date) . '</td>
                           <td>' . esc_html($order_status) . '</td>
                           <td>'.esc_html__('Expired','layaway-invoice').'</td>
                        </tr>';     

                       }else
                       {
                          
                           $result .= '<tr>
                            <td>' . esc_html($order_title) . '</td>
                            <td>' . esc_html($customer_fname,$customer_lname) . '</td>
                            <td>' . esc_html($customer_email) . '</td>
                            <td>$' . esc_html($customer_amount) . '</td>
                           <td>';
                         foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             $result .='<div><span id="trasc_date_f">'.$trasaction_date_without_time. '</span>&#x279B;<span style="color:'.$base_color.'!important" id="trasc_amount_f">$' . $trasaction_amount. '</span></div>'; 
                            }                     
                            $result .='</td>
                           <td>$0</td>
                           <td>' . esc_html($invoice_expired_date) . '</td>
                           <td>' . esc_html($phone_number) . '</td>
                           <td>' . esc_html($address) . '</td>
                           <td>' . esc_html($order_created_date) . '</td>
                           <td>' . esc_html($order_status) . '</td>
                           <td>'.esc_html__('Paid','layaway-invoice').'</td>
                           </tr>';  

                       } 
            
                       //Incase of partial amount
               
                 }elseif($remaining_amount<$customer_amount) {
                        
                   //get customer last payment 

                  $last_trasaction_date_and_amount_in__json  = get_post_meta($order_invoice_id, '_new_transaction_date_and_amount', true);
                  $last_trasaction_date_and_amount_in__decode =json_decode($last_trasaction_date_and_amount_in__json,true);
                         
                      //check invoice expiration date 
                      $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);
                      if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {

                          $result .= '<tr>
                            <td>' . esc_html($order_title) . '</td>
                            <td>' . esc_html($customer_fname,$customer_lname) . '</td>
                            <td>' . esc_html($customer_email) . '</td>
                            <td>$' . esc_html($customer_amount) . '</td>
                            <td>';
                         foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             $result .='<div><span id="trasc_date_f">'.$trasaction_date_without_time. '</span>&#x279B;<span style="color:'.$base_color.'!important" id="trasc_amount_f">$' . $trasaction_amount. '</span></div>'; 
                            }                     
                            $result .='</td>
                          <td>$' . esc_html($remaining_amount) . '</td>
                          <td>' . esc_html($invoice_expired_date) . '</td>
                          <td>' . esc_html($phone_number) . '</td>
                          <td>' . esc_html($address) . '</td>
                          <td>' . esc_html($order_created_date) . '</td>
                          <td>' . esc_html($order_status) . '</td>
                          <td>'.esc_html__('Expired','layaway-invoice').'</td>
                          </tr>'; 
                       }else
                       {    
                         $result .= '<tr>
                           <td>' . esc_html($order_title) . '</td>
                           <td>' . esc_html($customer_fname,$customer_lname) . '</td>
                           <td>' . esc_html($customer_email) . '</td>
                           <td>$'. esc_html($customer_amount) . '</td>
                           <td>';
                         foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             $result .='<div><span id="trasc_date_f">'.$trasaction_date_without_time. '</span>&#x279B;<span style="color:'.$base_color.'!important" id="trasc_amount_f">$' . $trasaction_amount. '</span></div>'; 
                            }                     
                           $result .='</td>
                          <td>$'. esc_html($remaining_amount) . '</td>
                          <td>' . esc_html($invoice_expired_date) . '</td>
                          <td>' . esc_html($phone_number) . '</td>
                          <td>' . esc_html($address) . '</td>
                          <td>' . esc_html($order_created_date) . '</td>
                          <td>' . esc_html($order_status) . '</td>
                           <td>
                           <form method="post" action="'.site_url().'/my-account/layaway-invoice?order_invoice_number='.$order_invoice_id.'&amount='.$customer_amount.'&purpose_of_invoice='.$purpose_of_invoice.'"> 
                          <div class="button-main">
                            <input style="background-color:'.$base_color.'!important; border: 1px solid '.$base_color.'!important;"  type="submit" name="payment_action" class="submit-btn-1" value="Pay">
                        </div>
                        </form>
                        </td>
                         
                        </tr>';   

                        
                       }   
                     
                    }            
           
                 }
        endwhile;

        wp_reset_postdata();
        $result.='</table>';
    endif; 
          
    //End invoice table       
/************************************************************************************/
//Add Authorized.net payment integration
 // Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(esc_html(sanitize_text_field($_POST['authorize_net_payment_submit']))) && wp_verify_nonce(esc_html(sanitize_text_field($_POST['authorize_net_payment_nonce_field'])), 'authorize_net_payment_nonce')) {



   //Billing informations
        $first_name      = esc_html(sanitize_text_field($_POST['first_name']));
        $last_name       = esc_html(sanitize_text_field($_POST['last_name']));
        $address         = esc_html(sanitize_text_field($_POST['address']));
        $phone_number    = esc_html(sanitize_text_field($_POST['phone_number']));
        $zip_code        = esc_html(sanitize_text_field($_POST['post_code']));
        $country         = esc_html(sanitize_text_field($_POST['country']));
        $email           = esc_html(sanitize_email($_POST['email']));
        $state           = esc_html(sanitize_text_field($_POST['state']));
        $city            = esc_html(sanitize_text_field($_POST['city']));
         //order details
        $customer_id     = esc_html(absint($_POST['customer_id']));
        $Purpose_of_invc = esc_html(sanitize_text_field($_POST['purpose_invoice']));
        //Invoice number
        $invoice_number  = esc_html(sanitize_text_field($_POST['order_invoice_number']));
        //payment  card information
        $card_number     = esc_html(sanitize_text_field($_POST['card_number']));
        $customer_enter_amount    = esc_html(sanitize_text_field($_POST['amount']));
        $expiration_month= esc_html(sanitize_text_field($_POST['expiration_month']));
        $expiration_year = esc_html(sanitize_text_field($_POST['expiration_year']));
        $cvv             = esc_html(sanitize_text_field($_POST['cvv']));
     

     
if (class_exists('\net\authorize\api\contract\v1\MerchantAuthenticationType')) {

    // Initialize the SDK with your API credentials
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();

    //set api login id and transaction key according to payment mode from settings
      if (!empty(esc_html(get_option('laya_invc_test_api_login_id'))) && esc_html(get_option('laya_invc_payment_mode')) == 'test') {
          
           $test__api_login_id    = esc_html(get_option('laya_invc_test_api_login_id'));
           $test__transaction_key = esc_html(get_option('laya_invc_test_transaction_key'));
           $merchantAuthentication->setName($test__api_login_id);
           $merchantAuthentication->setTransactionKey($test__transaction_key);

     }
     elseif (!empty(esc_html(get_option('laya_invc_live_api_login_id'))) && esc_html(get_option('laya_invc_payment_mode')) == 'live') {
            
           $live__api_login_id    = esc_html(get_option('laya_invc_live_api_login_id'));
           $live__transaction_key = esc_html(get_option('laya_invc_live_transaction_key'));
           $merchantAuthentication->setName($live__api_login_id);
           $merchantAuthentication->setTransactionKey($live__transaction_key);
     }

  }
    // Create a transaction request
if (class_exists('\net\authorize\api\contract\v1\TransactionRequestType')) {

    $transactionRequestType = new \net\authorize\api\contract\v1\TransactionRequestType();
    $transactionRequestType->setTransactionType('authCaptureTransaction');
    $transactionRequestType->setAmount($customer_enter_amount);
}

if (class_exists('\net\authorize\api\contract\v1\CreditCardType')) {

    $creditCard = new \net\authorize\api\contract\v1\CreditCardType();
    $creditCard->setCardNumber($card_number);
    $creditCard->setExpirationDate($expiration_month . $expiration_year);
    $creditCard->setCardCode($cvv);
}

if (class_exists('\net\authorize\api\contract\v1\NameAndAddressType')) {

    $shipTo  = new \net\authorize\api\contract\v1\NameAndAddressType();
    $shipTo->setFirstName($first_name);
    $shipTo->setLastName($last_name);
    $shipTo->setAddress($address);
    $shipTo->setCity($city);
    $shipTo->setState($state);
    $shipTo->setZip($zip_code);
    $shipTo->setCountry($country);
}

if (class_exists('\net\authorize\api\contract\v1\CustomerAddressType')) {

    $billTo = new \net\authorize\api\contract\v1\CustomerAddressType();
    $billTo->setFirstName($first_name);
    $billTo->setLastName($last_name);
    $billTo->setAddress($address);
    $billTo->setCity($city);
    $billTo->setState($state);
    $billTo->setZip($zip_code);
    $billTo->setCountry($country);
    $billTo->setPhoneNumber($phone_number);
}

if (class_exists('\net\authorize\api\contract\v1\OrderType')) {

    $order = new \net\authorize\api\contract\v1\OrderType();
    $order->setInvoiceNumber($invoice_number);
    $order->setDescription($Purpose_of_invc);
}

if (class_exists('\net\authorize\api\contract\v1\CustomerDataType')) {
    
    $customerData = new \net\authorize\api\contract\v1\CustomerDataType();
    $customerData->setEmail($email);
    $customerData->setId($customer_id);


    // Add the customer to the transaction request
    $transactionRequestType->setShipTo($shipTo);
    $transactionRequestType->setBillTo($billTo);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setCustomer($customerData);
}

if (class_exists('\net\authorize\api\contract\v1\PaymentType')) {

    $paymentOne = new \net\authorize\api\contract\v1\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    $transactionRequestType->setPayment($paymentOne);
}

if (class_exists('\net\authorize\api\contract\v1\CreateTransactionRequest')) {

    $request = new \net\authorize\api\contract\v1\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransactionRequest($transactionRequestType);
}
    // Send the transaction request to Authorize.Net
if (class_exists('\net\authorize\api\controller\CreateTransactionController')) {

    $controller = new \net\authorize\api\controller\CreateTransactionController($request);
    //set production mode and sandbox/test ,ode according to payment mode

      if (!empty(get_option('laya_invc_test_api_login_id')) && get_option('laya_invc_payment_mode') == 'test') {
          
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);


     }
     elseif (!empty(get_option('laya_invc_live_api_login_id')) && get_option('laya_invc_payment_mode') == 'live') {
            
           $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
     }
 
}
    // Process the response
    if ($response != null && $response->getMessages()->getResultCode() == 'Ok') {
      // Successful transaction

   echo '<div id="response"><h2 id="thankyou_mesg">'.esc_html__('Thankyou', 'layaway-invoice').'</h2><h4 id="successful">'.esc_html__('Transaction successful!', 'layaway-invoice').'</h4></div>';
      
     //update order invoice status
    update_post_meta( $invoice_number,'status','completed');
    
       
    //Add trasaction date and customer enter amount into database in json format and check valuse exists or not
        $current_date= gmdate('Y-m-d H:i:s');


        $transaction_date_and_amount=get_post_meta($invoice_number, '_new_transaction_date_and_amount', true);

        if (empty($transaction_date_and_amount)) {
  
            $transaction_date_and_amount_in_array =  array($current_date => $customer_enter_amount);
            $transaction_date_and_amount_in_json  =  wp_json_encode($transaction_date_and_amount_in_array);
 
            //add transaction id saved into meta in json format
            update_post_meta( $invoice_number, '_new_transaction_date_and_amount', $transaction_date_and_amount_in_json);

         } 
        else
         {
             $previous__transaction_date_and_amount_decode = json_decode($transaction_date_and_amount, true);
             

             // Adding a new date and amount like key-value  pair to the  $previous__transaction_date_and_amount_decode array
             $previous__transaction_date_and_amount_decode[$current_date] = $customer_enter_amount;

             $added_new_transaction_date_and_amount_in_json   = wp_json_encode($previous__transaction_date_and_amount_decode);

            //again  transaction id  saved into meta in json format
             update_post_meta( $invoice_number, '_new_transaction_date_and_amount', $added_new_transaction_date_and_amount_in_json);

         }


     //Add transaction id into postmeta
        $current_transaction_id=$response->gettransactionResponse()->gettransId();
       
        $get_previous__transaction_id=get_post_meta($invoice_number, '_transaction_id', true);

        if (empty($get_previous__transaction_id)) {

            $single_transaction_id_in_array =  array($current_transaction_id);
            $all_transaction_ids_in_json    =  wp_json_encode($single_transaction_id_in_array);
 
            //add transaction id saved into meta in json format
            update_post_meta( $invoice_number, '_transaction_id', $all_transaction_ids_in_json);

         } 
        else
         {
             $previous__transaction_id_decode = json_decode($get_previous__transaction_id);

             array_push($previous__transaction_id_decode,$current_transaction_id);
             
             $added_new_transaction_id_in__previous_ids   = wp_json_encode($previous__transaction_id_decode);

            //again  transaction id  saved into meta in json format
             update_post_meta( $invoice_number, '_transaction_id', $added_new_transaction_id_in__previous_ids);

         }

            
   //check amount
        $amount_in__acf    = get_post_meta($invoice_number,'amount', true);
       
        $customer_payment_check=get_post_meta($invoice_number,'customer_payment', true);

     if(empty($customer_payment_check))
     {   
       
             if ($customer_enter_amount<$amount_in__acf) {

              
             $remaining_amount_from_total=$amount_in__acf-$customer_enter_amount;
              //update amount
             update_post_meta( $invoice_number,'customer_payment',$remaining_amount_from_total);

         
         }
         elseif ($customer_enter_amount==$amount_in__acf) {
             
             
        //customer pay totall amount then show null in remaining amount fields
             $pay_full_remaining_amount__show_null='';
                update_post_meta( $invoice_number,'customer_payment',$pay_full_remaining_amount__show_null);
         }
     }

     elseif(!empty($customer_payment_check))
     {  
         
             if ($customer_enter_amount<$customer_payment_check) {
                    
                   
             $remaining_amount_from_total=$customer_payment_check-$customer_enter_amount;
              //update amount
             update_post_meta( $invoice_number,'customer_payment',$remaining_amount_from_total);

         
         }
         elseif ($customer_enter_amount==$customer_payment_check) {
              
             //customer pay totall amount then show null in remaining amount fields
             $pay_full_remaining_amount__show_null='';
              update_post_meta( $invoice_number,'customer_payment',$pay_full_remaining_amount__show_null);
         }
     }
  /**************************send  email to admin on new order*********************************/
      //get current date to set order date
        $today_date=gmdate('F j, Y');

      //get remaining amount through acf 
        if(empty(get_post_meta($invoice_number,'customer_payment', true))){
            $admin__email_remaining_amount   = '$0';
        }else{
            $admin__email_remaining_amount   = '$'.get_post_meta($invoice_number,'customer_payment', true);
        }
        

      //get admin email data from settings tab
        $admin__email                    = get_option('laya_invc_admin_new_order_email');
        $admin__email_subject            = get_option('laya_invc_admin_new_order_email_subject');
        $admin__email_heading            = get_option('laya_invc_admin_new_order_email_heading');
        $admin__email_additional_content = get_option('laya_invc_admin_new_order_email_additional_content');
        $admin__email_from_name          = get_option('laya_invc_customer_email_sender_name');
        $site_administrator_email        = get_option( 'admin_email' );


    
      //send email to admin when transaction successfully
       
        $subject = $admin__email_subject;
        $header  = "FROM: ".$admin__email_from_name." <".$site_administrator_email.">\r\n";
        $header .= "MIME-Version: 1.0 \r\n";  
        $header .= "Content-type: text/html;charset=UTF-8 \r\n";  
        $message = '<section class="content-table-main-02">
                     <div class="table-02-heading">
                         <h1>'.$admin__email_heading.': #'.$invoice_number.'</h1>
                     </div>
                        <div class="table-02-content">
                        <p>'.esc_html__('You have received an order from ', 'layaway-invoice'). ' '.$first_name.$last_name.' '.esc_html__('The order is as follows:', 'layaway-invoice').'</p>
                         <h2>'.esc_html__('Order #', 'layaway-invoice').' '.$invoice_number.' ('.$today_date.')</h2>
                  <table class="table-main">
                    <tr>
                      <th>'.esc_html__('Customer Name', 'layaway-invoice').'</th>
                      <td>' . esc_html($first_name . $last_name) . '</td>
                    </tr>
                    <tr>
                      <th>'.esc_html__('Email', 'layaway-invoice').'</th>
                       <td>' . esc_html($email) . '</td>
                    </tr>
                    <tr>
                      <th>'.esc_html__('Customer Id', 'layaway-invoice').'</th>
                      <td>' . esc_html($customer_id) . '</td>
                    </tr>
                     <tr>
                      <th>'.esc_html__('Order Invoice Id', 'layaway-invoice').'</th>
                      <td>' . esc_html($invoice_number) . '</td>
                    </tr>
                    <tr>
                      <th>'.esc_html__('Amount Paid', 'layaway-invoice').'</th>
                      <td>$' . esc_html($customer_enter_amount) . '</td>
                    </tr>
                     <tr>
                      <th>'.esc_html__('Remaining Amount', 'layaway-invoice').'</th>
                      <td>' . esc_html($admin__email_remaining_amount) . '</td>
                    </tr>
                    
                 </table>
                       <h3>' . esc_html($admin__email_additional_content) . '</h3>
                  </div>
               </section>';
              
        // Send the email
        wp_mail($admin__email, $subject, $message, $header);
        wp_mail($email, $subject, $message, $header);
       
                /************end****************/
  
} else {

      // Transaction failed

    $transactionResponse = $response->getTransactionResponse();
    echo '<div id="response"><h2 id="Ops">'.esc_html__('Declined!','layaway-invoice').'</h2>';

if ($transactionResponse !== null && $transactionResponse->getErrors()[0]->geterrorText()!==null) {

     $transaction_error_msg = $transactionResponse->getErrors()[0]->geterrorText();
     
     //print_r($transaction_error_msg);
     
    if ($transaction_error_msg) {

                if ($transaction_error_msg=='An error occurred during processing. Call Merchant Service Provider.') {

                    echo' <h4 id="failed">'.esc_html__('Please be sure your billing address matches the card you are paying with and try again','layaway-invoice').'</h4>';
                }else{
                   
                    echo' <h4 id="failed">'.esc_html__('Please be sure your billing address matches the card you are paying with and try again','layaway-invoice').'</h4>';

                }
  
     } 

   
  }elseif ($response->getMessages()->getmessage()[0]->gettext()!==null) {
       // print_r($response->getMessages());
    echo'<h4 id="failed">'.esc_html__('Please be sure your billing address matches the card you are paying with and try again.','layaway-invoice').'</h4>';

  }else{
      
    echo' <h4 id="failed">'.esc_html__('Transaction failed','layaway-invoice').'</h4>';

  }
  
 echo'</div>';
       
  }
}

//End isset submit form 
else
{
            /***********************************************/
                 //get current page url with parameter
               $url = isset($_SERVER['HTTPS']) &&
               $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
               $url .= sanitize_url($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);  
               $parts = wp_parse_url($url);
               
        
                    
               if(isset($parts['query']) and !empty($_GET['order_invoice_number'])){

                    $order_invoice_number   = sanitize_text_field(wp_unslash($_GET['order_invoice_number'])); //order_invoice_id
                    $purpose_of_inv         = sanitize_text_field(wp_unslash($_GET['purpose_of_invoice']));  
                    $amount                 = sanitize_text_field(wp_unslash($_GET['amount']));
                    $user_email             = wp_get_current_user()->user_email;
                    $user_id                = wp_get_current_user()->ID;

                     //customer remaining amount 
                    $customer_remaining_amount   = get_post_meta($order_invoice_number,'customer_payment', true);
                    
                    
                 /***********************************************/
                    //get billing address if customer set
                    $billing                = WC()->customer->get_billing(); 
                       
                       if($billing)
                       {

                        //billing details
                   $billing            = sanitize_text_field(WC()->customer->get_billing()); 
                   $billing_first_name = sanitize_text_field(WC()->customer->get_billing_first_name());
                   $billing_last_name  = sanitize_text_field(WC()->customer->get_billing_last_name());
                   $billing_address    = sanitize_text_field(WC()->customer->get_billing_address());
                   $billing_city       = sanitize_text_field(WC()->customer->get_billing_city());
                   $billing_state      = sanitize_text_field(WC()->customer->get_billing_state());
                   $billing_postcode   = sanitize_text_field(WC()->customer->get_billing_postcode());
                   $billing_country    = sanitize_text_field(WC()->customer->get_billing_country());
                   $billing_phone_no   = sanitize_text_field(WC()->customer->get_billing_phone());
                   $billing_email      = sanitize_email(WC()->customer->get_billing_email());


                    /***********************************************/
                        //Display checkout form to and add dynamic data 
                            
                            ?>
                        <form method="post" id="authorize_net_payment_form">

                          <div class="main_form_container">
                            <div id="billing_data">

                            
                            <div><?php echo esc_html('First Name:', 'layaway-invoice'); ?><input type="text" name="first_name" id="fname" 
                                value="<?php echo esc_attr($billing_first_name) ?>" required></div>

                            <div><?php echo esc_html('Last Name:', 'layaway-invoice'); ?><input type="text" name="last_name" id="lname" 
                                value="<?php echo esc_attr($billing_last_name) ?>" required></div>
                                
                            <div><?php echo esc_html('Email:', 'layaway-invoice'); ?><input type="email" name="email" id="email"
                                value="<?php echo esc_attr($billing_email) ?>"  required></div>
                               
                            <div><?php echo esc_html('Phone Number:', 'layaway-invoice'); ?><input type="number" name="phone_number" id="p_number" 
                                 value="<?php echo esc_attr($billing_phone_no) ?>" required></div> 

                            <div><?php echo esc_html('Address:', 'layaway-invoice'); ?><input type="text" name="address" id="add" 
                                 value="<?php echo esc_attr($billing_address) ?>" required></div>

                            <div><?php echo esc_html('Country:', 'layaway-invoice'); ?><input type="text" name="country" id="country" 
                                  value="<?php echo esc_attr($billing_country) ?>" required></div>
                                  
                            <div><?php echo esc_html('City:', 'layaway-invoice'); ?><input type="text" name="city" id="city" 
                                  value="<?php echo esc_attr($billing_city) ?>" required></div> 

                            <div><?php echo esc_html('State:', 'layaway-invoice'); ?><input type="text" name="state" id="state" 
                                  value="<?php echo esc_attr($billing_state) ?>" required></div>

                            <div><?php echo esc_html('Post code:', 'layaway-invoice'); ?><input type="number" name="post_code" id="p_code" 
                                  value="<?php echo esc_attr($billing_postcode) ?>" required></div>


                            <div><input type="hidden" name="customer_id" id="customer_id" 
                                 value="<?php echo esc_attr($user_id) ?>"></div>

                            <div><input type="hidden" name="purpose_invoice" id="purpose_invoice" 
                                 value="<?php echo esc_attr($purpose_of_inv) ?>"></div>

                            <div><input type="hidden" name="order_invoice_number" id="order_invoice_number" 
                                 value="<?php echo esc_attr($order_invoice_number) ?>"></div>

                          </div>
                          <div id="payment_data">
                                   
                           
                            <div><?php echo esc_html('Card Number:', 'layaway-invoice'); ?><input type="text" name="card_number" id="card_number" required></div>
                            <div><?php echo esc_html('Expiration Month:', 'layaway-invoice'); ?><input type="text" name="expiration_month" id="expiration_month" required></div>
                            <div><?php echo esc_html('Expiration Year:', 'layaway-invoice'); ?><input type="text" name="expiration_year" id="expiration_year" required></div>
                            <div><?php echo esc_html('Cvv:', 'layaway-invoice'); ?><input type="text" name="cvv" id="cvv" required></div>
                             <?php 
                             
                              if(empty($customer_remaining_amount))
                             {
                                  
                                   echo'<div>'.esc_html__('Total Amount($):', 'layaway-invoice').'<input type="text" name="amount" id="amount" value="'.esc_html($amount).'" required></div>';

                                   }

                                     elseif(!empty($customer_remaining_amount))
                                     {
                                              if($customer_remaining_amount<$amount)
                                        {

                                        echo'<div>'.esc_html__('Total Amount($):', 'layaway-invoice').'<input type="text" name="amount" id="amount" value="'.esc_html($customer_remaining_amount).'" required></div>';
                             
                                    }
                               }
        
                                // Add nonce field
                                wp_nonce_field('authorize_net_payment_nonce', 'authorize_net_payment_nonce_field');
                             
                             ?>
                           
                            
                            <input style="background-color:<?php echo esc_html($base_color) ?>!important; border: 1px solid <?php echo esc_html($base_color) ?> !important;" type="submit" name="authorize_net_payment_submit" value="Submit Payment">
           
                           </div>


                        </div>
                        
                    </form>
                       
                       <?php

                 
                    } else {
                      
                        ?>
                       <form method="post" id="authorize_net_payment_form">
                          <div class="main_form_container">
                              <div id="billing_data">

                            
                                <div><?php echo esc_html('First Name:', 'layaway-invoice'); ?><input type="text" name="first_name" id="fname" required></div>
                                <div><?php echo esc_html('Last Name:', 'layaway-invoice'); ?><input type="text" name="last_name" id="lname" required></div>
                                <div><?php echo esc_html('Email:', 'layaway-invoice'); ?><input type="email" name="email" id="email"  value="<?php esc_html($user_email) ?>"required></div>
                                <div><?php echo esc_html('Phone Number:', 'layaway-invoice'); ?><input type="number" name="phone_number" id="p_number" required></div> 
                                <div><?php echo esc_html('Country:', 'layaway-invoice'); ?><input type="text" name="country" id="country" required></div>
                                <div><?php echo esc_html('Address:', 'layaway-invoice'); ?><input type="text" name="address" id="add" required></div>
                                <div><?php echo esc_html('City:', 'layaway-invoice'); ?><input type="text" name="city" id="city" required></div> 
                                <div><?php echo esc_html('State:', 'layaway-invoice'); ?><input type="text" name="state" id="state" required></div>
                                <div><?php echo esc_html('Post code:', 'layaway-invoice'); ?><input type="number" name="post_code" id="p_code" required></div>

                                <div><input type="hidden" name="customer_id" id="customer_id" 
                                 value="<?php echo esc_attr($user_id) ?>"></div>

                                <div><input type="hidden" name="purpose_invoice" id="purpose_invoice" 
                                 value="<?php echo esc_attr($purpose_of_inv) ?>"></div>

                                <div><input type="hidden" name="order_invoice_number" id="order_invoice_number" 
                                 value="<?php echo esc_attr($order_invoice_number) ?>"></div>

                              </div>
                              <div id="payment_data">
                                       
                                
                                <div><?php echo esc_html('Card Number:', 'layaway-invoice'); ?><input type="text" name="card_number" id="card_number" required></div>
                                <div><?php echo esc_html('Expiration Month:', 'layaway-invoice'); ?><input type="text" name="expiration_month" id="expiration_month" required></div>
                                <div><?php echo esc_html('Expiration Year:', 'layaway-invoice'); ?><input type="text" name="expiration_year" id="expiration_year" required></div>
                                <div><?php echo esc_html('Cvv:', 'layaway-invoice'); ?><input type="text" name="cvv" id="cvv" required></div>
                                       <?php 
                             
                              if(!empty($customer_remaining_amount))
                             {
                                      
                                   if($customer_remaining_amount<$amount)
                                    {

                                        echo'<div>'.esc_html__('Total Amount($):', 'layaway-invoice').'<input type="text" name="amount" id="amount" value="'.esc_html($customer_remaining_amount).'" required></div>';
                             
                                    }
                                    else
                                    {
                                        echo'<div>'.esc_html__('Total Amount($):', 'layaway-invoice').'<input type="text" name="amount" id="amount" value="'.esc_html($amount).'" required></div>';
                            
                                    }
                             }
                               // Add nonce field
                                wp_nonce_field('authorize_net_payment_nonce', 'authorize_net_payment_nonce_field');
                             ?>

                                <input  style="background-color:<?php echo esc_html($base_color) ?>!important; border: 1px solid <?php echo esc_html($base_color) ?> !important;" type="submit" name="authorize_net_payment_submit" value="Submit Payment">
           
                              </div>

                          </div>
                        
                        </form>

                 <?php 
                    }         
                }
           
           else{
                 
              // echo  $result;
 //__________________Default show payment table on layaway invoice my account______________________//
             //Display content on page
        
        ?><table class="custom-table-main">
        <tr style="background-color:<?php echo esc_html($base_color);?>!important">
          <th><?php echo esc_html('Title', 'layaway-invoice'); ?></th>
          <th><?php  echo esc_html('Name', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Email', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Amount', 'layaway-invoice'); ?></th>            
          <th><?php echo esc_html('Payment history', 'layaway-invoice'); ?></th>  
          <th><?php echo esc_html('Rem..Amount', 'layaway-invoice'); ?></th>         
          <th><?php echo esc_html('Expiration date', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Phone Number', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Address', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Date', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Status', 'layaway-invoice'); ?></th>
          <th><?php echo esc_html('Action', 'layaway-invoice'); ?></th>
        </tr>
      <?php 
     //get order invoice post 
     $args =( array(
                    'post_type'=> 'laya_invc_order',
                    'posts_per_page' => '-1',      
        )
      );
                  
    $query = new WP_Query($args);

    if($query->have_posts()) :
       
        while($query->have_posts()) :
  
            $query->the_post() ;

            $order_title        = get_the_title();
            $author_name        = get_the_author();
            $order_created_date = get_the_date();
            $order_invoice_id   = get_the_ID();

            $order_status       = get_post_meta($order_invoice_id,'status', true);
            $customer_fname     = get_post_meta($order_invoice_id,'first_name', true);
            $customer_lname     = get_post_meta($order_invoice_id,'last_name', true);
            $customer_email     = get_post_meta($order_invoice_id,'email', true);
            $customer_amount    = get_post_meta($order_invoice_id,'amount', true);
            $address            = get_post_meta($order_invoice_id,'address', true);
            $phone_number       = get_post_meta($order_invoice_id,'phone_number', true);
            $purpose_of_invoice = get_post_meta($order_invoice_id,'purpose_of_invoice', true);


            //plugin base color
            $base_color         = get_option('laya_invc_layaway_base_color');

            if(strtolower(wp_get_current_user()->user_email)==strtolower($customer_email))
            {

            //customer remaining amount 
                $remaining_amount  = get_post_meta($order_invoice_id,'customer_payment', true);

                if ($order_status=='active') {
                   

                 //check invoice expiration date 
                $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);

                if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {
                    
                ?><tr>
                  <td><?php echo esc_html($order_title);?></td>
                  <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                  <td><?php echo esc_html($customer_email);?></td>
                  <td>$<?php echo esc_html($customer_amount);?></td> 
                  <td></td> 
                  <td>$<?php echo esc_html($customer_amount);?></td> 
                  <td><?php echo esc_html($invoice_expired_date);?></td>            
                  <td><?php echo esc_html($phone_number);?></td>
                  <td><?php echo esc_html($address);?></td>
                  <td><?php echo esc_html($order_created_date);?></td>
                  <td><?php echo esc_html($order_status);?></td>
                  <td><?php echo esc_html('Expired', 'layaway-invoice'); ?></td>
                </tr>
                <?php }
                else
                {
                ?><tr>
                  <td><?php echo esc_html($order_title);?></td>
                  <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                  <td><?php echo esc_html($customer_email);?></td>
                  <td>$<?php echo esc_html($customer_amount);?></td> 
                  <td></td> 
                  <td>$<?php echo esc_html($customer_amount);?></td>  
                  <td><?php echo esc_html($invoice_expired_date);?></td>             
                  <td><?php echo esc_html($phone_number);?></td>
                  <td><?php echo esc_html($address);?></td>
                  <td><?php echo esc_html($order_created_date);?></td>
                  <td><?php echo esc_html($order_status);?></td>
                  <td>

                  <form method="post" action="<?php echo esc_html(site_url());?>/my-account/layaway-invoice?order_invoice_number=<?php echo esc_html($order_invoice_id);?>&amount=<?php echo esc_html($customer_amount);?>&purpose_of_invoice=<?php echo esc_html($purpose_of_invoice);?>"> 
                  <div class="button-main">
                    <input style="background-color:<?php echo esc_html($base_color);?>!important; border: 1px solid <?php echo esc_html($order_status);?>!important;" type="submit" name="payment_action" class="submit-btn-1" value="Pay">
                </div>
                </form>
                </td>
                </tr>
                <?php }
                 
                }
                elseif (empty($remaining_amount) && !($order_status=='active')) {
                       

                    //get customer last payment 
                  $last_trasaction_date_and_amount_in__json  = get_post_meta($order_invoice_id, '_new_transaction_date_and_amount', true);
                  $last_trasaction_date_and_amount_in__decode =json_decode($last_trasaction_date_and_amount_in__json,true);
                         
                      //check invoice expiration date 
                      $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);

                      //Incase of full amount

                      if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {
                          
                        ?><tr>
                          <td><?php echo esc_html($order_title);?></td>
                          <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                          <td><?php echo esc_html($customer_email);?></td>
                          <td>$<?php echo esc_html($customer_amount);?></td>
                           <td>
                         <?php foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                            ?><div><span id="trasc_date_f"><?php echo esc_html($trasaction_date_without_time);?></span>&#x279B;<span style="color:<?php echo esc_html($base_color);?>!important" id="trasc_amount_f">$<?php echo esc_html($trasaction_amount);?></span></div> 
                            <?php } ?>                     
                           </td><td>$<?php echo esc_html($remaining_amount);?></td>
                          <td><?php echo esc_html($invoice_expired_date);?></td> 
                          <td><?php echo esc_html($phone_number);?></td>
                          <td><?php echo esc_html($address);?></td>
                          <td><?php echo esc_html($order_created_date);?></td>
                          <td><?php echo esc_html($order_status);?></td>
                           <td><?php echo esc_html('Expired', 'layaway-invoice'); ?></td>

                        </tr>   

                       <?php }else
                       {
                          
                         ?><tr>
                          <td><?php echo esc_html($order_title);?></td>
                          <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                          <td><?php echo esc_html($customer_email);?></td>
                          <td>$<?php echo esc_html($customer_amount);?></td>
                           <td>
                         <?php foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             ?><div><span id="trasc_date_f"><?php echo esc_html($trasaction_date_without_time);?></span>&#x279B;<span style="color:<?php echo esc_html($base_color);?>!important" id="trasc_amount_f">$<?php echo esc_html($trasaction_amount);?></span></div>
                            <?php } ?>                    
                           </td><td>$0</td>
                          <td><?php echo esc_html($invoice_expired_date);?></td> 
                          <td><?php echo esc_html($phone_number);?></td>
                          <td><?php echo esc_html($address);?></td>
                          <td><?php echo esc_html($order_created_date);?></td>
                          <td><?php echo esc_html($order_status);?></td>
                           <td><?php echo esc_html('Paid', 'layaway-invoice'); ?></td>

                        </tr>   

                       <?php } 
            
                       //Incase of partial amount
               
                 }elseif($remaining_amount<$customer_amount) {
                        
                   //get customer last payment 

                  $last_trasaction_date_and_amount_in__json  = get_post_meta($order_invoice_id, '_new_transaction_date_and_amount', true);
                  $last_trasaction_date_and_amount_in__decode =json_decode($last_trasaction_date_and_amount_in__json,true);
                         
                      //check invoice expiration date 
                      $invoice_expired_date= get_post_meta($order_invoice_id, '_invoice_expiration_date', true);
                      if (!empty($invoice_expired_date) and strtotime(gmdate("Y-m-d")) >= strtotime($invoice_expired_date)) {

                         ?><tr>
                          <td><?php echo esc_html($order_title);?></td>
                          <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                          <td><?php echo esc_html($customer_email);?></td>
                          <td>$<?php echo esc_html($customer_amount);?></td>
                           <td>
                        <?php  foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             ?><div><span id="trasc_date_f"><?php echo esc_html($trasaction_date_without_time);?></span>&#x279B;<span style="color:<?php echo esc_html($base_color);?>!important" id="trasc_amount_f">$<?php echo esc_html($trasaction_amount);?></span></div>
                            <?php } ?>                       
                          </td><td>$<?php echo esc_html($remaining_amount);?></td>
                          <td><?php echo esc_html($invoice_expired_date);?></td> 
                          <td><?php echo esc_html($phone_number);?></td>
                          <td><?php echo esc_html($address);?></td>
                          <td><?php echo esc_html($order_created_date);?></td>
                          <td><?php echo esc_html($order_status);?></td>
                           <td><?php echo esc_html('Expired', 'layaway-invoice'); ?></td>

                        </tr>  

                       <?php }else
                       {    
                         ?><tr>
                          <td><?php echo esc_html($order_title);?></td>
                          <td><?php echo esc_html($customer_fname);echo esc_html($customer_lname);?></td>
                          <td><?php echo esc_html($customer_email);?></td>
                          <td>$<?php echo esc_html($customer_amount);?></td>
                           <td>
                        <?php  foreach ($last_trasaction_date_and_amount_in__decode as $trasaction_date => $trasaction_amount) 
                           { 
                        $trasaction_date_with_time = strtotime($trasaction_date);
                        $trasaction_date_without_time    = gmdate('Y-m-d', $trasaction_date_with_time); 

                             ?><div><span id="trasc_date_f"><?php echo esc_html($trasaction_date_without_time);?></span>&#x279B;<span style="color:<?php echo esc_html($base_color);?>!important" id="trasc_amount_f">$<?php echo esc_html($trasaction_amount);?></span></div>
                            <?php } ?>                    
                                                   
                          </td><td>$<?php echo esc_html($remaining_amount);?></td>
                          <td><?php echo esc_html($invoice_expired_date);?></td> 
                          <td><?php echo esc_html($phone_number);?></td>
                          <td><?php echo esc_html($address);?></td>
                          <td><?php echo esc_html($order_created_date);?></td>
                          <td><?php echo esc_html($order_status);?></td>
                           <td>
                           <form method="post" action="<?php echo esc_html(site_url());?>/my-account/layaway-invoice?order_invoice_number=<?php echo esc_html($order_invoice_id);?>&amount=<?php echo esc_html($customer_amount);?>&purpose_of_invoice=<?php echo esc_html($purpose_of_invoice);?>"> 
                          <div class="button-main">
                            <input style="background-color:<?php echo esc_html($base_color);?>!important; border: 1px solid <?php echo esc_html($base_color);?>!important;"  type="submit" name="payment_action" class="submit-btn-1" value="Pay">
                        </div>
                        </form>
                        </td>
                         
                        </tr>   

                       <?php 
                       }   
                     
                    }            
           
                 }
        endwhile;

        wp_reset_postdata();
        ?></table><?php
    endif; 
          
    //End invoice table  

        }
    
    }
  }
}
//___________________________________________________________________________________________//
     /******Add partial payment and create invoice on select partially payment on checkout******/

add_action('woocommerce_review_order_before_payment', 'laya_invc_display_partial_payment_option');


function laya_invc_display_partial_payment_option(){
         
 if (current_user_can('manage_options')) {

     if (get_option('laya_invc_layaway_choose_partial_payment') == 'enable_partial_payment') {
         //get partail payment which is set in settings
        $partial_payment_in_percentage    =  get_option('laya_invc_partialy_payment_in_percentage');

        //get layaway invoice expiry dates which is set  in settings
        $layaway_invoice_expiry_days      =  get_option('laya_invc_layaway__invoice_expired_days');

        //get layaway privacy policy page link which is set in settings 
        $get_layaway_privacy_policy_page_link = get_option('laya_invc_layaway_privacy_policy_page_link');
          echo'<label>
        <input type="checkbox" name="partial_payment" value="partial_payment" id="partial_payment">'.esc_html__('Layaway (You pay ', 'layaway-invoice').' '.esc_html($partial_payment_in_percentage).' '.esc_html__('now and remaining balance due within ', 'layaway-invoice').''.esc_html($layaway_invoice_expiry_days).')
    </label><p><a href="'.esc_html($get_layaway_privacy_policy_page_link).'">'.esc_html__('layaway terms', 'layaway-invoice').'</a></p>';

   }
  }
}

     /*******************set session on checked**********************************/

add_action('wp_ajax_laya_invc_set_partialy_payment_on_checked', 'laya_invc_set_partialy_payment_on_checked');
add_action('wp_ajax_nopriv_laya_invc_set_partialy_payment_on_checked', 'laya_invc_set_partialy_payment_on_checked');

function laya_invc_set_partialy_payment_on_checked() {

     if (current_user_can('manage_options')) {
    // Check the nonce
    if(!wp_verify_nonce(esc_html(sanitize_text_field($_POST['wp_chechk_nonce_set_partialy_payment_on_checked'])), 'partialy_change_total_nonce' ))
    {
              echo esc_html__('Security Threat','layaway-invoice');
    }else{
     
         if (!empty(esc_html(sanitize_text_field($_POST['partially_payment_set'])))) {

        // Set Session value
        WC()->session->set('layaway_partially_payment_session', 'partialy');
    }
     
    }
}
die();
}

 /*******************set session at un-checked**********************************/
// Handle 'set_partialy_payment_on_un_checked' AJAX request
add_action('wp_ajax_laya_invc_set_partialy_payment_on_un_checked', 'laya_invc_set_partialy_payment_on_un_checked');
add_action('wp_ajax_nopriv_laya_invc_set_partialy_payment_on_un_checked', 'laya_invc_set_partialy_payment_on_un_checked');

function laya_invc_set_partialy_payment_on_un_checked() {

if (current_user_can('manage_options')) {    
 if(!wp_verify_nonce( esc_html(sanitize_text_field($_POST['wp_chechk_nonce_set_partialy_payment_on_un_checked'])), 'partialy_change_total_nonce' ))
     {
             echo esc_html__('Security Threat','layaway-invoice');
    }else{

    if (!empty(esc_html(sanitize_text_field($_POST['partially_payment_set'])))) {
        // Unset session values
        WC()->session->__unset('layaway_partially_payment_session');
        WC()->session->__unset('woo_payment_total');

    }
  }
}  
  die();
}
   /*****************************************************/
// Allow plugins to filter the grand total, and sum the cart totals in case of modifications.
function laya_invc_filter_woocommerce_calculated_total( $total, $cart ) { 
             // Get Session value.
if (current_user_can('manage_options')) {
  if(WC()->session->get( 'layaway_partially_payment_session' ))
  {

    WC()->session->set('woo_payment_total', $total);

    //get partail payment which is set in settings
    $set_partial_payment_from_db=  get_option('laya_invc_partialy_payment_in_percentage');

$partial_payment_in_simple = (float)str_replace('%', '', $set_partial_payment_from_db);
$percentage = $partial_payment_in_simple / 100;
    
    return $total * $percentage;

    }else{
    
        return $total;
    }
  }
}
add_filter( 'woocommerce_calculated_total', 'laya_invc_filter_woocommerce_calculated_total', 10, 2 );

//_____________________________________________________________________________________________/
         /*********create invoice on first order if partial payment select**********/
add_action('woocommerce_order_status_completed', 'laya_invc_create_layaway_order_invoice');
add_action('woocommerce_order_status_processing', 'laya_invc_create_layaway_order_invoice');
function laya_invc_create_layaway_order_invoice( $order_id ) {
 
 if (current_user_can('manage_options')) {  
//check layaway payment enable and incoice is already created or not 
    $check_layaway_payment        = get_post_meta( $order_id, '_layaway_partial_payment', true );
    $check_layway_invoice_created = get_post_meta( $order_id, '_check_layaway_invoice_on_partail_payment', true );
if(empty($check_layway_invoice_created))
{
     
    //check layaway payment enable
    if($check_layaway_payment == 'yes')
    {  

          //get total from custom session
        $woo_payment_total=   WC()->session->get( 'woo_payment_total' );

    $order = new WC_Order( $order_id );

   
    $order->get_subtotal();
    $get_partail_payment_which_is_set_in_settings=  get_option('laya_invc_partialy_payment_in_percentage');
    $partial_payment_without_percentage_sign = (float)str_replace('%','', $get_partail_payment_which_is_set_in_settings);
       
    $partial_payment_in_percentage = $partial_payment_without_percentage_sign / 100;
    $subract_amount_from_subtotal =$woo_payment_total * $partial_payment_in_percentage;
    $layaway_invoice_amount=$woo_payment_total - $subract_amount_from_subtotal;

    //   // Create post object
    $user_id = get_current_user_id();
    $my_post = array(
      'post_title'    => 'Woo Order ID#'.$order_id,
      'post_content'  => 'Partial payment invoice',
      'post_status'   => 'publish',
      'post_type'     => 'laya_invc_order',
      'post_author'   => $user_id,
    );

    $layaway_invoice_id=wp_insert_post( $my_post );


    update_post_meta( $layaway_invoice_id,'first_name',$order->get_billing_first_name());
    update_post_meta( $layaway_invoice_id,'last_name',$order->get_billing_last_name());
    update_post_meta( $layaway_invoice_id,'phone_number',$order->get_billing_phone());
    update_post_meta( $layaway_invoice_id,'email',$order->get_billing_email());
    update_post_meta( $layaway_invoice_id,'address',$order->get_billing_address_1());
    update_post_meta( $layaway_invoice_id,'amount',$layaway_invoice_amount);
    update_post_meta( $layaway_invoice_id,'purpose_of_invoice','remianing amount from partialy payment');
    update_post_meta( $layaway_invoice_id,'status','active');


    //layaway invoice expiration days from layaway settings tab
      $invoice_expiration_days         = get_option('laya_invc_layaway__invoice_expired_days');
       // Saved invoice expiration date into post meta
    $invoice_created_date = get_the_date('Y-m-d',$layaway_invoice_id);
            
    $invoice_expiration_date= gmdate('Y-m-d', strtotime($invoice_created_date. ' + '.$invoice_expiration_days.''));
    update_post_meta( $layaway_invoice_id, '_invoice_expiration_date', $invoice_expiration_date);

    //create invoice just once whener status change processing or completed
    update_post_meta( $order_id, '_check_layaway_invoice_on_partail_payment', 'created');


      }
    }
  } 
}



//________________________________________________________________________________________________//
        //*****************show layaway payment on new order********************//
add_action( 'woocommerce_email_order_meta', 'laya_invc_show_layaway_payment', 10, 3 );

function laya_invc_show_layaway_payment( $order, $sent_to_admin, $plain_text ){

if (current_user_can('manage_options')) {
  if (!$sent_to_admin) {
    //get currency symbol
  $currency_symbol = get_woocommerce_currency_symbol();
  $order_id  =   $order->get_order_number();
  $check_layaway_payment = get_post_meta( $order_id, '_layaway_partial_payment', true );
           //check layaway payment checked and session set
    if($check_layaway_payment == 'yes') {   
          
          //get layaway partialy payment percentage wich is set in layaway settings tab
          $get_partail_payment_which_is_set_in_settings=  get_option('laya_invc_partialy_payment_in_percentage');
          $partial_payment_without_percentage_sign = (float)str_replace('%','', $get_partail_payment_which_is_set_in_settings);


          $woo_payment_total=   WC()->session->get( 'woo_payment_total' );
          $partial_payment_in_percentage = $partial_payment_without_percentage_sign / 100;
          $subract_amount_from_subtotal = $woo_payment_total * $partial_payment_in_percentage;
          $layaway_invoice_amount= $woo_payment_total - $subract_amount_from_subtotal;


          echo'<h2 id="order_email_layaway_payment_title">'.esc_html__('Layaway payment','layaway-invoice').'</h2>
          <p>'.esc_html__('Layaway (You have to pay  remaining ','layaway-invoice').esc_html($currency_symbol).esc_html($layaway_invoice_amount).esc_html__(' amount)','layaway-invoice').'</p>
          <h4 class="order_email_remaining_amount_title">'.esc_html__('How to pay remaining layaway amount?','layaway-invoice').'</h4>
          <p id="order_email_layaway_user_redirect_to_pay_remaining_amount">'.esc_html__('To pay, after clicking the ','layaway-invoice').'<a href="'.esc_html(site_url()).'/my-account/layaway-invoice">'.esc_html__('?Pay Order?','layaway-invoice').'</a>'.esc_html__('  link, simply login with your ','layaway-invoice').'<a href="'.esc_html(site_url()).'">'.esc_html(get_bloginfo( 'name' )).'</a>'.esc_html__(' account or create a new account with the e-mail address
you provided to us.','layaway-invoice').'</p>';
       }
    }
  }
}
//________________________________________________________________________________________________//
    //*****************************Add  layaway payment into new order meta**********************//
add_action( 'woocommerce_checkout_order_created', 'laya_invc_add_layaway_payment');

function laya_invc_add_layaway_payment( $order){

 if (current_user_can('manage_options')) {
  $order_id  =   $order->get_order_number();
if (get_option('laya_invc_layaway_choose_partial_payment') == 'enable_partial_payment' and WC()->session->get( 'layaway_partially_payment_session' )) {   

    update_post_meta($order_id, '_layaway_partial_payment', 'yes');

    WC()->session->__unset( 'layaway_partially_payment_session' );


 }else{

    update_post_meta($order_id, '_layaway_partial_payment', 'no');

    }
  }
}
//________________________________________________________________________________________________//
      //*********************Change payment method name in new order email*****************//
add_filter( 'woocommerce_get_order_item_totals', 'laya_invc_change_payment_method_name_in_emails_on_layaway_payment', 10, 3 );
function laya_invc_change_payment_method_name_in_emails_on_layaway_payment( $total_rows, $order, $tax_display ){
 
if (current_user_can('manage_options')) { 
   $order_id  =   $order->get_order_number();
  //check layaway payment
  $check_layaway_payment = get_post_meta( $order_id, '_layaway_partial_payment', true );
           //check layaway payment checked  on checkout page
    if($check_layaway_payment == 'yes') {   

            $total_rows['payment_method']['value'] = 'Layaway Payment';
      }
    return $total_rows;
  }
}

//_____________________________________________________________________________________________________//
  //************************************** Change custom post type order listing *******************************//

add_action('pre_get_posts', 'laya_invc_set_order_invoice_posts_order');
function laya_invc_set_order_invoice_posts_order($query) {

 if (current_user_can('manage_options')) {
    // just change layaways invoice post table order 'laya_invc_order' post type
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'laya_invc_order') {
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
    }
  }
}