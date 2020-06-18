<?php

use WP_SMS\Api\V1\Newsletter;

class WebsmscURLOTP
{	
	public static function sendtemplatemismatchemail($template)
	{
		$username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway', '');
		$To_mail=websmslk_get_option( 'alert_email', 'Websms_general', '');
		
		//Email template with content
		$params = array(
                'template' => nl2br($template),
                'username' => $username,
                'server_name' => $_SERVER['SERVER_NAME'],
                'admin_url' => admin_url(),
        );
		$emailcontent = get_websmslk_template('template/emails/mismatch_template.php',$params);
		wp_mail( $To_mail, '❗ ✱ Newsletters.lk SMS ✱ Template Mismatch', $emailcontent,'content-type:text/html');
	}
	
	public static function checkPhoneNos($nos=NULL)
	{
		$country_code = websmslk_get_option( 'default_country_code', 'Websms_general' );
		
		$nos = explode(',',$nos);
		$valid_no=array();
		if(is_array($nos))
		{			
			foreach($nos as $no){
				$no = ltrim(ltrim($no, '+'),'0'); //remove leading + and 0
				$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
				$match = preg_match(WebsmsConstants::getPhonePattern(),$no);
				if($match)
				{
					$valid_no[] = $no;
				}
			}
		}
		
		if(sizeof($valid_no)>0)
		{
			$nos = implode(',',$valid_no);
			return $nos;
		}
		else
		{
			return false;
		}
	}

	public static function sendsms($sms_data) 
	{ 
		
        $response = false;
        $APIKEY = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
        $APITOKEN = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
        $senderid = websmslk_get_option( 'Websms_Sender_ID', 'Websms_gateway' );
		$enable_short_url = websmslk_get_option( 'enable_short_url', 'Websms_general');
		
        $phone = self::checkPhoneNos($sms_data['number']);
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
        $text = htmlspecialchars($sms_data['sms_body']);
		$text=str_replace("#","ID:",$text);
		$websms=new Newsletterslk;
		$websms->setUser($APIKEY,$APITOKEN);
		$websms->setSenderID($senderid);
		$response=$websms->SendMessage($phone,$text,TRUE);
		$response_arr=json_decode($response,true);
		if($response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				self::sendtemplatemismatchemail($text);
			}
		}
        return $response;
    }
	
	public static function Websms_send_otp_token($form, $email='', $phone='')
	{
		$phone = self::checkPhoneNos($phone);
		$cookie_value = get_websmslk_cookie($phone);
		$max_otp_resend_allowed = websmslk_get_option( 'max_otp_resend_allowed', 'Websms_general');
		if(get_websmslk_cookie($phone)>$max_otp_resend_allowed)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= WebsmsMessages::showMessage('MAX_OTP_LIMIT');
			return json_encode($data);
		}
		
		$response = false;
		$APIKEY = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
        $APITOKEN = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
        $senderid = websmslk_get_option( 'Websms_Sender_ID', 'Websms_gateway' );
		$template = websmslk_get_option( 'sms_otp_send', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_OTP);
		if($senderid == ''){
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= 'No Sender ID Found, Please Setup Sender ID';
			return json_encode($data);
		}
		$websms=new Newsletterslk;
		$websms->setUser($APIKEY,$APITOKEN);
		$websms->setSenderID($senderid);
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= "phone number not valid";
			return json_encode($data);
		}
		

		$fields = array('user'=>$APIKEY, 'pwd'=>$APITOKEN, 'mobileno'=>$phone, 'sender'=>$senderid, 'template'=>$template);
		$json = json_encode($fields);
		/** Version 1.2 OPT */
		$pass= rand(100000, 999999);
		$template=str_replace("[otp]",$pass,$template);
		
		$_SESSION['otp_code'] = $pass;

		/** */
		$response = $websms->SendMessage($phone,$template,TRUE);
		$real_res=json_decode($response,true);
		if($real_res['status']=='success' || $real_res['status'] == 'queued'){
			create_websmslk_cookie($phone,$cookie_value+1);
			return json_encode(['status'=>'success']);
		}else{
			$data=array();
			$data['status']= "error";
			if(isset($real_res['message'])){
				$data['description']['desc']= $real_res['message'];
			}else{
				$data['description']['desc']= 'Unknown Reason';
			}
			return json_encode($data);
		}
		

	}
	
	public static function validate_otp_token($mobileno,$otpToken)
	{
        $response = false;
		$APIKEY = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
        $APITOKEN = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
        $senderid = websmslk_get_option( 'Websms_Sender_ID', 'Websms_gateway' );
		$mobileno = self::checkPhoneNos($mobileno);
		$websms=new Newsletterslk;
		$websms->setUser($APIKEY,$APITOKEN);
		$websms->setSenderID($senderid);
		if($mobileno===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
		
        // if ( empty( $APIKEY ) || empty( $APITOKEN ) || empty( $senderid ) ) {
        //     return $response;
        // }
		if($_SESSION['otp_code'] == $otpToken){
			clear_websmslk_cookie($mobileno);
			return json_encode(['status' => 'success']);
		} 
		$fields = array('user'=>$APIKEY, 'pwd'=>$APITOKEN, 'mobileno'=>$mobileno, 'code'=>$otpToken);
		
		$response    = self::callAPI($url, $fields, null);
		$content = json_decode($response,true);
		if(isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			clear_websmslk_cookie($mobileno);
		}
		
		
		return $response;
	}
	
	public static function get_senderids( $username=NULL, $password = NULL)
    {
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvc2VuZGVybGlzdC5qc29u");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_templates( $username=NULL, $password = NULL)
    {
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvdGVtcGxhdGVsaXN0Lmpzb24=");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_credits()
    {
       $response = false;
	   $api_key = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
       $token = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
		
		if ( empty( $api_key ) || empty( $token ) ) {
			
			return $response;
		}
               
       	$url = 'http://app.newsletters.lk/smsAPI?balance';

		$fields = array('apikey'=>$api_key, 'apitoken'=>$token);
		$response    = self::callAPI($url, $fields, null);
		return $response;
	} 
	
	public static function group_list()
    {
       $username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
       $password = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvZ3JvdXBsaXN0Lmpzb24=");

		$fields = array('user'=>$username, 'pwd'=>$password);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    }

	public static function country_list()
    {
        $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY291bnRyeWxpc3QuanNvbg==");
		$response    = self::callAPI($url, null, null);
		return $response;
    }	
		
	public static function creategrp()
    {
       $username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
       $password = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY3JlYXRlZ3JvdXAuanNvbg==");

		$fields = array('user'=>$username, 'pwd'=>$password, 'name'=>$_SERVER['SERVER_NAME']);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    } 	
	
	public static function create_contact($group_name=null,$name=null,$mob=null)
    {
       $username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
       $password = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cDovL3d3dy5zbXNhbGVydC5jby5pbi9hcGkvY3JlYXRlY29udGFjdC5qc29u");

		$fields = array('user'=>$username, 'pwd'=>$password,'grpname'=>$group_name,'name'=>$name,'number'=>$mob);
		$response    = self::callAPI($url, $fields, null);
		return $response;
    } 
		
	public static function callAPI($url, $params, $headers = array("Content-Type: application/json"))
	{
		$extra_params = array('plugin'=>'woocommerce', 'website'=>$_SERVER['SERVER_NAME']);
		$params = (!is_null($params)) ? array_merge($params, $extra_params) : $extra_params;
		
		$args=array('body'=>$params);
		$response = wp_remote_post($url,$args);
		return wp_remote_retrieve_body($response);
	}
}