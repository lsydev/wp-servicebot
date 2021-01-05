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
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<h2>Billflow Settings</h2>
	<p>Setup your Billflow integration</p>
	<hr/>
	<h3>Step 1</h3>
	<p>Add the follow settings, you can find the secret key in your <a href="https://dashboard.billflow.io/integrations" target="_blank">Billflow Dashboard</a>'s integrations page.</p>
	<!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php">  
		<section>
		<?php 
			settings_fields( 'servicebot_general_settings' );
			do_settings_sections( 'servicebot_general_settings' ); 
		?>
		</section>
		<hr/>
		<h3>Step 2</h3>
		<p>Setup your integration options.</p>
		<section>
		<?php 
			settings_fields( 'servicebot_integration_settings' );
			do_settings_sections( 'servicebot_integration_settings' ); 
		?>
		</section>
		<hr/>
		<?php submit_button(); ?>  
	</form> 
</div>