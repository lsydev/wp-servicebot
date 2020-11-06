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
		wp_send_json( array(  
			'info' => 'Unable to create user. The user with this email probably already exists in your Wordpress site.',
			'email' => $email,
			'name' => $name,
			'password' => '*****',
		), 200 );
	}
}

function servicebot_webhook_listener() {

	$rest_server = rest_get_server();
	$rest_args = array(
		'methods'  => 'POST',
	);
	$rest_server->register_route( 'servicebot/v1', '/stripe-hooks', $rest_args, false );
	

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
				$customer_id = $subscription->customer;
				$product_id = $subscription['plan']['product'];
				//check the subscription's product and make sure it's the same as 
				//the sb_service configured in plugin settings
				$sb_service = get_option('servicebot_servicebot_service_global_setting');
				//get the product using subscription obj's attached product id
				try{
					$product = \Stripe\Product::retrieve($product_id);
					$product_sb_service = $product['metadata']['sb_service'];
					// $subscription_sb_service = $subscription['metadata']['sb_service'];
					if($sb_service == $product_sb_service){
						$NUM_OF_ATTEMPTS = 5;
						$attempts = 0;

						do {
							try
							{
								$customer = \Stripe\Customer::retrieve($customer_id);
								//after getting a customer object, pass it to create wp user
								if(!$customer['email']){
									throw(new Exception('No customer retrieved'));
								}
								servicebot_create_wp_user($customer);
								break;
							} catch (Exception $e) {
								$attempts++;
								if(attempts == NUM_OF_ATTEMPTS-1){
									//log something to alert site owner of the failure
									wp_send_json_error( array(
										"error"=> "We are unable to retrieve customer object with id $customer_id from the stripe account after $attempts retries to get the customer's email to create a wordpress user. Please create this wordpress user manually.",
										"action" => "retrieve customer object via stripe API for $customer_id",
										"attempt_counts" => $attempts,
										"payload"=>array(
											"customer_id" => $customer_id
										),
										"stripe_response" => array(
											"customer_object" => $customer,
										)
									), 500 );
								}
								sleep(1);
								continue;
							}
							break;
						} while($attempts < $NUM_OF_ATTEMPTS);
						
					}else{
						wp_send_json( array(
							"message" => "Subscription is not created with the sb_service $sb_service you configured in your Wordpress Servicebot plugin. See Servicebot docs for more info. If you continue to have this issue and you think everything is setup correctly, please contact Servicebot for more help!",
							"action" => "Validating the subscrption is created with a product with sb_service, this product must be the same as what is setup in your wordpress site's servicebot plugin settings.",
							"info" => array(
								"actual_product" => array(
									"sb_service" => $product['metadata']['sb_service'],
									"sb_tier" => $product['metadata']['sb_tier'],
									"product_object" => $product,
								)
							)
						), 200 );
					}
				}catch(Exception $e){
					wp_send_json_error( array(
						"error"=> "We are unable to retrieve product with id $product_id to validate the sb_service setup is with this site from the stripe account, please create this user $customer_id manually.",
						"info" => "Please make sure your servicebot wordpress plugin has the correct Stripe API keys set.",
						"action" => "retrieve product via stripe API for $product_id",
						"payload" => array(
							"product_id" => $product_id,
						),
						"stripe_response" => array(
							"product_object" => $product,
						)
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

	return $rest_server;
}

add_action( 'wp_loaded', 'servicebot_webhook_listener' );