<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       servicebot.io
 * @since      1.0.0
 *
 * @package    Servicebot
 * @subpackage Servicebot/admin/partials
 */

	$live_mode = get_option('servicebot_servicebot_live_mode_global_setting') == 1 ? true : false;
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<h2>Stripe Webhooks (Optional)</h2>  
		<!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php">  
		<section>
			<p>You can configure these keys to enable automatic creation of users for your Wordpress site whenever a Stripe customer is created.</p>
			<p>Not sure if you need this? <a href="https://docs.billflow.io/no-code-integrations/no-code-app-builders/wordpress#step-2-optionally-configure-stripe-webhooks" target="_blank">learn more about this feature here.</a></p>
			<hr/>
			<h3>Step 1</h3>
			<p>Add your Stripe webhook keys</p>
			<?php 
				settings_fields( 'servicebot_stripe_webhooks_settings' );
				do_settings_sections( 'servicebot_stripe_webhooks_settings' ); 
			?> 
			<hr/>
			<h3>Step 2</h3>
			<p> Go to <a target="_blank" href="https://dashboard.stripe.com/<?php echo $live_mode ? '' : 'test/'?>webhooks">https://dashboard.stripe.com/<?php echo $live_mode ? '' : 'test/'?>webhooks</a> to add the handler endpoint.</p>
            <p><i>You will need to configure this for both live and test mode in Stripe. 
            Your WordPress Integration is running in <?php echo $live_mode ? 'live' : 'test'?> mode now.</i></p>
			<ol>
				<li>Copy this into your clipboard <code><?php echo get_site_url(); ?>/servicebot/v1/stripe-hooks</code></li>
				<li>Setup <b>test</b> mode webhooks here: <a target="_blank" href="https://dashboard.stripe.com/test/webhooks">https://dashboard.stripe.com/test/webhooks</a></li>
				<li>Setup <b>live</b> mode webhooks here: <a target="_blank" href="https://dashboard.stripe.com/webhooks">https://dashboard.stripe.com/live/webhooks</a></li>
				<li>Click on the <i class="highlighted">Add endpoint</i> button</li>
				<li>Paste <i class="highlighted"><?php echo get_site_url(); ?>/servicebot/v1/stripe-hooks</i> into the Endpoint URL input box</li>
				<li>Select <i class="highlighted">customer.created</i> for the event to send input box.</li>
				<li>Click <i class="highlighted">Add endpoint</i> to save</li>
			</ol>
			<hr/>
			<h3>Step 3</h3>
			<p>Toggle webhooks to listen to Stripe live account when you are ready.</p>
			<?php 
				settings_fields( 'servicebot_stripe_webhooks_options_settings' );
				do_settings_sections( 'servicebot_stripe_webhooks_options_settings' ); 
			?> 
		</section>
		<hr/>
		<?php submit_button(); ?>  
	</form> 
</div>