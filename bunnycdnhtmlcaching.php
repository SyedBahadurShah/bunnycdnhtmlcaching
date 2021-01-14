<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.gulshankumar.net/about/
 * @since             1.0.0
 * @package           Bunnycdnhtmlcaching
 *
 * @wordpress-plugin
 * Plugin Name:       BunnyCDN HTML Caching
 * Plugin URI:        https://www.gulshankumar.net/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Gulshan Kumar
 * Author URI:        https://www.gulshankumar.net/about/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bunnycdnhtmlcaching
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BUNNYCDNHTMLCACHING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bunnycdnhtmlcaching-activator.php
 */
function activate_bunnycdnhtmlcaching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bunnycdnhtmlcaching-activator.php';
	Bunnycdnhtmlcaching_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bunnycdnhtmlcaching-deactivator.php
 */
function deactivate_bunnycdnhtmlcaching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bunnycdnhtmlcaching-deactivator.php';
	Bunnycdnhtmlcaching_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bunnycdnhtmlcaching' );
register_deactivation_hook( __FILE__, 'deactivate_bunnycdnhtmlcaching' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bunnycdnhtmlcaching.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bunnycdnhtmlcaching() {

	$plugin = new Bunnycdnhtmlcaching();
	$plugin->run();

}
run_bunnycdnhtmlcaching();
