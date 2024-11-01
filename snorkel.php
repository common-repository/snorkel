<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Snorkel
 *
 * @wordpress-plugin
 * Plugin Name: Snorkel
 * Plugin URI: https://www.snorkelapp.com/wordpress
 * Description: Uncover valuable insights in visitor interactions, see patterns in customer behaviour and make informed decisions to increase your ecommerce sales. 
 * Version: 1.0
 * Author: The Snorkel Team
 * Author URI: http://snorkelapp.com/
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       snorkel
 * Domain Path:       /languages
 */
global $snorkel_api_url;
$snorkel_api_url = 'https://disco-olark.herokuapp.com';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SNORKEL_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-snorkel-activator.php
 */
function activate_snorkel() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-snorkel-activator.php';
    global $snorkel_api_url;
	Snorkel_Activator::activate($snorkel_api_url);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-snorkel-deactivator.php
 */
function deactivate_snorkel() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-snorkel-deactivator.php';
	Snorkel_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_snorkel' );
register_deactivation_hook( __FILE__, 'deactivate_snorkel' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-snorkel.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_snorkel() {
    global $snorkel_api_url;
	$plugin = new Snorkel($snorkel_api_url);
	$plugin->run();

}
run_snorkel();
