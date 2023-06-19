<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://bellweather.agency/
 * @since             1.0.0
 * @package           SV_Api
 *
 * @wordpress-plugin
 * Plugin Name:       SV API
 * Plugin URI:        https://bellweather.agency/
 * Description:       Pulls listings from the Simpleview CRM API
 * Version:           1.0.0
 * Author:            Bellweather Agency
 * Author URI:        https://bellweather.agency/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sv-api
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
define( 'SV_API_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sv-api-activator.php
 */
function activate_sv_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sv-api-activator.php';
	SV_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sv-api-deactivator.php
 */
function deactivate_sv_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sv-api-deactivator.php';
	SV_Api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sv_api' );
register_deactivation_hook( __FILE__, 'deactivate_sv_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sv-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sv_api() {	
	$plugin = new SV_Api();
	$plugin->run();
}

use SV\API;

run_sv_api();
