<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       billflow.io
 * @since      1.0.0
 *
 * @package    Servicebot
 * @subpackage Servicebot/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<style>
	.bfwp-admin-box {
		font-size: 14px;
		display: flex; background: white; border-radius: 5px; margin-bottom: 2em;
	}
	.bfwp-admin-box.layout-col{
		flex-direction: column;
	}
	.bfwp-admin-box.padded{
		padding: 3em;
	}
	.bfwp-button{
		font-size: 14px;
		padding: 1em 2em;
		border-radius: 5px;
		text-decoration: none;
		white-space: nowrap;
	}
	.bfwp-button.primary{
		background-color: blue;
		color: white;
	}
	.banner-right{
		padding: 30px 100px;
	}
	@media only screen and (max-width: 1300px) {
		.bfwp-admin-box{
			font-size: 12px;
		}
	}
	@media only screen and (max-width: 1200px) {
		.bfwp-admin-box{
			font-size: 10px;
		}
	}
	@media only screen and (max-width: 600px) {
		.bfwp-admin-box{
			flex-direction: column;
			font-size: 10px;
		}
		.banner-right{
			padding: 30px 30px;
		}
	}
</style>

<?php
	$servicebot_secret_key = get_option('servicebot_servicebot_secret_key_global_setting');
?>

<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<div class="bfwp-admin-box" style="<?php if($servicebot_secret_key ) echo "display: none;";?>">
		<div class="banner-left" style="">
			<img style="max-width: 100%;" src="<?php echo plugin_dir_url( __DIR__ )?>img/signup-banner-icon.png"/>
		</div>
		<div class="banner-right" style="flex-basis: 70%; justify-content: space-evenly; display: flex; flex-direction: column; ">
			<div>
				<img style="max-width: 170px;" src="<?php echo plugin_dir_url( __DIR__ )?>img/stripe-verified-partner-logo.png"/>
			</div>
			<div>
				<p style="font-size: 2em; max-width: 330px; padding-bottom: 0.8em; margin: 0.4em 0;">Build your Stripe billing flow with no code in minutes</p>
			</div>
			<div>
				<a class="bfwp-button primary" href="https://dashboard.billflow.io/signup?ref=wp-plugin-signup-banner">Create a Billflow Account</a>
			</div>
			<div>
				<a class="bfwp-link" href="#servicebot_servicebot_secret_key_global_setting">I already have an account</a>
			</div>
		</div>
	</div>
	<div class="bfwp-admin-box layout-col padded">
		<h2>Billflow Settings</h2>
		<p>Setup your Billflow integration</p>
		<hr/>
		<h3>Step 1</h3>
		<p>Add the follow settings, you can find the secret key in your <a href="https://dashboard.billflow.io/integrations" target="_blank">Billflow Dashboard</a>'s integrations page.</p>
		<!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
		<?php settings_errors(); ?>  
		<form method="POST" action="options.php">  
			<section>
				<p>
					<?php 
						settings_fields( 'servicebot_general_settings' );
						do_settings_fields('servicebot_general_settings', 'servicebot_general_section' ); 
					?>
				</p>
			</section>
			<hr/>
			<h3>Step 2</h3>
			<p>Setup your integration options.</p>
			<section>
				<p>
					<?php 
						do_settings_fields('servicebot_general_settings', 'servicebot_integration_section' ); 
					?>
				</p>
			</section>
			<hr/>
			<?php submit_button(); ?>  
		</form> 
	</div>
</div>