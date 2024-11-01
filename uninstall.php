<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 */

// If uninstall not called from WordPress, then die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Removing plugin options
$option_list = array(
    'ss_views_delay',
);
foreach ( $option_list as $option ) {
    delete_option( $option );
    delete_site_option( $option );
}

// Removing product metadata that the plugin adds
$meta_list = array(
    'spv_views',
    'spv_sales',
    'spv',
);
foreach ( $meta_list as $meta ) {
    delete_metadata( 'post', null, $meta, '', true );
}

// Removing database tables that the plugin creates
global $wpdb;
$table_list = array(
    'wp_smart_sorting_views_table',
);
foreach ( $table_list as $table ) {
    $wpdb->query(
        $wpdb->prepare(
            "DROP TABLE IF EXISTS {$table}"
        )
    );
}

