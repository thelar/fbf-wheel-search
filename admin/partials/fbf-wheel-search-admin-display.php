<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <?php
        global $shortcode_tags;
        //var_dump($shortcode_tags);
    ?>

    <form method="post" action="options.php">
        <?php
        settings_errors();
        settings_fields( $this->plugin_name );
        do_settings_sections( $this->plugin_name );
        submit_button();
        ?>
    </form>

    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" style="margin-bottom: 1em">
        <input type="hidden" name="action" value="fbf_wheel_search_sync_manufacturers">
        <input type="submit" value="Sync Manufacturers" class="button-primary">
    </form>

    <hr/>

    <h2>Manufacturers</h2>

    <table class="widefat" id="fbf-wheel-search-manufacturers-table" style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th class="row-title"><?php esc_attr_e( 'Manufacturer name', $this->plugin_name ); ?></th>
                <th><?php esc_attr_e( 'Enable/Disable', $this->plugin_name ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->print_manufacturer_rows(); ?>
        </tbody>
    </table>
</div>
