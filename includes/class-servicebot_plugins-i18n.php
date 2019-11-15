<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       servicebot.io
 * @since      1.0.0
 *
 * @package    Servicebot_plugins
 * @subpackage Servicebot_plugins/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Servicebot_plugins
 * @subpackage Servicebot_plugins/includes
 * @author     Servicebot Inc. <lung@servicebot.io>
 */
class Servicebot_plugins_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'servicebot_plugins',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
