<?php

/**
 * Fired during plugin activation.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/includes
 * @author      SmartSorting Team <smartsprtingofficial@gmail.com>
 */

class Smart_Sorting_Activator {

    /**
     * For all created products, adds metadata that is used for sorting.
     *
     * @since   0.1.0
     */
    private static function add_spv_field() {

        $posts_meta = array(
            'spv_views',
            'spv_sales',
            'spv',
        );

        $params = array(
            'post_type'      => 'product',
            'meta_key'       => 'total_sales',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        );

        $product_query = new WP_Query( $params );

        if ( $product_query->have_posts() ) {
            $fl = false;
            $max_sales = 0;
            while ( $product_query->have_posts() ) {
                $product_query->the_post();
                $id = $product_query->post->ID;
                if ( ! $fl ) {
                    $max_sales = (float) get_post_meta(
                        $id,
                        'total_sales',
                        true
                    );
                    $fl = true;
                }
                $views = 0;
                $sales = 0;
                foreach ( $posts_meta as $key ) {
                    $value = get_post_meta( $id, $key, true );
                    if ( 'spv_views' == $key ) {
                        if( '' == $value ) {
                            $value = 100;
                        }
                        $views = $value;
                    } elseif ( 'spv_sales' == $key ) {
                        if( '' == $value ) {
                            if ( 0 == $max_sales ) {
                                $value = 0;
                            } else {
                                $value = (float) get_post_meta($id, 'total_sales', true);
                                $value = (int)(((($value) / $max_sales) *
                                        0.5 + 0.5) * 100);
                            }
                        }
                        $sales = $value;
                    } elseif ( 'spv' == $key ) {
                        $value = (float) $sales / (float) $views;
                    }
                    update_post_meta( $id, $key, $value );
                }
            }
            wp_reset_postdata();
        }
    }

    /**
     * Creates a table that stores all user views.
     *
     * @since   0.1.0
     */
    private static function add_views_tracking_table() {
        global $wpdb;
        $wpdb->query(
            "CREATE TABLE wp_smart_sorting_views_table (
                product_id INT NOT NULL,
                user_id INT NOT NULL,
                view_date DATE NOT NULL,
                view_num INT DEFAULT 0 NOT NULL,
                is_counted BIT DEFAULT 0 NOT NULL)"
        );
    }

    /**
     * Add necessary data to products, add additional tables in database
     * and add plugin options.
     *
     * @since    0.1.0
     */
	public static function activate() {
        self::add_spv_field();
        self::add_views_tracking_table();
        add_option( 'ss_views_delay', 7 );
	}
}
