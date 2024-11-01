<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin-specific functions.
 *
 * @package    SmartSorting
 * @subpackage SmartSorting/admin
 * @author     SmartSorting Team <smartsprtingofficial@gmail.com>
 */
class Smart_Sorting_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string $smart_sorting The ID of this plugin.
     */
    private $smart_sorting;

    /**
     * The version of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param   string  $smart_sorting  The name of this plugin.
     * @param   string  $version        The version of this plugin.
     * @since   0.1.0
     */
    public function __construct( $smart_sorting, $version ) {

        $this->smart_sorting = $smart_sorting;
        $this->version       = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since   0.1.0
     */
    public function enqueue_styles() {

        wp_enqueue_style(
            $this->smart_sorting . '_admin_style',
            plugin_dir_url( __FILE__ ) . 'css/smart-sorting-admin.css',
            array(),
            false,
            'all'
        );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since   0.1.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script(
            $this->smart_sorting . '_admin_script',
            plugin_dir_url( __FILE__ ) . 'js/smart-sorting-admin.js',
            array( 'jquery' ),
            false,
            false
        );

    }

    /**
     * Creates a plugin section in the settings menu.
     *
     * @since   0.1.0
     */
    public function create_admin_menu() {
        add_options_page(
            __( 'Smart-sorting settings', 'smart-sorting' ),
            __( 'Smart-sorting settings', 'smart-sorting' ),
            'manage_options',
            'smart-sorting_settings',
            array( $this, 'load_menu_content' )
        );
    }

    /**
     * Adds product metadata that is used for sorting.
     *
     * @param integer $product_id ID of the product for which metadata are being added
     * @since   0.1.0
     */
    public function add_spv_metadata( $product_id ) {
        $params = array(
            'post_type'      => 'product',
            'numberposts'    => 1,
            'meta_key'       => 'total_sales',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        );
        $product_list = get_posts( $params );
        $min_spv = (float) get_post_meta(
            $product_list[0]->ID,
            'spv',
            true
        );

        $params = array(
            'post_type'      => 'product',
            'numberposts'    => 1,
            'meta_key'       => 'total_sales',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        );
        $product_list = get_posts( $params );
        $max_spv = (float) get_post_meta(
            $product_list[0]->ID,
            'spv',
            true
        );

        $middle_spv = ( $max_spv + $min_spv ) / 2;
        $spv_attributes = array(
            'spv_views',
            'spv_sales',
            'spv',
        );
        foreach ( $spv_attributes as $key ) {
            $value = get_post_meta( $product_id, $key, true );
            if ( '' == $value ) {
                if ( 'spv_views' == $key ) {
                    $value = 100;
                } elseif ( 'spv_sales' == $key ) {
                    $value = (int) ( $middle_spv * 100 );
                } elseif ( 'spv' == $key ) {
                    $value = $middle_spv;
                }
            }
            update_post_meta( $product_id, $key, $value );
        }
    }

    /**
     * When a product is deleted, deletes product data that plugin created.
     *
     * @param   integer $id ID of the deleting product
     * @since   0.1.0
     */
    public function delete_product_data( $id ) {
        $meta_list = array(
            'spv_views',
            'spv_sales',
            'spv',
        );
        foreach ( $meta_list as $meta ) {
            delete_metadata( 'product', $id, $meta);
        }
    }

    /**
     * Used to display the contents of the plugin section.
     *
     * @since   0.1.0
     */
    public function load_menu_content() {
        include plugin_dir_path( __FILE__ ) .
            'partials/smart-sorting-admin-display.php';
        show_smart_sorting_options();
    }

    /**
     * Creates fields for each plugin option.
     *
     * @since   0.1.0
     */
    public function smart_sorting_settings_fields() {
        register_setting(
            'smart-sorting_settings',
            'ss_views_delay',
            'absint'
        );

        add_settings_section(
            'section',
            '',
            '',
            'smart-sorting_settings'
        );

        add_settings_field(
            'ss_views_delay',
            __( 'Delay views', 'smart-sorting' ),
            array( $this, 'display_views_delay_field' ),
            'smart-sorting_settings',
            'section',
            array(
                'name' => 'ss_views_delay',
            )
        );
    }

    /**
     * Used to create a field for the delay views option.
     *
     * @param   array $args
     * @since   0.1.0
     */
    public function display_views_delay_field( $args ) {
        $option = get_option( $args['name'] );

        printf(
            '<input type="number" min="0" id="%s" name="%s" value="%d" />',
            esc_attr( $args['name'] ),
            esc_attr( $args['name'] ),
            absint( $option )
        );
    }
}
