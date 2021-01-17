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

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<h2>Setup Roles</h2>  
		<!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php">  
		<section>
			<p>You can map your user roles to your sb_tier setup here.</p>
			<hr/>
			<h3>Step 1</h3>
			<p>Enter the sb_tier name for the roles you'd like to add automatically when a subscription is created / updated.</p>
			<?php 
				settings_fields( 'billflow_roles_settings' );
				do_settings_sections( 'billflow_roles_settings' ); 
			?> 
		</section>
		<hr/>
		<?php submit_button(); ?>  
	</form> 
</div>