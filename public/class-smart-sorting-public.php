<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for public-facing functions.
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/public
 * @author      SmartSorting Team <smartsprtingofficial@gmail.com>
 */
class Smart_Sorting_Public {

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
	 * @param   string  $smart_sorting  The name of the plugin.
	 * @param   string  $version        The version of this plugin.
     * @since   0.1.0
	 */
	public function __construct( $smart_sorting, $version ) {

		$this->smart_sorting = $smart_sorting;
		$this->version       = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
            $this->smart_sorting . '_public_style',
            plugin_dir_url( __FILE__ ) . 'css/smart-sorting-public.css',
            array(),
            $this->version,
            'all'
        );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
            $this->smart_sorting . '_public_script',
            plugin_dir_url( __FILE__ ) . 'js/smart-sorting-public.js',
            array( 'jquery' ),
            $this->version,
            false
        );

        wp_localize_script(
            $this->smart_sorting . '_public_script',
            'ajax_obj',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'increment_view' ),
            )
        );
	}

    /**
     * Set the sorting parameter.
     *
     * @param   array $args
     * @return  array
     * @since   0.1.0
     */
    public function get_smartsorting_ordering_args( $args ) {
        if ( isset( $_GET['orderby'] ) ) {
            if ( 'menu_order' == wc_clean( $_GET['orderby'] ) ) {
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_key'] = 'spv';
            }
        } else {
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            $args['meta_key'] = 'spv';
        }
        return $args;
    }

    /**
     * Add the current view to the database.
     *
     * @since   0.1.0
     */
    public static function ajax_add_view() {
        global $wpdb;

        check_ajax_referer( 'increment_view' );
        $product_id = sanitize_key( $_POST['productId'] );

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
        } else {
            $user_id = -1;
        }

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE wp_smart_sorting_views_table
            SET view_num = view_num + 1
            WHERE product_id = %d
              AND user_id = %d
              AND view_date = CURRENT_DATE
              AND is_counted = 0",
                $product_id,
                $user_id,
            )
        );

        if ( 0 == $wpdb->rows_affected ) {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO wp_smart_sorting_views_table
                (product_id, user_id, view_date, view_num)
                VALUES (%d, %d, CURRENT_DATE, 1)",
                    $product_id,
                    $user_id,
                )
            );
        }

        wp_die();
    }

    /**
     * Update the sorting parameter value.
     *
     * @since   0.1.0
     */
    public static function update_spv_value() {
        $product_query = new WP_Query( array(
            'post_type' => 'product',
        ) );
        while ( $product_query->have_posts() ){
            $product_query->the_post();
            $id    = $product_query->post->ID;
            $views = (float) get_post_meta( $id, 'spv_views', true );
            $sales = (float) get_post_meta( $id, 'spv_sales', true );
            $spv_value = 0;
            if ( 0 != $views ) {
                $spv_value = $sales / $views;
            }
            update_post_meta( $id, 'spv', $spv_value );
        }
        wp_reset_postdata();
    }

    /**
     * Update sales and views values after a purchase.
     *
     * @param   $order_id
     * @since   0.1.0
     */
    public function track_total_sales( $order_id ) {
        global $wpdb;
        $order = wc_get_order( $order_id );

        $view_delay = get_option( 'ss_views_delay' );

        if ( count( $order->get_items() ) > 0 ) {
            foreach ( $order->get_items() as $item ) {

                $cart_product_id = $item->get_product_id();

                if ( $cart_product_id ) {

                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$wpdb->postmeta}
                                SET meta_value = meta_value + %f
                                WHERE post_id = %d
                                  AND meta_key = 'spv_sales'",
                            $item->get_quantity(),
                            $cart_product_id
                        )
                    );

                    if ( is_user_logged_in() ) {
                        $user_id = get_current_user_id();
                    } else {
                        $user_id = -1;
                    }

                    if ( 0 != $view_delay ) {
                        $view_nums = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT product_id, view_num
                                    FROM wp_smart_sorting_views_table
                                    WHERE view_date > DATE_SUB(CURRENT_DATE, INTERVAL %d DAY)
                                      AND user_id = %d
                                      AND is_counted = 0",
                                $view_delay,
                                $user_id
                            )
                        );
                    } else {
                        $view_nums = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT product_id, view_num
                                    FROM wp_smart_sorting_views_table
                                    WHERE user_id = %d
                                      AND is_counted = 0",
                                $user_id
                            )
                        );
                    }

                    $sale_terms = get_the_terms(
                        $cart_product_id,
                        'product_cat'
                    );
                    $views = array();

                    foreach ( $view_nums as $view_num ) {
                        $views[ $view_num->product_id ] = 0;
                        $view_terms = get_the_terms(
                            $view_num->product_id,
                            'product_cat'
                        );
                        foreach ( $view_terms as $view_term ) {
                            if ( in_array( $view_term, $sale_terms ) ||
                                ( ! $sale_terms ) ) {
                                $views[ $view_num->product_id ] +=
                                    $view_num->view_num;
                                break;
                            }
                        }
                    }

                    foreach ( $views as $viewed_product_id => $viewed_product_views ) {
                        if ( $viewed_product_views > 0 ) {

                            $wpdb->query(
                                $wpdb->prepare(
                                    "UPDATE {$wpdb->postmeta}
                                        SET meta_value = meta_value + %d
                                        WHERE post_id = %d
                                          AND meta_key = 'spv_views'",
                                    $viewed_product_views,
                                    $viewed_product_id
                                )
                            );

                            if ( 0 != $view_delay ) {
                                $wpdb->query(
                                    $wpdb->prepare(
                                        "UPDATE wp_smart_sorting_views_table
                                            SET is_counted = 1
                                            WHERE view_date > DATE_SUB(CURRENT_DATE, INTERVAL %d DAY)
                                              AND user_id = %d
                                              AND product_id = %d
                                              AND is_counted = 0",
                                        $view_delay,
                                        $user_id,
                                        $viewed_product_id
                                    )
                                );
                            } else {
                                $wpdb->query(
                                    $wpdb->prepare(
                                        "UPDATE wp_smart_sorting_views_table
                                            SET is_counted = 1
                                            WHERE user_id = %d
                                              AND product_id = %d
                                              AND is_counted = 0",
                                        $user_id,
                                        $viewed_product_id
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
