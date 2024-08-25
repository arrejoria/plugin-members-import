<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/arr-dev
 * @since             1.0.0
 * @package           Import_Members
 *
 * @wordpress-plugin
 * Plugin Name:       Import Members
 * Plugin URI:        https://boldt.com.ar
 * Description:       Este plugin registrará una lista de socios mediante archivo CSV en la base de datos.
 * Version:           1.0.0
 * Author:            Performance Team
 * Author URI:        https://www.linkedin.com/in/arr-dev/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       import-members
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
define( 'IMPORT_MEMBERS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-import-members-activator.php
 */
function activate_import_members() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-import-members-activator.php';
	Import_Members_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-import-members-deactivator.php
 */
function deactivate_import_members() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-import-members-deactivator.php';
	Import_Members_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_import_members' );
register_deactivation_hook( __FILE__, 'deactivate_import_members' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-import-members.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_import_members() {

	$plugin = new Import_Members();
	$plugin->run();

}

run_import_members();
