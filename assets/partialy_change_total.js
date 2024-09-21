jQuery(function($){
    $(document).ready(function(){
        $(document).on('change', 'input[name="partial_payment"]', function(e){
            var isChecked = $(this).prop('checked'); // Check if the checkbox is checked
            if (isChecked) {

                var partially_val = $(this).val();
                console.log(frontend_ajax_object.ajaxurl);
                $.ajax({
       
                    url: frontend_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'laya_invc_set_partialy_payment_on_checked',
                        partially_payment_set: partially_val,
                        wp_chechk_nonce_set_partialy_payment_on_checked:  frontend_ajax_object.nonce,
                    },
                    success: function(results) {
                       // alert(results);
                          $("#partialy_check").val(results);
                          $( document.body ).trigger( 'update_checkout' );

                    }
                });
            }else {

            $.ajax({
                    url: frontend_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'laya_invc_set_partialy_payment_on_un_checked',
                        partially_payment_set: 2,
                        wp_chechk_nonce_set_partialy_payment_on_un_checked:  frontend_ajax_object.nonce,
                    },
                    success: function(results) {
                        //alert(results);
                          $("#partialy_check").val(results);
                          $( document.body ).trigger( 'update_checkout' );

                    }
                }); 
        
    }
        });
    });
});
