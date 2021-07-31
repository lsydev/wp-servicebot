<?php

if(!class_exists('Stripe\Stripe')){
	require_once 'stripe/init.php';
}
use Stripe\Stripe;
use Stripe\Event;
use Stripe\Webhook;

/**
	 * User meta data created by billflow
	 * billflow_created
	 * billflow_checkout_created
	 * billflow_stripe_webhook_created
	 * billflow_stripe_subscription_id
	 * 
	 * User created default role to whatever the system default is.
	 */

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

function billflow_log_in_user($user_id, $email, $user_login, $password, $subscription_id, $caller){
	
	$is_webhook_new_user = get_user_meta( $user_id, "billflow_stripe_webhook_created", TRUE );
	$user_subscription_id_matched = get_user_meta( $user_id, "billflow_stripe_subscription_id", TRUE) === $subscription_id;
	$real_password = $is_webhook_new_user ? $subscription_id : $password;
	$wp_auth = wp_authenticate($user_login, $real_password);

	if(is_wp_error($wp_auth)){

		if($is_webhook_new_user && $user_subscription_id_matched){
			// safe to set the password for this user since they have matching OR 
			// they are the newly created by webhook and have matching subscription id 
			// subscription id and is a new webhook created user.
			wp_set_password( $password, $user_id );
			wp_clear_auth_cookie(); // Login the new user
			wp_set_current_user ( $user_id ); // Set the current user detail
			wp_set_auth_cookie  ( $user_id ); // Set auth details in cookie
			// remove the meta that indicates need password update
			delete_user_meta($user_id, "billflow_stripe_webhook_created");
			
			wp_send_json( array(  
				'email' => $email,
				'user_id' => $user_id,
				'user_login' => $user_login,
				'password' => '****************************',
				'login_by_webhook_subscription' => true,
				'is_webhook_new_user' => $is_webhook_new_user,
				'user_subscription_id_matched' => $user_subscription_id_matched,
				'refresh'=> true,
				'caller' => $caller
			), 200 );

		}else{
			// this is probably someone else used the same email OR put in the wrong password.
			wp_send_json_error( array(  
				'email' => $email,
				'user_already_exists' => true,
				'password_match' => false,
				'is_webhook_new_user' => $is_webhook_new_user,
				'user_subscription_id_matched' => $user_subscription_id_matched,
				'caller' => $caller
			), 401 );
		}
	}else{
		// Login the new user
		wp_clear_auth_cookie();
		wp_set_current_user ( $user_id ); // Set the current user detail
		wp_set_auth_cookie  ( $user_id ); // Set auth details in cookie
		
		// remove the meta that indicates need password update because they got the right password already
		delete_user_meta($user_id, "billflow_stripe_webhook_created");

		wp_send_json( array(  
			'email' => $email,
			'user_id' => $user_id,
			'user_login' => $user_login,
			'password' => '****************************',
			'login_user_by_password' => true,
			'refresh'=> true,
			'caller' => $caller
		), 200 );
	}
}

/**
 * create user function called by frontend widget ajax
 */
function servicebot_ajax_create_user() {

    $email = sanitize_email( $_POST['email'] );
    $name = sanitize_user( $_POST['name'] );
	$password = $_POST['password'];
	$subscription_id = $_POST['subscription_id'];
	
    $userdata = array(
      'user_login'  =>  $name,
      'user_email' => $email,
      'user_pass'   =>  $password,
	);
	
	$user_id = wp_insert_user( $userdata );

	//On success
	if ( ! is_wp_error( $user_id ) ) {

		// add user meta to note that this is created by ajax
		update_user_meta($user_id, "billflow_checkout_created", TRUE);
		update_user_meta($user_id, "billflow_created", TRUE);

		// Send email to the new user
		wp_new_user_notification( $user_id, null, 'both');

		// Login the new user
		wp_clear_auth_cookie();
        wp_set_current_user ( $user_id ); // Set the current user detail
        wp_set_auth_cookie  ( $user_id ); // Set auth details in cookie

		wp_send_json( array(    'user_id' => $user_id,
								'email' => $email,
								'name' => $name,
								'password' => '*****',
								'message' => 'User created successfully by ajax call',
								'refresh' => true
					), 200 );
	}else{
		// if user already exists
		$existing_user = get_user_by('email', $email);
		
		if($existing_user){
			$user_id = $existing_user->get('id');
			$user_login = $existing_user->get('user_login');
		
			// if user exists but not newly created by webhook
			// because user exists and provided a password.
			billflow_log_in_user($user_id, $email, $user_login, $password, $subscription_id,'billflow_login_by_password');

		}else{
			// WP_Error but not existing user, some other unhandled errors.
			// for some reason other than an existing user with the same email.
			wp_send_json_error( array(  'email' => $email,
				'error' => $user_id->get_error_message(),
			), 500 );
		}
	}

}
add_action( 'wp_ajax_create_user', 'servicebot_ajax_create_user' );
add_action( 'wp_ajax_nopriv_create_user', 'servicebot_ajax_create_user' );

function updateUserRole($user_id, $product_sb_tier){
	// Set the new user's role
	/**
	 * [$tier_to_role_map an associative array sb_tier => role]
	 *
	 * @var [associative array]
	 */
	$user = get_user_by('id', $user_id);

	if(in_array('administrator', $user->roles)){
		return;
	}

	$tier_to_role_map;
	$all_roles = wp_roles()->get_names();
	foreach($all_roles as $role_name){
		$tier = get_option("billflow_role_to_tier_$role_name");
		if($tier){
			$tier_to_role_map[$tier] = $role_name;
		}
	}
	if($tier_to_role_map && $user){
		if($tier_to_role_map[$product_sb_tier]){
			$user->set_role(strtolower($tier_to_role_map[$product_sb_tier]));
		}else{
			$user->set_role(get_option('default_role'));
		}
	}else if($user){
		$user->set_role(get_option('default_role'));
	}
}

function removeUserRole($wp_user){
	// remove user roles for cancel and out of status acccounts
	// except for admin
	if(!in_array('administrator', $wp_user->roles)){
		$wp_user->set_role("");
	}
}

/**
 * webhook create user handler, called by the Stripe webhook handler
 *
 * @param   [object]$customer         Stripe customer object
 * @param   [string]$product_sb_tier  sb_tier meta data of the subscription
 * @param   [string]$subscription_id  Stripe subscription id
 *
 * @return  [WP_User]                 returns the WP user object if created
 */
function servicebot_create_wp_user($customer, $product_sb_tier = NULL, $subscription_id = NULL){
	$email = sanitize_email( $customer->email );

	// create wp user with the email and role should be assigned to whatever system settings's default is
	$userdata = array(
		'user_login'  => $email,
		'user_email' => $email,
		'password' => $subscription_id
	);

	$user_id = wp_insert_user( $userdata );

	if ( ! is_wp_error( $user_id ) ) {

		// add user meta to note that this is created by Stripe webhook
		update_user_meta($user_id, "billflow_stripe_webhook_created", TRUE);
		update_user_meta($user_id, "billflow_created", TRUE);
		if(isset($subscription_id)){
			update_user_meta($user_id, "billflow_stripe_subscription_id", $subscription_id);
		}

		// update user role according to the billfow plugin roles settings
		$new_user = get_user_by('id', $user_id);
		if($product_sb_tier){
			updateUserRole($user_id, $product_sb_tier);
		}

		// send email notification to new user
		wp_new_user_notification( $user_id, null, 'both');

		wp_send_json( array(    
			'user_id' => $user_id,
			'email' => $email,
			'wp_user' => get_user_by('id', $user_id),
			'password' => '*****',
			'message' => 'User created successfully.'
		), 200 );
		return $new_user;

	}else{

		// if user already exists
		$user = get_user_by('email', $email);
		if($user){
			// and the subscription has product metadata sb_tier
			if($product_sb_tier){
				// then try updating the user role according to the billflow roles settings
				updateUserRole($user->get('id'), $product_sb_tier);
				wp_send_json( array(  
					'info' => 'User already exists, updated user role if changed',
					'user' => get_user_by('email', $email)
				), 200 );
			}else{
				wp_send_json( array(  
					'ok', true,
					'info' => 'User already exists, no action performed.',
					'user' => get_user_by('email', $email)
				), 200 );
			}
		}
	}
	return NULL;
}

function getStripeProduct($event, $product_id){
	try{
		$product = \Stripe\Product::retrieve($product_id);
		return $product;
	}catch(Exception $e){
		wp_send_json_error( array(
			"event"				=> $event,
			"error"				=> "We are unable to retrieve product with id $product_id to validate the sb_service setup is with this site from the stripe account.",
			"info" 				=> "Please make sure your Billflow WordPress plugin webhooks settings has the correct Stripe API keys and sb_service setting.",
			"action" 			=> "retrieve product via stripe API for $product_id",
			"payload"	 		=> array(
									"product_id" => $product_id,
								),
			"stripe_response"	 => array(
									"product_object" => $product,
								)
		), 500 );
	}
}

function getStripeCustomer($event, $customer_id){
	$NUM_OF_ATTEMPTS = 5;
	$attempts = 0;

	do {
		try{
			$customer = \Stripe\Customer::retrieve($customer_id);
			if(!$customer['email']){
				return NULL;
			}
			return $customer;
		} catch (Exception $e) {
			$attempts++;
			if($attempts == $NUM_OF_ATTEMPTS-1){
				wp_send_json_error( array(
					"event"				=> $event,
					"error"				=> "We are unable to retrieve customer object with id $customer_id from the stripe account 
											after $attempts retries to get the customer's email in order to handle this event.",
					"action" 			=> "retrieve customer object via stripe API for $customer_id",
					"attempt_counts" 	=> $attempts,
					"payload"			=>array(
											"customer_id" => $customer_id
										),
					"stripe_response" 	=> array(
											"customer_object" => $customer,
										)
				), 500 );
			}
			sleep(1);
			continue;
		}
		break;
	} while($attempts < $NUM_OF_ATTEMPTS);
}

function servicebot_webhook_listener() {

	$rest_server = rest_get_server();
	$rest_args = array(
		'methods'  => 'POST',
	);
	$rest_server->register_route( 'servicebot/v1', '/stripe-hooks', $rest_args, false );
	$rest_server->register_route( 'billflow/v1', '/stripe-hooks', $rest_args, false );
	

	if ( $_SERVER['REQUEST_URI'] === '/servicebot/v1/stripe-hooks' || $_SERVER['REQUEST_URI'] === '/billflow/v1/stripe-hooks'){

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
			case 'customer.subscription.updated':
				$subscription = $event->data->object;
				$customer_id = $subscription->customer;
				$product_id = $subscription['plan']['product'];
				$sb_service = get_option('servicebot_servicebot_service_global_setting');
				$product = getStripeProduct($event->type, $product_id);
				if($product){
					$product_sb_service = $product['metadata']['sb_service'];
					$product_sb_tier = $product['metadata']['sb_tier'];
					
					if($sb_service == $product_sb_service){

						$customer = getStripeCustomer($event->type, $customer_id);
						if(!$customer['email']){
							// customer does not exist in stripe, do nothing
						}else{
							$user = get_user_by('email', $customer['email']);
							// get the subscription status
							$subscription_status = $subscription['status'];
							// if status is canceled, unpaid, past_due, incomplete or incomplete_expired
							if(in_array($subscription_status, ['unpaid', 'past_due', 'incomplete', 'incomplete_expired'])){
								//set the user's role to none
								removeUserRole($user);
								wp_send_json( array(    
									'event'					=> $event->type,
									'subscription_status' 	=> $subscription_status,
									'message' 				=> "Removed all WP roles from user because their subscription status is one of these 'unpaid' || 'past_due' || 'incomplete' || 'incomplete_expired'",
									'updated_user' 			=> get_user_by('email', $customer['email'])
								), 200 );
							}else{
								//call update user status to check if it needs updating according to the $product_sb_tier
								updateUserRole($user->get('id'), $product_sb_tier);
								// send response to Stripe
								wp_send_json( array(    
									'event'			=> $event->type,
									'message' 		=> "Updated user WP roles based on the sb_tier they are subscribed to.",
									'updated_user' 	=> get_user_by('email', $customer['email'])
								), 200 );
							}
						}
					}
				}
				break;	
			case 'customer.subscription.deleted':
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
					$product_sb_tier = $product['metadata']['sb_tier'];
					// $subscription_sb_service = $subscription['metadata']['sb_service'];
					if($sb_service == $product_sb_service){
						$NUM_OF_ATTEMPTS = 5;
						$attempts = 0;

						do {
							try{
								$customer = \Stripe\Customer::retrieve($customer_id);
								//after getting a customer object, pass it to create wp user
								if(!$customer['email']){
									throw(new Exception('No customer retrieved'));
								}

								$wp_user = get_user_by('email', $customer['email']);
								removeUserRole($wp_user);

								wp_send_json( array(    
									'message' => 'User role updated successfully.',
									'updated_user' => $wp_user
								), 200 );

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
					}
				}catch(Exception $e){
					wp_send_json_error( array(
						"event"=> "customer.subscription.deleted",
						"error"=> "We are unable to retrieve product with id $product_id to validate the sb_service setup is with this site from the stripe account, please create this user $customer_id manually.",
						"info" => "Please make sure your Billflow WordPress plugin has the correct Stripe API keys set.",
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
					$product_sb_tier = $product['metadata']['sb_tier'];
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
								servicebot_create_wp_user($customer, $product_sb_tier, $subscription['id']);
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
							"message" => "Subscription is not created with the sb_service $sb_service you configured in your Billflow Wordpress plugin. See Billflow docs for more info. If you continue to have this issue and you think everything is setup correctly, please contact Servicebot for more help!",
							"action" => "Validating the subscrption is created with a product with sb_service, this product must be the same as what is setup in your wordpress site's Billflow plugin settings.",
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
						"info" => "Please make sure your Billflow WordPress plugin has the correct Stripe API keys set.",
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