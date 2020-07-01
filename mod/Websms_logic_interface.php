<?php

abstract class LogicInterface
{
	abstract public function newsletterslk_handle_logic($user_login,$user_email,$phone_number,$otp_type,$from_both);
	abstract public function newsletterslk_handle_otp_sent($user_login,$user_email,$phone_number,$otp_type,$from_both,$content);
	abstract public function newsletterslk_handle_otp_sent_failed($user_login,$user_email,$phone_number,$otp_type,$from_both,$content);
	abstract public function newsletterslk_get_otp_sent_message();
	abstract public function newsletterslk_get_otp_sent_failed_message();
	abstract public function newsletterslk__get_otp_invalid_format_message();
	abstract public function newsletterslk_handle_matched($user_login,$user_email,$phone_number,$otp_type,$from_both);
	abstract public function newsletterslk_handle_not_matched($phone_number,$otp_type,$from_both);
	
	public static function _is_ajax_form()
	{
		return (Bool) apply_filters('is_ajax_form',FALSE);
	}
}