<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/includes
 * @author     Kevin Price-Ward <kevin.price-ward@chapteragency.com>
 */
class Fbf_Wheel_Search_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        //Install the logging database
        self::db_install();
	}

    private static function db_install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            boughto_id mediumint(9) NOT NULL,
            name varchar(20) NOT NULL,
            display_name varchar(20) NOT NULL,
            enabled boolean NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) $charset_collate";

        dbDelta($sql);

        add_option('fbf_wheel_search_db_version', FBF_WHEEL_SEARCH_DB_VERSION);
	}

}
