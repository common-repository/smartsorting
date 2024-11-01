<?php

/**
 *
 * @license           https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since             0.1.0
 *
 * @package           SmartSorting
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Sorting for WooCommerce
 * Description:       Algorithm for changing the sorting of the output of goods. Smart sorting is an easy way to increase sales and user experience. With any set of products in your online store, it is important to show the user first of all those products that they are most likely to buy. To do this, our plugin collects information about the number of views and sales of each product and re-sorts the products in your store using a simple formula for sales per views.
 * Version:           0.1.2
 * Author:            SmartSorting
 * License:           LGPL-3.0+
 * License URI:       https://www.gnu.org/licenses/lgpl-3.0.txt
 * Text Domain:       smart-sorting
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'SMART_SORTING_VERSION', '0.1.2' );

/**
 * The code that runs during plugin activation.
 */
function activate_smart_sorting() {
	require_once plugin_dir_path( __FILE__ ) .
        'includes/class-smart-sorting-activator.php';
	Smart_Sorting_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smart_sorting() {
	require_once plugin_dir_path( __FILE__ ) .
        'includes/class-smart-sorting-deactivator.php';
	Smart_Sorting_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smart_sorting' );
register_deactivation_hook( __FILE__, 'deactivate_smart_sorting' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smart-sorting.php';

/**
 * Begins execution of the plugin.
 *
 * @since    0.1.0
 */
function run_smart_sorting() {

	$plugin = new Smart_Sorting();
	$plugin->run();

}
run_smart_sorting();
