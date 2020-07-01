<?php

/*
 * Plugin Name: Newsletters.lk
 * Plugin URI: https://newsletters.lk
 * Description: SMS Solucation For Sri Lanka
 * Version: 1.5
 * Author: SmarteWorks PVT 
 * Author URI: https://www.smarteworks.com
 * Text Domain: Newsletters.lk
 * WC requires at least: 5.1.0
 * WC tested up to: 5.4.0
 * License: GPL2
 */


if ( !defined( 'ABSPATH' ) ) exit;

require_once dirname(__FILE__). '/lib/class.settings-api.php';
require_once dirname(__FILE__). '/lib/newsletterslk.class.php';

if(! function_exists('plugin_get_version')){
	function plugin_get_version() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	
	}
}

function websmslk_sanitize_array($arr) 
{
	  $result = array();
	  foreach ($arr as $key => $val)
	  {
		  $result[$key] = is_array($val) ? websmslk_sanitize_array($val) : sanitize_text_field($val);
	  }
	  return $result;
}

function create_websmslk_cookie($cookie_key,$cookie_value)
{
	ob_start();
	setcookie($cookie_key,$cookie_value, time()+(15 * 60));
	ob_get_clean();
}
	
function clear_websmslk_cookie($cookie_key)
{	
	if(isset($_COOKIE[$cookie_key])){
		unset($_COOKIE[$cookie_key]);
		setcookie( $cookie_key, '', time() - ( 15 * 60 ) );
	}
}

function get_websmslk_cookie($cookie_key)
{
	if(!isset($_COOKIE[$cookie_key])) {
	  return false;
	} else {
	  return $_COOKIE[$cookie_key];
	}
}
 
function websmslk_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    return $default;
}

function get_websmslk_template($filepath,$datas)
{
		ob_start();
		extract($datas);
		include(plugin_dir_path( __DIR__ ).'newsletters.lk/'.$filepath);
		return ob_get_clean();
}

class WebSMS_WC_Order_SMS {
    public function __construct() {
        $this->instantiate();

        add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'init', array($this, 'register_hook_send_sms'));

		add_action('websmslk_after_update_new_user_phone', array( $this,  'Websms_after_user_register'), 10, 2 );
		
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'buyer_notification_update_order_meta' ) );
        add_action( 'woocommerce_order_status_changed', array( $this, 'trigger_after_order_place' ), 10, 3 );
		
		

		require_once 'includes/formlist.php';
		require_once 'views/common-elements.php';
		require_once 'mod/forms/FormInterface.php';
		require_once 'mod/Websms_form_handler.php';
		
		if(is_admin())
		{
			
			add_action( 'add_meta_boxes', array($this, 'add_send_sms_meta_box') );
			add_action( 'wp_ajax_wc_websms_lk_sms_send_order_sms', array( $this,'send_custom_sms'));
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'woocommerce_new_customer_note', array($this, 'trigger_new_customer_note'), 10 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta_link' ), 10, 4 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
		}

		add_action ('Websms_balance_notify',array($this, 'background_task'));
	}
	
    /**
     * Instantiate necessary Class
     * @return void
     */
    function instantiate() {
		spl_autoload_register( array($this, 'app_boot') );
        new Websms_Setting_Options();
    }

	/**
	 * Autoload class files on demand
	 *
	 * @param string $class requested class name
	 */	
	function app_boot( $class ) {

		require_once 'mod/Websms_logic_interface.php';
		require_once 'mod/Websms_phone_logic.php';
		require_once 'includes/sessionVars.php';
		require_once 'includes/utility.php';
		require_once 'includes/constants.php';
		require_once 'includes/messages.php';
		require_once 'includes/curl.php';
		require_once 'inc/app.php';
		require_once 'inc/admin-option.php';

		// if ( stripos( $class, 'Websms_' ) !== false ) {

		// 	$class_name = str_replace( array('Websms_', '_'), array('', '-'), $class );
		// 	$filename = dirname( __FILE__ ) . '/inc/' . strtolower( $class_name ) . '.php';
			
		// 	if ( file_exists( $filename ) ) {
		// 		require_once $filename;
		// 	}
		// }
	}

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WebSMS_WC_Order_SMS();
        }
        return $instance;
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public static function localization_setup() {
		load_plugin_textdomain( 'newsletters.lk', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
		
	function Websms_after_user_register($user_id,$billing_phone) 
	{
	  
	  $Websms_reg_notify 			= websmslk_get_option( 'registration_msg', 'Websms_general', 'off');
	  $sms_body_new_user 			= websmslk_get_option( 'sms_body_registration_msg', 'Websms_message', WebsmsMessages::DEFAULT_NEW_USER_REGISTER );	  
	  $Websms_reg_admin_notify 	= websmslk_get_option( 'admin_registration_msg', 'Websms_general', 'off');
	  $sms_admin_body_new_user 		= websmslk_get_option( 'sms_body_registration_admin_msg', 'Websms_message', WebsmsMessages::DEFAULT_ADMIN_NEW_USER_REGISTER );
	  $admin_phone_number     		= websmslk_get_option( 'sms_admin_phone', 'Websms_message', '' );
	  $user 						= get_userdata($user_id);
	
	  if($Websms_reg_notify=='on' && $billing_phone!='')
	  {
			$search = array(
				'[username]',
				'[store_name]',
				'[email]',
				'[billing_phone]'
			);
			
			$replace = array(
				$user->user_login,
				get_bloginfo(),
				$user->user_email,
				$billing_phone
			);
			
			$sms_body_new_user 			= str_replace($search,$replace,$sms_body_new_user);
			$buyer_sms_data['number'] 	= $billing_phone;
			$buyer_sms_data['sms_body'] = $sms_body_new_user;
			$buyer_response 			= WebsmscURLOTP::sendsms( $buyer_sms_data );
	  }
	  if($Websms_reg_admin_notify=='on' && $admin_phone_number!='')
	  {
			$search=array(
				'[username]',
				'[store_name]',
				'[email]',
				'[billing_phone]'
			);
			
			$replace=array(
				$user->user_login,
				get_bloginfo(),
				$user->user_email,
				$billing_phone
			);
			
			$sms_admin_body_new_user = str_replace($search,$replace,$sms_admin_body_new_user);
			$admin_sms_data['number'] 	= $admin_phone_number;
			$admin_sms_data['sms_body'] = $sms_admin_body_new_user;
			$admin_response = WebsmscURLOTP::sendsms( $admin_sms_data );
	  }
	}
	
	

	function fn_sa_send_sms($number, $content)
	{
		$obj=array();
		$obj['number'] = $number;
		$obj['sms_body'] = $content;
		$response = WebsmscURLOTP::sendsms($obj);
		return $response;
	}

	function register_hook_send_sms()
	{
		add_action( 'sa_send_sms', array($this, 'fn_sa_send_sms'), 10, 2 ); 
	}
	
    public function admin_enqueue_scripts() {

        wp_enqueue_style( 'admin-Websms-styles', plugins_url( 'css/admin.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_script( 'admin-Websms-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_script( 'admin-Websms-scriptss', plugins_url( 'js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), false, false );

        wp_localize_script( 'admin-Websms-scripts', 'Websms', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );
		
    }

    public function plugin_row_meta_link( $plugin_meta, $plugin_file, $plugin_data, $status ) 
	{

        if( isset( $plugin_data['slug'] ) && ( $plugin_data['slug'] == 'newsletters.lk' ) && ! defined( 'Websms_DIR' ) ) {
			$plugin_meta[] = '<a href="http://newsletters.lk/knowledgebase/wordpress.html" target="_blank">Documentation</a>';
        }
        return $plugin_meta;
    }
	
	function add_action_links ( $links ) {
		$links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=newsletters.lk') );
		return $links;
	}

	function add_send_sms_meta_box(){
			add_meta_box(
				'wc_websms_lk_send_sms_meta_box',
				'newsletters.lk SMS (Custom SMS)',
				array($this, 'display_send_sms_meta_box'),
				'shop_order',
				'side',
				'default'
			);
	}
	
	function display_send_sms_meta_box($data){
		global $woocommerce, $post;
		$order = new WC_Order($post->ID);
		$order_id = $post->ID;
		
		$apitoken = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway' );
		$apikey = websmslk_get_option( 'Websms_API_Token', 'Websms_gateway' );
		$senderid=websmslk_get_option("Websms_Sender_ID","Websms_gateway","");
		$templates = [];
		?>
		<select name="Websms_templates" id="Websms_templates" style="width:87%;" onchange="return selecttemplate(this, '#wc_websms_lk_sms_order_message');">
		<option value="">Select Template</option>
		<?php
		if(array_key_exists('description', $templates) && (!array_key_exists('desc', $templates['description']))) {
		foreach($templates['description'] as $template) {
		?>
		<option value="<?php echo $template['Smstemplate']['template'] ?>"><?php echo $template['Smstemplate']['title'] ?></option>
		<?php } } ?>
		</select>
		<span class="woocommerce-help-tip" data-tip="You can add templates from your newsletters.lk Dashboard"></span>
										<p><textarea type="text" name="wc_websms_lk_sms_order_message" id="wc_websms_lk_sms_order_message" class="input-text" style="width: 100%;" rows="4" value=""></textarea></p>
			<input type="hidden" class="wc_websms_lk_order_id" id="wc_websms_lk_order_id" value="<?php echo $order_id;?>" >
			<p><a class="button tips" id="wc_websms_lk_sms_order_send_message" data-tip="Send an SMS to the billing phone number for this order.">Send SMS</a>
			<span id="wc_websms_lk_sms_order_message_char_count" style="color: green; float: right; font-size: 16px;">0</span></p>
			<?php
	}
	
	static function only_credit(){
		$trans_credit = 0;
		$credits = json_decode(WebsmscURLOTP::get_credits(),true); 
		if(isset($credits['balance']))
		{
			$trans_credit=$credits['balance'];
		}
		return $trans_credit;
	 }
		
	static function run_on_activate()
	{
		if( !wp_next_scheduled( 'Websms_balance_notify' ) )
		{
			wp_schedule_event( time(), 'hourly', 'Websms_balance_notify');
		}
	}

	static function run_on_deactivate()
	{
		wp_clear_scheduled_hook('Websms_balance_notify');
	}

	function background_task()
	{
		$low_bal_alert = websmslk_get_option( 'low_bal_alert', 'Websms_general', 'off');
		$daily_bal_alert = websmslk_get_option( 'daily_bal_alert', 'Websms_general', 'off');
		$user_authorize = new Websms_Setting_Options();
		$islogged = true;
		$auto_sync = websmslk_get_option( 'auto_sync', 'Websms_general', 'off');
		
		if($low_bal_alert == 'on'){self::send_Websms_balance();}
		if($daily_bal_alert == 'on'){self::daily_email_alert();}
		
	}
	
	static function send_Websms_balance()
	{
		$date = date("Y-m-d");	
		$update_dateTime = websmslk_get_option( 'last_updated_lBal_alert','Websms_background_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		 
		$username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway', '');
		$low_bal_val = websmslk_get_option( 'low_bal_val', 'Websms_general', '');  
		$To_mail=websmslk_get_option( 'alert_email', 'Websms_general', '');
		$trans_credit = self::only_credit();
		
		$params = array(
                'trans_credit' => $trans_credit,
                'username' => $username,
                'admin_url' => admin_url(),
        );
		$emailcontent = get_websmslk_template('template/emails/Websms-low-bal.php',$params);
		update_option('Websms_background_task',array('last_updated_lBal_alert'=>date('Y-m-d')));//update last time and date 
		if($trans_credit <= $low_bal_val)
		{
			 wp_mail( $To_mail, '❗ ✱ Newsletters.lk SMS ✱ Low Balance Alert', $emailcontent,'content-type:text/html');
		}	
	}

	function daily_email_alert(){
		$username = websmslk_get_option( 'websmslk_API_key', 'Websms_gateway', '');  //Websms auth username
		$real_name=websmslk_get_option('Websms_Sender_ID','Websms_gateway','');
		$date = date("Y-m-d");	
		$To_mail=websmslk_get_option( 'alert_email', 'Websms_general', '');
		$update_dateTime = websmslk_get_option( 'last_updated_dBal_alert','Websms_background_dBal_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		
		$daily_credits = self::only_credit();
		$params = array(
                'daily_credits' => $daily_credits,
                'username' => $real_name,
                'date' => $date,
                'admin_url' => admin_url(),
        );
		$dailyemailcontent = get_websmslk_template('template/emails/daily_email_alert.php',$params);
		update_option('Websms_background_dBal_task',array('last_updated_dBal_alert'=>date('Y-m-d')));//update last time and date 
		wp_mail($To_mail, '✱ Newsletters.lk SMS ✱ Daily  Balance Alert ',$dailyemailcontent,'content-type:text/html');
	 }	
    /**
     * Update Order buyer notify meta in checkout page
     * @param  integer $order_id
     * @return void
     */
    function buyer_notification_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['buyer_sms_notify'] ) ) { // $_POST use only for data validate
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
        }
    }

    public  function trigger_after_order_place( $order_id, $old_status, $new_status ) {	
		
		$order = new WC_Order( $order_id );
		
        if( !$order_id ) {
            return;
        }
        $admin_sms_data = $buyer_sms_data = array();

        $order_status_settings  = websmslk_get_option( 'order_status', 'Websms_general', array() );
        $admin_phone_number     = websmslk_get_option( 'sms_admin_phone', 'Websms_message', '' );
		
        if( count( $order_status_settings ) < 0 ) {
            return;
        }
		
        if( in_array( $new_status, $order_status_settings ) ) 
		{
			$default_buyer_sms 			=  defined('WebsmsMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status))) ? constant('WebsmsMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status))) : WebsmsMessages::DEFAULT_BUYER_SMS_STATUS_CHANGED;
			
			$buyer_sms_body 			= websmslk_get_option( 'sms_body_'.$new_status, 'Websms_message', $default_buyer_sms);
			$buyer_sms_data['number'] 	= get_post_meta( $order_id, '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $this->pharse_sms_body( $buyer_sms_body, $new_status, $order, '');

			$buyer_response = WebsmscURLOTP::sendsms( $buyer_sms_data );
			
			
			$response=json_decode($buyer_response, true);
			
			if( $response['status']=='success' ) {
				$order->add_order_note( __('SMS Send to buyer Successfully.', 'Websms' ) );
			} else {
				if(isset($response['description']) && is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'Websms' ) );
				}
				else
				{
					$order->add_order_note( __($response['description'], 'Websms' ) );
				}
			}
		}
		
		if(websmslk_get_option( 'admin_notification_'.$new_status, 'Websms_general', 'on' ) == 'on' && $admin_phone_number!='')
		{	
			if(strpos($admin_phone_number,'post_author') !== false)
			{
				$order_items 		= $order->get_items();
				$first_item 		= current($order_items);		
				$prod_id 			= $first_item['product_id'];
				$product 			= wc_get_product( $prod_id );
				$author_no = get_the_author_meta('billing_phone', get_post($prod_id)->post_author);
				$admin_phone_number = str_replace('post_author',$author_no,$admin_phone_number);
			}
			$default_admin_sms = defined('WebsmsMessages::DEFAULT_ADMIN_SMS_'.str_replace(" ","_",strtoupper($new_status))) ? constant('WebsmsMessages::DEFAULT_ADMIN_SMS_'.str_replace(" ","_",strtoupper($new_status))) : WebsmsMessages::DEFAULT_ADMIN_SMS_STATUS_CHANGED;
			
			$admin_sms_body  			= websmslk_get_option( 'admin_sms_body_'.$new_status, 'Websms_message', $default_admin_sms );	
			$admin_sms_data['number']   = $admin_phone_number;
			$admin_sms_data['sms_body'] = $this->pharse_sms_body( $admin_sms_body, $new_status, $order, '');
			$admin_response             = WebsmscURLOTP::sendsms( $admin_sms_data );
			$response=json_decode($admin_response,true);
			if( $response['status']=='success' ) {
				$order->add_order_note( __( 'SMS Sent Successfully.', 'Websms' ) );
			} else {
				if(is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'Websms' ) );
				}
				else {
					$order->add_order_note( __($response['description'], 'Websms' ) );
				}
			}
		}
    }

	function trigger_new_customer_note( $data ) {
		
		if(websmslk_get_option('buyer_notification_notes', 'Websms_general')=="on")
		{
			$order_id					= $data['order_id'];
			$order						= new WC_Order( $order_id ); 
			$buyer_sms_body         	= websmslk_get_option( 'sms_body_new_note', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_NOTE );
			$buyer_sms_data 			= array();
			$buyer_sms_data['number']   = get_post_meta( $data['order_id'], '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $this->pharse_sms_body( $buyer_sms_body, $order->get_status(), $order, $data['customer_note']);
			$buyer_response 			= WebsmscURLOTP::sendsms( $buyer_sms_data );
			$response					= json_decode($buyer_response,true);
			
			if( $response['status']	== 'success' ) {
				$order->add_order_note( __( 'Order note SMS Sent to buyer', 'Websms' ) );
			} else {
				$order->add_order_note( __($response['description']['desc'], 'Websms' ) );
			}
		}
	}
	
    public function pharse_sms_body( $content, $order_status, $order, $order_note, $rma_id = '' ) {
		
		$order_id			= is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
		$order_variables	= get_post_custom($order_id); 
		$order_items 		= $order->get_items();
		$item_name			= implode(", ",array_map(function($o){return $o['name'];},$order_items));
		$item_name_with_qty	= implode(", ",array_map(function($o){return sprintf("%s [%u]", $o['name'], $o['qty']);},$order_items));
		$store_name 		= get_bloginfo();
		$tracking_number 	= '';
		$tracking_provider 	= '';
		$tracking_link 		= '';
		$aftrShp_tracking_number 	= '';
		$aftrShp_tracking_provider_name 	= '';
		
		if(
			(strpos($content, '[tracking_number]') 		!== false) || 
			(strpos($content, '[tracking_provider]') 	!== false) || 
			(strpos($content, '[tracking_link]') 		!== false)
		)
		{			
			if(is_plugin_active( 'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php')) 
			{
				$tracking_info = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );
				if(sizeof($tracking_info) > 0)
				{
					$t_info = array_shift($tracking_info);
					$tracking_number 	= $t_info['tracking_number'];
					$tracking_provider 	= ($t_info['tracking_provider'] != '') ? $t_info['tracking_provider'] : $t_info['custom_tracking_provider'];
					$tracking_link 		= $t_info['custom_tracking_link'];
				}
			}
			elseif( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ))
			{
				$ast = new WC_Advanced_Shipment_Tracking_Actions;
				$tracking_items = $ast->get_tracking_items( $order_id,true );
				if (count($tracking_items)>0)
				{
					$t_info = array_shift($tracking_items);
					$tracking_number = $t_info['tracking_number'];
					$tracking_provider = $t_info['formatted_tracking_provider'];
					$tracking_link = $t_info['formatted_tracking_link'];
				}
			}
		}
		
		if(
			(strpos($content, '[aftership_tracking_number]') 		!== false) || 
			(strpos($content, '[aftership_tracking_provider_name]') 	!== false) 
		)
		{	if(is_plugin_active( 'aftership-woocommerce-tracking/aftership.php')) 
			{		
				$aftrShp_tracking_number = get_post_meta( $order_id, '_aftership_tracking_number', true );
				$aftrShp_tracking_provider_name = get_post_meta( $order_id, '_aftership_tracking_provider_name', true );
			}
		}
		
		$find = array(
            '[order_id]',
            '[order_status]',
            '[rma_status]',
            '[first_name]',
            '[item_name]',
            '[item_name_qty]',
            '[order_amount]',
            '[note]',
            '[rma_number]',
            '[store_name]',
            '[tracking_number]',
            '[tracking_provider]',
            '[tracking_link]',
			'[aftership_tracking_number]',
            '[aftership_tracking_provider_name]',
            '[pdf_invoice_link]',
        );
        $replace = array(
            $order->get_order_number(),
            $order_status,
            $order_status,
            '[billing_first_name]',
            $item_name,
			$item_name_with_qty,
			$order->get_total(),
			$order_note,
			$rma_id,
			$store_name,
			$tracking_number,
			$tracking_provider,
			$tracking_link,
			$aftrShp_tracking_number,
			$aftrShp_tracking_provider_name,
			admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=" . $order_id."&order_key=".$order->get_order_key() )
        );
		
        $content = str_replace( $find, $replace, $content );
		foreach ($order_variables as &$value) {
			$value = $value[0];
		}
		unset($value);
		
		$order_variables = array_combine(
			array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
			$order_variables
		);
        $content = str_replace( array_keys($order_variables), array_values($order_variables), $content );
        return $content;
    }
	
	
	public function new_menu_websms_lk ($panels) {
		$panels['Newsletters.lk-sms-panel'] = array(
				'title' => __('Newsletters.lk SMS'),
				'callback' => array($this, 'add_panel_websms_lk')
		);
		return $panels;
	}
	
	public function add_panel_websms_lk($form) { 
		if ( wpcf7_admin_has_edit_cap() ) {
		  $options = get_option( 'Websms_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id) );
		  if( empty( $options ) || !is_array( $options ) ) {
			$options 		= array( 'phoneno' => '', 'text' => '', 'visitorNumber' => '','visitorMessage' => '');
		  }
		  $options['form'] 	= $form;
          $data 			= $options; 
		  include(plugin_dir_path( __DIR__ ).'Newsletters.lk/template/cf7-template.php'); 
		}
	}
	
  public function save_form( $form ) {
    update_option( 'Websms_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id), sanitize_text_field($_POST['wpcf7Websms-settings']) );
  } 
  
  public function get_cf7_tagS_To_String($value,$form){
		if(function_exists('wpcf7_mail_replace_tags')) {
			$return = wpcf7_mail_replace_tags($value); 
		} elseif(method_exists($form, 'replace_mail_tags')) {
			$return = $form->replace_mail_tags($value); 
		} else {
			return;
		}
		return $return;
	}
	
	public	function send_custom_sms($data) 
	{
		$order 							= new WC_Order( sanitize_text_field($_POST['order_id']));
		$sms_body 						= sanitize_text_field($_POST['sms_body']);
		$buyer_sms_data 				= array();
		$buyer_sms_data['number']   	= get_post_meta( sanitize_text_field($_POST['order_id']), '_billing_phone', true );
		$buyer_sms_data['sms_body'] 	= $this->pharse_sms_body($sms_body, $order->get_status(), $order, '');
		$buyer_response 				= WebsmscURLOTP::sendsms( $buyer_sms_data );
		echo $buyer_response;
		exit();
	}
	
	public function do_gForm_processing( $entry, $form ) 
	{	 
		$meta = RGFormsModel::get_form_meta( $entry['form_id'] );
	    $feeds = GFAPI::get_feeds(null,$entry['form_id'],'gravity-forms-newsletters.lk');
		$message = $cstmer_nos = $admin_nos = $admin_msg ='';
		foreach($feeds as $feed)
		{
			if(sizeof($feed)>0 && array_key_exists('meta',$feed))
			{
				$admin_msg = $feed['meta']['Websms_gForm_admin_text'];	
				$admin_nos = $feed['meta']['Websms_gForm_admin_nos'];
				$cstmer_nos_pattern = $feed['meta']['Websms_gForm_cstmer_nos'];				
				$message =$feed['meta']['Websms_gForm_cstmer_text'];	
			}
		}
		
		foreach($meta['fields'] as $meta_field)
		{
			if(is_object($meta_field)) 
			{
				$field_id = $meta_field->id;
				if(isset($entry[$field_id]))
				{
					$label = $meta_field->label;
					$search = '{'.$label.':'.$field_id.'}';
					$replace=$entry[$field_id];
					$message = str_replace($search,$replace,$message);
					$admin_msg = str_replace($search,$replace,$admin_msg);
					
					if($cstmer_nos_pattern==$search)
					{
						$cstmer_nos=$replace;
					}
				}				
			}
		}				
		if($cstmer_nos!='' && $message!='')
		{
			$buyer_sms_data['number']   = $cstmer_nos;
			$buyer_sms_data['sms_body'] = $message;
			$buyer_response = WebsmscURLOTP::sendsms( $buyer_sms_data );
		}
		if($admin_nos!='' && $admin_msg!='')
		{
			$admin_sms_data['number']   = $admin_nos;
			$admin_sms_data['sms_body'] = $admin_msg;
			$admin_response = WebsmscURLOTP::sendsms( $admin_sms_data );
		}
	}
	
	/**gravity form submission frontend ends*/
	
} // WebSMS_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 */
add_action( 'plugins_loaded', 'load_WebSMS_WC_Order_SMS' );

function load_WebSMS_WC_Order_SMS() {
    $Websms = WebSMS_WC_Order_SMS::init();
}

register_activation_hook( __FILE__, 	array('WebSMS_WC_Order_SMS', 'run_on_activate'));
register_deactivation_hook( __FILE__, 	array('WebSMS_WC_Order_SMS', 'run_on_deactivate'));
?>