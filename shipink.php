<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://shipink.io
 * @since             1.5.0
 * @package           Shipink
 *
 * @wordpress-plugin
 * Plugin Name:       Shipink
 * Plugin URI:        https://shipink.io
 * Description:       Shipink is a new and innovative way for e-commerce companies to easily integrate and use the shipping companies they want to work with.

 * Version:           1.5.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       shipink
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
define( 'SHIPINK_VERSION', '1.5.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shipink-activator.php
 */
function activate_shipink() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipink-activator.php';
	Shipink_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shipink-deactivator.php
 */
function deactivate_shipink() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipink-deactivator.php';
	Shipink_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_shipink' );
register_deactivation_hook( __FILE__, 'deactivate_shipink' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shipink.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.5.0
 */
function run_shipink() {

	$plugin = new Shipink();
	$plugin->run();

}
run_shipink();
