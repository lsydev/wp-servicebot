<?php
/*
Plugin Name: Servicebot Subscription Portal Widget Plugin
Plugin URI: http://www.wpexplorer.com/servicebot/
Description: This plugin adds a servicebot subscription portal embed widget.
Version: 1.0
Author: Servicebot
Author URI: https://servicebot.io
License: GPL2
*/

// The widget class
class Servicebot_Subscription_Portal_Widget extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'servicebot_subscription_portal_widget',
			__( 'Servicebot Subscription Portal Widget', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
        );

        $this->global_values = array(
            'servicebot_id' => get_option('servicebot_servicebot_id_global_setting'),
            'servicebot_id_live' => get_option('servicebot_servicebot_id_live_global_setting'),
            'secret_key' => get_option('servicebot_servicebot_secret_key_global_setting'),
            'service' => get_option('servicebot_servicebot_service_global_setting'),
            'create_user' => get_option('servicebot_servicebot_create_user_global_setting'),
            'login_redirect_url' => get_option('servicebot_servicebot_login_redirect_url_global_setting'),
        );

        $this->livemode = get_option('servicebot_servicebot_live_mode_global_setting') == 1 ? true : false;
        $this->servicebot_id = $this->livemode ? $this->global_values['servicebot_id_live'] : $this->global_values['servicebot_id'];
        $this->secret_key = $this->global_values['secret_key'];
    }

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
            'title'           => '',
            'service'         => '',
            'email'           => '',
            'customer_id'     => '',
            'subscription_id' => '',
            'coupon'          => '',
            'embed_options'   => '',
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
                <td>Servicebot Id</td>
                <td><?php print($this->servicebot_id); ?></td>
            </tr>
            <tr>
                <td>Secret Key</td>
                <td><?php print(($this->secret_key) ? "Set!" : "<a href='/wp-admin/admin.php?page=servicebot'>Settings</a>"); ?></td>
            </tr>
            <tr>
                <td>Service</td>
                <td><?php print($this->global_values['service']); ?></td>
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title (Optional)', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'service' ) ); ?>"><?php _e( 'Sb Service', 'text_domain' ); ?></label>
			<input required class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'service' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'service' ) ); ?>" type="text" value="<?php echo esc_attr( $service ? $service : $this->global_values['service'] ); ?>" />
		</p>
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
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'coupon' ) ); ?>"><?php _e( 'Pre-applied Coupon (Optional)', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'coupon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'coupon' ) ); ?>" type="text" value="<?php echo esc_attr( $coupon ); ?>" />
		</p>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'embed_options' ) ); ?>"><?php _e( 'Embed Options JSON', 'text_domain' ); ?></label>
			<textarea rows="10" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'embed_options' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'embed_options' ) ); ?>"><?php echo esc_attr($embed_options); ?></textarea>
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
        $instance['title']          = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        $instance['service']        = isset( $new_instance['service'] ) ? wp_strip_all_tags( $new_instance['service'] ) : '';
        $instance['email']          = isset( $new_instance['email'] ) ? wp_strip_all_tags( $new_instance['email'] ) : '';
        $instance['customer_id']    = isset( $new_instance['customer_id'] ) ? wp_strip_all_tags( $new_instance['customer_id'] ) : '';
        $instance['subscription_id']        = isset( $new_instance['subscription_id'] ) ? wp_strip_all_tags( $new_instance['subscription_id'] ) : '';
        $instance['coupon']                 = isset( $new_instance['coupon'] ) ? wp_strip_all_tags( $new_instance['coupon'] ) : '';
        $instance['embed_options']          = isset( $new_instance['embed_options'] ) ? wp_strip_all_tags( $new_instance['embed_options'] ) : '';
        $instance['create_user']            = isset( $new_instance['create_user'] ) ? wp_strip_all_tags( $new_instance['create_user'] ) : '';
        $instance['sb_login_redirect_url']    = isset( $new_instance['sb_login_redirect_url'] ) ? wp_strip_all_tags( $new_instance['sb_login_redirect_url'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {

		extract( $args );

        // Check the widget options
        $billing_page_id           = isset( $instance['billing_page_id'] ) ? apply_filters( 'widget_billing_page_id', $instance['billing_page_id'] ) : '';
        $title           = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $servicebot_id   = $this->servicebot_id;
        $sb_secret       = $this->secret_key;
        $service         = isset( $instance['service'] ) ? apply_filters( 'widget_service', $instance['service'] ) : $this->global_values['service'];
        $email           = isset( $instance['email'] ) ? apply_filters( 'widget_email', $instance['email'] ) : '';
        $customer_id     = isset( $instance['customer_id'] ) ? apply_filters( 'widget_customer_id', $instance['customer_id'] ) : '';
        $subscription_id = isset( $instance['subscription_id'] ) ? apply_filters( 'widget_subscription_id', $instance['subscription_id'] ) : '';
        $coupon          = isset( $instance['coupon'] ) ? apply_filters( 'widget_coupon', $instance['coupon'] ) : '';
        $embed_options   = isset( $instance['embed_options'] ) ? apply_filters( 'embed_options', $instance['embed_options'] ) : '';
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
        
        //Add 
        $decoded_options = json_decode($embed_options, true);
        if($decoded_options){
            if($decoded_options['behavior']){
                $decoded_options['behavior']['signup']['promptPassword'] = $default_options['behavior']['signup']['promptPassword'];
            }else{
                $decoded_options['behavior'] = $default_options['behavior'];
            }
            $encoded_options = json_encode($decoded_options);
        }else{
            $encoded_options = json_encode($default_options);
        }
     
        // Display widget title if defined
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

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
        wp_enqueue_script( 'servicebot_subscription_portal_widget', 
                            plugin_dir_url( __FILE__ ) . 'js/servicebot-subscription-portal-widget.js',
                            array(),
                            $js_version,
                            true
                         );
    
        wp_localize_script( 'servicebot_subscription_portal_widget', 
                            'php_props_sp_widget', 
                            array(
                                'billing_page_id' => $billing_page_id,
                                'livemode'        => $this->livemode,
                                'servicebot_id'   => $this->servicebot_id,
                                'hash'            => isset($hash) ? $hash : '',
                                'service'         => $service,
                                'email'           => $logged_in_email ? $logged_in_email : $email,
                                'customer_id'     => $customer_id,
                                'subscription_id' => $subscription_id,
                                'coupon'          => $coupon,
                                'options'         => $encoded_options,
                                'create_user'     => $create_user ? true : false,
                                'is_logged_in'    => $logged_in_email ? true : false,
                                'logged_in_email' => $logged_in_email,
                                'login_redirect_url' => $login_url,
                                'admin_ajax_url'  => $admin_ajax_url,
                                'widget'          => 'servicebo-subscription-portal-widget',
                                'embed_type'      => 'portal',
                                'js_version'      => $js_version
                            )
                          );

        // Test handle response function hook
        wp_enqueue_script( 'servicebot_handle_response_js', 
                            plugin_dir_url( __FILE__ ) . 'js/servicebot-handle-response.js',
                            array('servicebot_subscription_portal_widget'),
                            null,
                            true
                         );

		// WordPress core after_widget hook (always include )
		echo $after_widget;

    }

    public function get_script_depends() {
        return [ 'servicebot_subscription_portal_widget' ];
    }
    
}

// Register the widget
function servicebot_register_subscription_portal_widget() {
	register_widget( 'Servicebot_Subscription_Portal_Widget' );
}
add_action( 'widgets_init', 'servicebot_register_subscription_portal_widget' );

// Add shortcode for the widget
function shortcode_servicebot_subscription_portal_widget($params = array()) {

    // default parameters
    extract(shortcode_atts(array(
        'title' => 'Subscription Portal',
        'id'    => 'servicebot_subscription_portal_shotcode',
        'depth' => 2
    ), $params));

    /*
    * @note: for backward compatibility: allow overriding widget args through the shortcode parameters
    */
    // $widget_args = shortcode_atts( array(
    //     'before_widget' => '<' . $container_tag . ' id="' . $container_id . '" class="' . $container_class . '">',
    //     'before_title' => '<' . $title_tag . ' class="' . $title_class . '">',
    //     'after_title' => '</' . $title_tag . '>',
    //     'after_widget' => '</' . $container_tag . '>',
    // ), $params );
    // extract( $widget_args );
  

    ob_start();
    echo '<!-- Widget Shortcode -->';
    the_widget( 'Servicebot_Subscription_Portal_Widget', $params , array() );
    echo '<!-- /Widget Shortcode -->';
    $content = ob_get_clean();

    // if ( $echo !== true )
    //     return $content;

    echo $content;
}

add_shortcode('servicebot_subscription_portal_shortcode', 'shortcode_servicebot_subscription_portal_widget');