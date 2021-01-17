<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       servicebot.io
 * @since      1.0.0
 *
 * @package    Servicebot
 * @subpackage Servicebot/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Servicebot
 * @subpackage Servicebot/admin
 * @author     Billflow <team@billflow.io>
 */
class Servicebot_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);
		add_action('admin_init', array( $this, 'registerAndBuildFields' ));

	}

	public function addPluginAdminMenu() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(  $this->plugin_name, 
			'Billflow', 
			'administrator', 
			$this->plugin_name, 
			array( $this, 'displayPluginAdminSettings' ), 
			plugin_dir_url( __DIR__ ) . 'img/billflow-white-gradient.png', 26 );
		
		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page( $this->plugin_name, 'Billflow Stripe Webhooks', 'Stripe Webhooks', 'administrator', $this->plugin_name.'-stripe-webhooks', array( $this, 'displayPluginAdminStripeWebhooks' ));

		// add roles settings page
		add_submenu_page( $this->plugin_name, 'Billflow Role & Tiers', 'Setup Roles', 'administrator', $this->plugin_name.'-setup-roles', array( $this, 'displayPluginAdminSetupRoles' ));
	}

	public function displayPluginAdminSettings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
			add_action('admin_notices', array($this,'servicebotSettingsMessages'));
			do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-display.php';
	}

	public function displayPluginAdminStripeWebhooks() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
			add_action('admin_notices', array($this,'servicebotSettingsMessages'));
			do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-stripe-webhooks.php';
	}

	public function displayPluginAdminSetupRoles() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
			add_action('admin_notices', array($this,'servicebotSettingsMessages'));
			do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-setup-roles.php';
	}

	public function ServicebotSettingsMessages($error_message){
		switch ($error_message) {
			case '1':
				$message = ( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.');
				$err_code = esc_attr( 'servicebot_example_setting' );
				$setting_field = 'servicebot_example_setting';
				break;
		}
		$type = 'error';
		add_settings_error(
			   $setting_field,
			   $err_code,
			   $message,
			   $type
		   );
	}

	public function registerAndBuildFields() {
		/**
	     * First, we add_settings_section. This is necessary since all future settings must belong to one.
	     * Second, add_settings_field
	     * Third, register_setting
	     */     
		add_settings_section(
			// ID used to identify this section and with which to register options
			'servicebot_general_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'servicebot_display_general_account' ),    
			// Page on which to add this section of options
			'servicebot_general_settings'                   
		);

		add_settings_section(
			// ID used to identify this section and with which to register options
			'servicebot_integration_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'servicebot_display_general_account' ),    
			// Page on which to add this section of options
			'servicebot_general_settings'                   
		);

		add_settings_section(
			// ID used to identify this section and with which to register options
			'billflow_roles_settings_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'servicebot_display_general_account' ),    
			// Page on which to add this section of options
			'billflow_roles_settings'                   
		);

		// Billflow secret key
		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_secret_key_global_setting',
			'name'		=> 'servicebot_servicebot_secret_key_global_setting',
			'required' 	=> 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option'
		);

		add_settings_field(
			'servicebot_servicebot_secret_key_global_setting',
			'<p><label>Billflow Secret Key</p></label>',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_general_settings',
			'servicebot_general_section',
			$args
		);

		register_setting(
			'servicebot_general_settings',
			'servicebot_servicebot_secret_key_global_setting'
		);

		// create WP user on signup?
		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'checkbox',
			'id'		=> 'servicebot_servicebot_create_user_global_setting',
			'name'		=> 'servicebot_servicebot_create_user_global_setting',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option',
			'required'  => false
		);

		add_settings_field(
			'servicebot_servicebot_create_user_global_setting',
			'<p><label>Do you want to create a WordPress user when a customer signs up via a Billflow embed?</label></p>',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_general_settings',
			'servicebot_integration_section',
			$args
		);

		register_setting(
			'servicebot_general_settings',
			'servicebot_servicebot_create_user_global_setting'
		);

		// Stripe webhooks section
		add_settings_section(
			// ID used to identify this section and with which to register options
			'servicebot_stripe_webhooks_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'servicebot_display_stripe_webhooks' ),    
			// Page on which to add this section of options
			'servicebot_stripe_webhooks_settings'                   
		);

		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_stripe_test_secret_key_setting',
			'name'		=> 'servicebot_servicebot_stripe_test_secret_key_setting',
			'required' 	=> 'false',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option'
		);
		add_settings_field(
			'servicebot_servicebot_stripe_test_secret_key_setting',
			'Stripe Test Secret Key',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);
		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_stripe_test_secret_key_setting'
		);

		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_stripe_live_secret_key_setting',
			'name'		=> 'servicebot_servicebot_stripe_live_secret_key_setting',
			'required' 	=> 'false',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option'
		);
		add_settings_field(
			'servicebot_servicebot_stripe_live_secret_key_setting',
			'Stripe Live Secret Key',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);
		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_stripe_live_secret_key_setting'
		);

		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_stripe_test_signing_secret_setting',
			'name'		=> 'servicebot_servicebot_stripe_test_signing_secret_setting',
			'required' 	=> 'false',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option'
		);
		add_settings_field(
			'servicebot_servicebot_stripe_test_signing_secret_setting',
			'Stripe Test Signing Secret',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);
		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_stripe_test_signing_secret_setting'
		);

		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_stripe_live_signing_secret_setting',
			'name'		=> 'servicebot_servicebot_stripe_live_signing_secret_setting',
			'required' 	=> 'false',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option'
		);
		add_settings_field(
			'servicebot_servicebot_stripe_live_signing_secret_setting',
			'Stripe Live Signing Secret',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);
		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_stripe_live_signing_secret_setting'
		);

		// Livemode toggle -- this should belong to webhooks, should webhook be listening to live mode or test mode
		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'checkbox',
			'id'		=> 'servicebot_servicebot_live_mode_global_setting',
			'name'		=> 'servicebot_servicebot_live_mode_global_setting',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option',
			'required' => false
		);

		add_settings_field(
			'servicebot_servicebot_live_mode_global_setting',
			'Webhook Live Mode (Uncheck for Test Mode)',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);

		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_live_mode_global_setting'
		);

		// Sb service for webhooks to match which subscription events it should listen to
		unset($args);
		$args = array (
			'type'		=> 'input',
			'subtype'	=> 'text',
			'id'		=> 'servicebot_servicebot_service_global_setting',
			'name'		=> 'servicebot_servicebot_service_global_setting',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' 	=> 'option',
			'required' => true
		);

		add_settings_field(
			'servicebot_servicebot_service_global_setting',
			'The sb_service for the webhook to listen to',
			array( $this, 'servicebot_render_settings_field' ),
			'servicebot_stripe_webhooks_settings',
			'servicebot_stripe_webhooks_section',
			$args
		);

		register_setting(
			'servicebot_stripe_webhooks_settings',
			'servicebot_servicebot_service_global_setting'
		);

		// Tier to roles map for webhook to update user roles

		$all_roles = wp_roles()->get_names();
		foreach($all_roles as $role_name){
			unset($args);
			$args = array (
				'type'		=> 'input',
				'subtype'	=> 'text',
				'id'		=> "billflow_role_to_tier_$role_name",
				'name'		=> "billflow_role_to_tier_$role_name",
				'get_options_list' => '',
				'value_type' => 'normal',
				'wp_data' 	=> 'option',
				'required' => false
			);

			add_settings_field(
				"billflow_role_to_tier_$role_name",
				$role_name,
				array( $this, 'servicebot_render_settings_field' ),
				'billflow_roles_settings',
				'billflow_roles_settings_section',
				$args
			);

			register_setting(
				'billflow_roles_settings',
				"billflow_role_to_tier_$role_name",
			);
		}
		
	}

	public function servicebot_display_general_account() {
		echo '<p></p>';
	} 

	public function servicebot_display_stripe_webhooks() {
		echo '<p></p>';
	} 

	public function servicebot_display_stripe_options_webhooks() {
		echo '<p></p>';
	}

	public function servicebot_render_settings_field($args) {
		/* EXAMPLE INPUT
				  'type'      => 'input',
				  'subtype'   => '',
				  'id'    => $this->plugin_name.'_example_setting',
				  'name'      => $this->plugin_name.'_example_setting',
				  'required' => 'required="required"',
				  'get_option_list' => "",
					'value_type' = serialized OR normal,
		'wp_data'=>(option or post_meta),
		'post_id' =>
		*/     
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}

		switch ($args['type']) {

			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if($args['subtype'] != 'checkbox'){
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
					$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
					$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
					if(isset($args['disabled'])){
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					} else {
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/

				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
				}
				break;
			default:
				# code...
				break;
		}
	}


	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/servicebot-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/servicebot-admin.js', array( 'jquery' ), $this->version, false );

	}

}
