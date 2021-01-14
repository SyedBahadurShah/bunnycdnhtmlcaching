<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.gulshankumar.net/about/
 * @since      1.0.0
 *
 * @package    Bunnycdnhtmlcaching
 * @subpackage Bunnycdnhtmlcaching/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Bunnycdnhtmlcaching
 * @subpackage Bunnycdnhtmlcaching/includes
 * @author     Gulshan Kumar <admin@gulshankumar.net>
 */
class Bunnycdnhtmlcaching_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bunnycdnhtmlcaching',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
