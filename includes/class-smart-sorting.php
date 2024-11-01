<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 * @author      SmartSorting Team <smartsprtingofficial@gmail.com>
 */
class Smart_Sorting {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Smart_Sorting_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $smart_sorting The string used to uniquely identify this plugin.
	 */
	protected $smart_sorting;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since   0.1.0
	 */
	public function __construct() {
		if ( defined( 'SMART_SORTING_VERSION' ) ) {
			$this->version = SMART_SORTING_VERSION;
		} else {
			$this->version = '0.1.2';
		}
		$this->smart_sorting = 'smart-sorting';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smart_Sorting_Loader. Orchestrates the hooks of the plugin.
	 * - Smart_Sorting_i18n. Defines internationalization functionality.
	 * - Smart_Sorting_Admin. Defines all hooks for the admin area.
	 * - Smart_Sorting_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'includes/class-smart-sorting-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'includes/class-smart-sorting-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'admin/class-smart-sorting-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'public/class-smart-sorting-public.php';

		$this->loader = new Smart_Sorting_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smart_Sorting_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smart_Sorting_i18n();

		$this->loader->add_action(
            'plugins_loaded',
            $plugin_i18n,
            'load_plugin_smart_sorting'
        );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Smart_Sorting_Admin(
            $this->get_smart_sorting(),
            $this->get_version()
        );

		$this->loader->add_action(
            'admin_enqueue_scripts',
            $plugin_admin,
            'enqueue_styles'
        );
		$this->loader->add_action(
            'admin_enqueue_scripts',
            $plugin_admin,
            'enqueue_scripts'
        );
        $this->loader->add_action(
            'admin_menu',
            $plugin_admin,
            'create_admin_menu'
        );
        $this->loader->add_action(
            'admin_init',
            $plugin_admin,
            'smart_sorting_settings_fields'
        );
        $this->loader->add_action(
            'woocommerce_update_product',
            $plugin_admin,
            'add_spv_metadata'
        );
        $this->loader->add_action(
            'woocommerce_before_delete_product',
            $plugin_admin,
            'delete_product_data'
        );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smart_Sorting_Public(
            $this->get_smart_sorting(),
            $this->get_version()
        );

		$this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public,
            'enqueue_styles'
        );
		$this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public,
            'enqueue_scripts'
        );
        $this->loader->add_action(
            'woocommerce_recorded_sales',
            $plugin_public,
            'track_total_sales'
        );
        $this->loader->add_action(
            'woocommerce_after_order_details',
            $plugin_public,
            'update_spv_value'
        );
        $this->loader->add_action(
            'wp_ajax_add_view',
            $plugin_public,
            'ajax_add_view'
        );

        $this->loader->add_filter(
            'woocommerce_get_catalog_ordering_args',
            $plugin_public,
            'get_smartsorting_ordering_args',
        );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_smart_sorting() {
		return $this->smart_sorting;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Smart_Sorting_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
