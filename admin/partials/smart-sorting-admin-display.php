<?php

/**
 * Provide an admin-specific view for the plugin.
 *
 * This file is used to markup the admin-specific aspects of the plugin.
 *
 * @license     https://www.gnu.org/licenses/lgpl-3.0.txt  LGPL License 3.0
 * @since       0.1.0
 *
 * @package     SmartSorting
 * @subpackage  SmartSorting/admin/partials
 */

/**
 * Creates a form for changing plugin settings.
 *
 * @since   0.1.0
 */
function show_smart_sorting_options() { ?>
        <div class="wrap">
	        <h1><?php echo get_admin_page_title(); ?></h1>
	        <form method="post" action="options.php">
                <?php
                    settings_fields( 'smart-sorting_settings' );
                    do_settings_sections( 'smart-sorting_settings' );
                    submit_button();
                ?>
            </form>
        </div>
<?php } ?>