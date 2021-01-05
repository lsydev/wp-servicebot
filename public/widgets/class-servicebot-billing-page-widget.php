<?php
/*
Plugin Name: Billflow Billing Page Widget Plugin
Plugin URI: http://www.wpexplorer.com/servicebot/
Description: This plugin adds a Billflow billing page embed widget.
Version: 1.0
Author: Billflow
Author URI: https://servicebot.io
License: GPL2
*/

// The widget class
class Servicebot_Billing_Page_Widget extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'servicebot_Billing_Page_widget',
			__( 'Billflow Billing Page Widget', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
        );

        $this->global_values = array(
            'secret_key' => get_option('servicebot_servicebot_secret_key_global_setting'),
            'create_user' => get_option('servicebot_servicebot_create_user_global_setting'),
            'login_redirect_url' => get_option('servicebot_servicebot_login_redirect_url_global_setting'),
        );

        $this->livemode = get_option('servicebot_servicebot_live_mode_global_setting') == 1 ? true : false;
        $this->secret_key = $this->global_values['secret_key'];
    }

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
            'title'           => '',
            'email'           => '',
            'customer_id'     => '',
            'subscription_id' => '',
            'create_user'     => '',
            'sb_login_redirect_url' => '',
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
        <h3><b>Global Settings</b></h3>
        <table>
            <tr>
                <td>Mode</td>
                <td><?php print($this->livemode ? 'Live Mode' : 'Test Mode'); ?></td>
            </tr>
            <tr>
                <td>Secret Key</td>
                <td><?php print(($this->secret_key) ? "Set!" : "<a href='/wp-admin/admin.php?page=servicebot'>Settings</a>"); ?></td>
            </tr>
            <tr>
                <td>Create User?</td>
                <td><?php print((!!$this->global_values['create_user']) ? 'Yes' : 'No'); ?></td>
            </tr>
            <tr>
                <td>Login Redirect URL</td>
                <td><?php print($this->global_values['login_redirect_url']); ?></td>
            </tr>
        </table>

        <b>Configure Embed</b>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>"><?php _e( 'Customer Email (Optional)', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'email' ) ); ?>" type="text" value="<?php echo esc_attr( $email ); ?>" />
		</p>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'customer_id' ) ); ?>"><?php _e( 'Customer ID (Optional)', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'customer_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'customer_id' ) ); ?>" type="text" value="<?php echo esc_attr( $customer_id ); ?>" />
		</p>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subscription_id' ) ); ?>"><?php _e( 'Subscription ID (Optional)', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subscription_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subscription_id' ) ); ?>" type="text" value="<?php echo esc_attr( $subscription_id ); ?>" />
		</p>
        
        <b>Configure Behavior</b>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'create_user' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'create_user' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $create_user ? $create_user : (!!$this->global_values['create_user'])); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'create_user' ) ); ?>"><?php _e( 'Create WordPress user on signup', 'text_domain' ); ?></label>
		</p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'sb_login_redirect_url' ) ); ?>"><?php _e( 'Servicebot Embedded URL', 'text_domain' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'sb_login_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sb_login_redirect_url' ) ); ?>" type="text" value="<?php echo esc_attr( $sb_login_redirect_url ? $sb_login_redirect_url : $this->global_values['login_redirect_url'] ); ?>" />
            <span>The url of the page where you embedded this Servicebot embed</span>
		</p>
	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['billing_page_id'] = isset( $new_instance['billing_page_id'] ) ? wp_strip_all_tags( $new_instance['billing_page_id'] ) : '';
        $instance['email']          = isset( $new_instance['email'] ) ? wp_strip_all_tags( $new_instance['email'] ) : '';
        $instance['customer_id']    = isset( $new_instance['customer_id'] ) ? wp_strip_all_tags( $new_instance['customer_id'] ) : '';
        $instance['subscription_id']        = isset( $new_instance['subscription_id'] ) ? wp_strip_all_tags( $new_instance['subscription_id'] ) : '';
        $instance['create_user']            = isset( $new_instance['create_user'] ) ? wp_strip_all_tags( $new_instance['create_user'] ) : '';
        $instance['sb_login_redirect_url']    = isset( $new_instance['sb_login_redirect_url'] ) ? wp_strip_all_tags( $new_instance['sb_login_redirect_url'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {

        extract( $args );

        // Check the widget options
        $billing_page_id = isset( $instance['billing_page_id'] ) ? apply_filters( 'widget_billing_page_id', $instance['billing_page_id'] ) : '';
        $sb_secret       = $this->secret_key;
        $email           = isset( $instance['email'] ) ? apply_filters( 'widget_email', $instance['email'] ) : '';
        $customer_id     = isset( $instance['customer_id'] ) ? apply_filters( 'widget_customer_id', $instance['customer_id'] ) : '';
        $subscription_id = isset( $instance['subscription_id'] ) ? apply_filters( 'widget_subscription_id', $instance['subscription_id'] ) : '';
        $create_user     = isset( $instance['create_user'] ) ? apply_filters( 'create_user', $instance['create_user'] ) : (!!$this->global_values['create_user']);
        $sb_login_redirect_url     = isset( $instance['sb_login_redirect_url'] ) ? apply_filters( 'sb_login_redirect_url', $instance['sb_login_redirect_url'] ) : $this->global_values['login_redirect_url'];

        // Get Wordpress data
        $logged_in_user = wp_get_current_user();
        $logged_in_email = $logged_in_user->user_email;
        $login_url = wp_login_url($sb_login_redirect_url);
        $admin_ajax_url = admin_url("admin-ajax.php");

        if($sb_secret && $logged_in_email){
            $hash = hash_hmac(
                'sha256',
                isset($logged_in_email) ? $logged_in_email : $email,
                $sb_secret
            );
        }

        wp_localize_script( 'wp-api', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );
        wp_enqueue_script( 'wp-api' );

		// WordPress core before_widget hook (always include )
        echo $before_widget;
        
        //Create options from WP widget inputs
        $default_options = ['behavior' => ['signup' => ['promptPassword' => $create_user ? true : false]]];
        $encoded_options = json_encode($default_options);

        // Display the widget
        $render_div = '<div class="widget-text wp_widget_plugin_box">
                            <div id="servicebot-subscription-portal"></div>
                       </div>';

        print($render_div);

        if ( defined( 'SERVICEBOT_VERSION' ) ) {
            $js_version = SERVICEBOT_VERSION;
        } else {
            $js_version = '1.0.0';
        }
        wp_enqueue_script( 'billflow_billing_page_widget', 
                            plugin_dir_url( __FILE__ ) . 'js/billflow-widget.js',
                            array(),
                            $js_version,
                            true
                         );
        

        $js_settings = array(
            'billing_page_id' => $billing_page_id,
            'hash'            => isset($hash) ? $hash : '',
            'email'           => $logged_in_email ? $logged_in_email : $email,
            'customer_id'     => $customer_id,
            'subscription_id' => $subscription_id,
            'options'         => $encoded_options,
            'create_user'     => $create_user ? true : false,
            'is_logged_in'    => $logged_in_email ? true : false,
            'logged_in_email' => $logged_in_email,
            'login_redirect_url' => $login_url,
            'admin_ajax_url'  => $admin_ajax_url,
            'widget'          => 'billflow-billing-page-widget',
            'embed_type'      => 'billing_page',
            'js_version'      => $js_version
        );

        // print_r($js_settings);

        wp_localize_script( 'billflow_billing_page_widget', 
                            'php_props_billflow_settings', 
                            $js_settings
                          );

        // Test handle response function hook
        wp_enqueue_script( 'servicebot_handle_response_js', 
                            plugin_dir_url( __FILE__ ) . 'js/servicebot-handle-response.js',
                            array('billflow_billing_page_widget'),
                            null,
                            true
                         );

		// WordPress core after_widget hook (always include )
		echo $after_widget;

    }

    public function get_script_depends() {
        return [ 'billflow_billing_page_widget' ];
    }
    
}

// Register the widget
function servicebot_register_billing_page_widget() {
	register_widget( 'Servicebot_Billing_Page_Widget' );
}
add_action( 'widgets_init', 'servicebot_register_billing_page_widget' );

// Add shortcode for the widget
function shortcode_servicebot_billing_page_widget($params = array()) {

    // print_r($params);

    // default parameters
    extract(shortcode_atts(array(
        'title' => 'Billing Page',
        'id'    => 'billflow_shortcode',
        'billing_page_id' => 'billing_page_id',
        'depth' => 2
    ), $params));

    /*
    * @note: for backward compatibility: allow overriding widget args through the shortcode parameters
    */
    $widget_args = shortcode_atts( array(
        'before_widget' => '<' . $container_tag . ' id="' . $container_id . '" class="' . $container_class . '">',
        'before_title' => '<' . $title_tag . ' class="' . $title_class . '">',
        'after_title' => '</' . $title_tag . '>',
        'after_widget' => '</' . $container_tag . '>',
    ), $params );
    extract( $widget_args );
  
    // print_r($widget_args);

    ob_start();
    echo '<!-- Widget Shortcode -->';
    the_widget( 'Servicebot_Billing_Page_Widget', $params , array() );
    echo '<!-- /Widget Shortcode -->';
    $content = ob_get_clean();

    if ( $echo !== true )
        return $content;

    echo $content;
}

// add_shortcode('servicebot_billing_page_shortcode', 'shortcode_servicebot_billing_page_widget');
add_shortcode('billflow', 'shortcode_servicebot_billing_page_widget');