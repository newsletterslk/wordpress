<tr valign="top">
    <th scrope="row"><?php _e( 'Default Country', WebsmsConstants::TEXT_DOMAIN ) ?></th>
    <td>
        <?php echo self::get_country_code_dropdown(); ?>
        <span class="tooltip" data-title="Default Country for mobile number format validation"><span class="dashicons dashicons-info"></span></span>	
        <input type="hidden" name="Websms_general[sa_mobile_pattern]" id="sa_mobile_pattern" value="<?php echo $sa_mobile_pattern;?>"/>
    </td>
</tr>
<tr valign="top" class="top-border">
    <th scrope="row"><?php _e( 'Alerts', WebsmsConstants::TEXT_DOMAIN ) ?> </th>
    <td>						
        <input type="text" name="Websms_general[alert_email]" class="admin_email" id="Websms_general[alert_email]" value="<?php echo $alert_email; ?>"> <?php _e( 'send alerts to this email id', WebsmsConstants::TEXT_DOMAIN ) ?>
        <span class="tooltip" data-title="Send Alerts for low balance & daily balance etc."><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<tr valign="top">
    <th scrope="row"></th>
    <td>
        <input type="checkbox" name="Websms_general[low_bal_alert]" id="Websms_general[low_bal_alert]" class="Websms_box notify_box" <?php echo (($low_bal_alert=='on')?"checked='checked'":'');?> onchange="toggleReadonly(this, 'input[type=number]')" /> <?php _e( 'Low Balance Alert', WebsmsConstants::TEXT_DOMAIN ) ?> 
        <input type="number" min="500" name="Websms_general[low_bal_val]" id="Websms_general[low_bal_val]" value="<?php echo $low_bal_val; ?>" >
        <span class="tooltip" data-title="Set Low Balance Alert"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<tr valign="top">
    <th scrope="row"></th>
    <td>
        <input type="checkbox" name="Websms_general[daily_bal_alert]" id="Websms_general[daily_bal_alert]" class="notify_box" <?php echo (($daily_bal_alert=='on')?"checked='checked'":''); ?>/>
        <?php _e( 'Daily Balance Alert', WebsmsConstants::TEXT_DOMAIN ) ?>
        <span class="tooltip" data-title="Set Daily Balance Alert"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>

<!--enable shorturl-->
<tr valign="top">
    <th scrope="row"></th>
    <td>
        <input type="checkbox" name="Websms_general[enable_short_url]" id="Websms_general[enable_short_url]" class="notify_box" <?php echo (($enable_short_url=='on')?"checked='checked'":''); ?> /><?php _e( 'Enable Short Url', WebsmsConstants::TEXT_DOMAIN ) ?>
        <span class="tooltip" data-title="Enable Short Url"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<!--/-enable shorturl-->
<?php
if(is_plugin_active('woocommerce/woocommerce.php')){ ?>
    <tr valign="top">
        <th scrope="row"></th>
        <td>
            <input type="checkbox" name="Websms_general[auto_sync]" id="Websms_general[auto_sync]" class="Websms_box sync_group" <?php echo (($auto_sync=='on')?"checked='checked'":''); ?> onchange="toggleDisabled(this)"/> <?php _e( 'Sync To Group', WebsmsConstants::TEXT_DOMAIN ) ?>
            <?php 
                $groups = json_decode(WebsmscURLOTP::group_list(),true);
            ?>
            <select name="Websms_general[group_auto_sync]" id="group_auto_sync">

                <?php 
                if(!is_array($groups['description']) || array_key_exists('desc', $groups['description'])){ ?>
                        <option value="0">SELECT</option>
                    <?php
                }
                else{
                    foreach($groups['description'] as $group){ ?>
                        <option value="<?php echo $group['Group']['name']; ?>" <?php echo (trim($group_auto_sync) == $group['Group']['name']) ? 'selected="selected"' : ''; ?>><?php echo $group['Group']['name']; ?></option>
                    <?php						
                    }
                }
                ?>

            </select>
            <?php
                if((!is_array($groups['description']) || array_key_exists('desc', $groups['description'])) && $islogged==true){
                    ?>
                        <a href="javascript:void(0)" onclick="create_group(this);" id="create_group" style="text-decoration: none;">Create Group </a>
                    <?php		
                }
            ?>
            <span class="tooltip" data-title="Sync users to a Group in Newsletters.lk"><span class="dashicons dashicons-info"></span></span>						  
        </td>
    </tr>
    <?php
        }
    ?>
<?php
if($hasWoocommerce){?>
<?php
/* 
<tr valign="top">
    <th scrope="row"><input type="checkbox" name="Websms_general[otp_for_selected_gateways]" id="Websms_general[otp_for_selected_gateways]" class="notify_box" <?php echo (($otp_for_selected_gateways=='on')?"checked='checked'":'')?>  onclick="toggleDisabled(this)"/><?php  _e( 'Enable OTP for Selected Payment Gateways', WebsmsConstants::TEXT_DOMAIN ) ?>
    <?php ?>
    <span class="tooltip" data-title="Please select payment gateway for which you wish to enable OTP Verification"><span class="dashicons dashicons-info"></span></span>
    </th>
    <td>
    <?php
    if($hasWoocommerce){
        self::get_wc_payment_dropdown($checkout_payment_plans); 
    }
    ?>
    </td>
</tr>*/?>
<?php } ?>

<?php if($hasWoocommerce || $hasWPAM){ ?>	
<tr valign="top">
    <th scrope="row"><?php _e( 'Send Admin SMS To', WebsmsConstants::TEXT_DOMAIN ) ?>
        <span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
    </th>
    <td>
        <select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
            <option value="">Custom</option>
            <option value="post_author" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'selected="selected"' : ''; ?>>Post Author</option>
        </select>
    <script>
    function toggle_send_admin_alert(obj){
        var value = jQuery(obj).val();
            jQuery('.admin_no').val(value);
        if(value == 'post_author')
            jQuery('.admin_no').attr('readonly', 'readonly');
        else
            jQuery('.admin_no').removeAttr('readonly');
    }
    </script>
    <input type="text" name="Websms_message[sms_admin_phone]" class="admin_no" id="Websms_message[sms_admin_phone]" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'readonly="readonly"' : ''; ?> value="<?php echo $sms_admin_phone; ?>">
    <br /><span><?php _e( 'Admin order sms notifications will be send in this number.', WebsmsConstants::TEXT_DOMAIN ); ?></span>
    </td>
</tr>
<?php } ?>
<?php
if($hasWoocommerce){ ?>
<tr valign="top" class="top-border">
    <th scrope="row"><?php _e( 'OTP Settings', WebsmsConstants::TEXT_DOMAIN ); ?>
    </th>
    <td>
    <input type="checkbox" name="Websms_general[checkout_otp_popup]" id="Websms_general[checkout_otp_popup]" class="notify_box" <?php echo (($checkout_otp_popup=='on')?"checked='checked'":'')?>/><?php _e( 'Verify OTP in Popup', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Verify OTP in Popup"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>

<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <input type="checkbox" name="Websms_general[register_otp_popup_enabled]" id="Websms_general[register_otp_popup_enabled]" class="notify_box" <?php echo (($register_otp_popup_enabled=='on')?"checked='checked'":'')?>/><?php _e( 'Register OTP in Popup', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Register OTP in Popup"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<tr valign="top">
    <th scrope="row">
    
    <?php _e( 'Exclude Role from LOGIN OTP', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Exclude Role from LOGIN OTP"><span class="dashicons dashicons-info"></span></span>
    </th>
    <td>
    <?php echo self::get_wc_roles_dropdown($admin_bypass_otp_login);?>
    
    
    </td>
</tr>

<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <input type="checkbox" name="Websms_general[checkout_show_otp_button]" id="Websms_general[checkout_show_otp_button]" class="notify_box" <?php echo (($checkout_show_otp_button=='on')?"checked='checked'":'')?>/><?php _e( 'Show Verify Button at Checkout', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Show verify button in-place of link at checkout"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <input type="checkbox" name="Websms_general[checkout_show_otp_guest_only]" id="Websms_general[checkout_show_otp_guest_only]" class="notify_box" <?php echo (($checkout_show_otp_guest_only=='on')?"checked='checked'":'')?>/><?php _e( 'Verify only Guest Checkout', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="OTP verification only for guest checkout"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>

<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <?php _e( 'OTP Re-send Timer', WebsmsConstants::TEXT_DOMAIN ) ?>
    <input type="number" name="Websms_general[otp_resend_timer]" id="Websms_general[otp_resend_timer]" class="notify_box" value="<?php echo $otp_resend_timer;?>" min="15" max="300"/> Sec
    <span class="tooltip" data-title="Set OTP Re-send Timer"><span class="dashicons dashicons-info"></span></span>	
    </td>
</tr>

<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <?php _e( 'Max OTP Re-send Allowed', WebsmsConstants::TEXT_DOMAIN ) ?>
    <input type="number" name="Websms_general[max_otp_resend_allowed]" id="Websms_general[max_otp_resend_allowed]" class="notify_box" value="<?php echo $max_otp_resend_allowed;?>" min="1" max="10"/> Times
    <span class="tooltip" data-title="Set MAX OTP Re-send Allowed"><span class="dashicons dashicons-info"></span></span>	
    </td>
</tr>


<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <?php _e( 'OTP Verify Button Text', WebsmsConstants::TEXT_DOMAIN ) ?>
    <input type="text" name="Websms_general[otp_verify_btn_text]" id="Websms_general[otp_verify_btn_text]" class="notify_box" value="<?php echo $otp_verify_btn_text;?>" required/>
    <span class="tooltip" data-title="Set OTP Verify Button Text"><span class="dashicons dashicons-info"></span></span>	
    
    </td>
</tr>
<?php
}
?> 
<?php
if($hasWoocommerce || $hasUltimate || $hasWPAM){
?>
<!--Validate Before Sending OTP-->
<tr valign="top">
    <th scrope="row">
    </th>
    <td>
    <input type="checkbox" name="Websms_general[validate_before_send_otp]" id="Websms_general[validate_before_send_otp]" class="notify_box" <?php echo (($validate_before_send_otp=='on')?"checked='checked'":'')?>/><?php _e( 'Validate Before Sending OTP At Checkout', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Validate Before Sending OTP"><span class="dashicons dashicons-info"></span></span>
    </td>
</tr>
<!--/-Validate Before Sending OTP-->

<?php
}
?>
<?php
if(is_plugin_active('woocommerce/woocommerce.php')){
?>
    <tr valign="top" class="top-border">
            <th scrope="row"><?php _e( 'Miscellaneous', WebsmsConstants::TEXT_DOMAIN ) ?>
            </th>
            <td>
            <input type="checkbox" name="Websms_general[allow_multiple_user]" id="Websms_general[allow_multiple_user]" class="notify_box" <?php echo (($Websms_allow_multiple_user=='on')?"checked='checked'":'')?>/><?php _e( 'Allow multiple accounts with same mobile number', WebsmsConstants::TEXT_DOMAIN ) ?>
            <span class="tooltip" data-title="OTP at registration should be active"><span class="dashicons dashicons-info"></span></span>	
            </td>
            
    </tr>
<?php
}
?>
<!--integration for contact form 7 -->
<?php 
    if (is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
?>
<tr valign="top">
    <th scrope="row"> <?php _e( 'Enable ContactForm7', WebsmsConstants::TEXT_DOMAIN ) ?>
    </th>
    <td>
    <input type="checkbox" name="Websms_general[allow_query_sms]" id="Websms_general[allow_query_sms]" class="notify_box" 
    <?php 
        echo (($Websms_allow_query_sms=='on')?"checked='checked'":'');
    ?>
/><?php _e( 'ContactForm 7 Integration', WebsmsConstants::TEXT_DOMAIN ) ?>
    <span class="tooltip" data-title="Enable SMS for Contact Form 7"><span class="dashicons dashicons-info"></span></span>	
    </td>
</tr>
<?php }?>