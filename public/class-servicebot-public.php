<?php

require_once 'stripe/init.php';
use Stripe\Stripe;
use Stripe\Event;
use Stripe\Webhook;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       servicebot.io
 * @since      1.0.0
 *
 * @package    Servicebot
 * @subpackage Servicebot/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Servicebot
 * @subpackage Servicebot/public
 * @author     Servicebot <team@servicebot.io>
 */
class Servicebot_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Servicebot_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Servicebot_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/servicebot-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Servicebot_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Servicebot_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/servicebot-public.js', array( 'jquery' ), $this->version, false );

	}

}


function servicebot_ajax_create_user() {

    $email = sanitize_email( $_POST['email'] );
    $name = sanitize_user( $_POST['name'] );
    $password = $_POST['password'];
	
    $userdata = array(
      'user_login'  =>  $name,
      'user_email' => $email,
      'user_pass'   =>  $password,
      'role' => 'subscriber'
	);
	
	$user_id = wp_insert_user( $userdata );
	
	//On success
	if ( ! is_wp_error( $user_id ) ) {
		wp_new_user_notification( $user_id, null, 'both');
		wp_send_json( array(    'user_id' => $user_id,
								'email' => $email,
								'name' => $name,
								'password' => '*****',
								'message' => 'User created successfully.'
					), 200 );
	}else{
		wp_send_json_error( array(  'email' => $email,
									'name' => $name,
									'password' => '*****',
									'error' => 'Unable to create user.',
							), 500 );
	}

}
add_action( 'wp_ajax_create_user', 'servicebot_ajax_create_user' );
add_action( 'wp_ajax_nopriv_create_user', 'servicebot_ajax_create_user' );


function servicebot_create_wp_user($customer){
	$email = sanitize_email( $customer->email );
	$userdata = array(
		'user_login'  =>  $email,
		'user_email' => $email,
		'role' => 'subscriber'
	);
	$user_id = wp_insert_user( $userdata );

	if ( ! is_wp_error( $user_id ) ) {
		wp_new_user_notification( $user_id, null, 'both');
		wp_send_json( array(    
			'user_id' => $user_id,
			'email' => $email,
			'password' => '*****',
			'message' => 'User created successfully.'
		), 200 );
	}else{
		wp_send_json_error( array(  
			'email' => $email,
			'name' => $name,
			'password' => '*****',
			'error' => 'Unable to create user. The user with this email probably already exists in your Wordpress site.',
		), 500 );
	}
}

function servicebot_webhook_listener() {

	register_rest_route( 'servicebot/v1', '/stripe-hooks', array(
		'methods'  => 'POST'
	  ) );

	if ( $_SERVER['REQUEST_URI'] === '/servicebot/v1/stripe-hooks'){

		$live_mode = get_option('servicebot_servicebot_live_mode_global_setting') == 1 ? true : false;
		if(!$live_mode){
			$stripe_sign_secret = get_option('servicebot_servicebot_stripe_test_signing_secret_setting');
			$stripe_secret_key = get_option('servicebot_servicebot_stripe_test_secret_key_setting');
		}else{
			$stripe_sign_secret = get_option('servicebot_servicebot_stripe_live_signing_secret_setting');
			$stripe_secret_key = get_option('servicebot_servicebot_stripe_live_secret_key_setting');
		}

		Stripe::setApiKey($stripe_secret_key);
		$endpoint_secret = $stripe_sign_secret;
		
		$payload = @file_get_contents( 'php://input' );
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event = null;
		
		try {
			$event = Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch(\UnexpectedValueException $e) {
			// Invalid payload
			http_response_code(481);
			exit();
		} catch(\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			http_response_code(482);
			exit();
		}

		// Handle the event
		switch ($event->type) {
			case 'customer.subscription.created':
				$subscription = $event->data->object;
				//check the subscription's product and make sure it's the same as 
				//the sb_service configured in plugin settings
				$sb_service = get_option('servicebot_servicebot_service_global_setting');
				$subscription_sb_service = $subscription['metadata']['sb_service'];
				if($sb_service == $subscription_sb_service){
					$customer_id = $subscription->customer;
					$customer = \Stripe\Customer::retrieve($customer_id);
					servicebot_create_wp_user($customer);
				}else{
					wp_send_json_error( array(
						"error"=> "Subscription is not created with the sb_service you configured in your Wordpress Servicebot plugin. See Servicebot docs for more info."
					), 500 );
				}
				break;
			case 'customer.created':
				$customer = $event->data->object;
				servicebot_create_wp_user($customer);
				break;
			case 'payment_method.attached':
				$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
				// Then define and call a method to handle the successful attachment of a PaymentMethod.
				// handlePaymentMethodAttached($paymentMethod);
				break;
			// ... handle other event types
			default:
				// Unexpected event type
				http_response_code(489);
				exit();
		}

		http_response_code(200);
	}
}

add_action( 'init', 'servicebot_webhook_listener' );