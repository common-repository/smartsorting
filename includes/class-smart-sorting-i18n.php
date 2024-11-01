<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 * @author      SmartSorting Team <smartsprtingofficial@gmail.com>
 */
class Smart_Sorting_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_smart_sorting() {

		load_plugin_textdomain(
			'smart-sorting',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
