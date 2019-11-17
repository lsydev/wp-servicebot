<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       servicebot.io
 * @since      1.0.0
 *
 * @package    Servicebot_plugins
 * @subpackage Servicebot_plugins/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Servicebot_plugins
 * @subpackage Servicebot_plugins/public
 * @author     Servicebot Inc. <lung@servicebot.io>
 */
class Servicebot_plugins_Public {

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
		 * defined in Servicebot_plugins_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Servicebot_plugins_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/servicebot_plugins-public.css', array(), $this->version, 'all' );

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
		 * defined in Servicebot_plugins_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Servicebot_plugins_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/servicebot_plugins-public.js', array( 'jquery' ), $this->version, false );

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
		wp_send_json( array(    'user_id' => $user_id,
								'email' => $email,
								'name' => $name,
								'password' => '*****',
								'message' => 'User created successfully.'
					), 200 );
		wp_new_user_notification( $user_id, null, 'both');
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
