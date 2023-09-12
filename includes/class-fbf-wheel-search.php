<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/includes
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
 * @since      1.0.0
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/includes
 * @author     Kevin Price-Ward <kevin.price-ward@chapteragency.com>
 */
class Fbf_Wheel_Search {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Fbf_Wheel_Search_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'FBF_WHEEL_SEARCH_VERSION' ) ) {
			$this->version = FBF_WHEEL_SEARCH_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'fbf-wheel-search';

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
	 * - Fbf_Wheel_Search_Loader. Orchestrates the hooks of the plugin.
	 * - Fbf_Wheel_Search_i18n. Defines internationalization functionality.
	 * - Fbf_Wheel_Search_Admin. Defines all hooks for the admin area.
	 * - Fbf_Wheel_Search_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fbf-wheel-search-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-fbf-wheel-search-api.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fbf-wheel-search-public.php';

		$this->loader = new Fbf_Wheel_Search_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fbf_Wheel_Search_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fbf_Wheel_Search_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Fbf_Wheel_Search_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_api = new Fbf_Wheel_Search_Api( $this->get_plugin_name(), $this->get_version() );
		$plugin_public = new Fbf_Wheel_Search_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_page' );
        $this->loader->add_action( 'admin_post_fbf_wheel_search_sync_manufacturers', $plugin_admin, 'fbf_wheel_search_sync_manufacturers');
        $this->loader->add_action( 'admin_post_fbf_wheel_search_enable_manufacturer', $plugin_admin, 'fbf_wheel_search_enable_manufacturer');
        $this->loader->add_action( 'admin_post_fbf_wheel_search_scrape_boughto', $plugin_admin, 'fbf_wheel_search_scrape_boughto');
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'fbf_wheel_search_admin_notices');

        //Filters
        //$this->loader->add_filter('query_vars', $plugin_public, 'fbf_wheel_search_query_vars'); - now loaded in theme

        //Ajax
        $this->loader->add_action( 'wp_ajax_fbf_wheel_search_get_chasis', $plugin_public, 'fbf_wheel_search_get_chasis' );
        $this->loader->add_action( 'wp_ajax_nopriv_fbf_wheel_search_get_chasis', $plugin_public, 'fbf_wheel_search_get_chasis' );
        $this->loader->add_action( 'wp_ajax_fbf_wheel_fitment', $plugin_public, 'fbf_wheel_fitment' );
        $this->loader->add_action( 'wp_ajax_nopriv_fbf_wheel_fitment', $plugin_public, 'fbf_wheel_fitment' );
        $this->loader->add_action( 'wp_ajax_fbf_postcode_check', $plugin_public, 'fbf_postcode_check' );
        $this->loader->add_action( 'wp_ajax_nopriv_fbf_postcode_check', $plugin_public, 'fbf_postcode_check' );

        //Shortcodes
        $this->loader->add_shortcode('fbf_wheel_search_widget', $plugin_public, 'wheel_search_widget');
        $this->loader->add_shortcode('fbf_package_search_widget', $plugin_public, 'package_search_widget');
        $this->loader->add_shortcode('fbf_wheel_search_widget_v2', $plugin_public, 'wheel_search_widget_v2');
        $this->loader->add_shortcode('fbf_wheel_search_widget_v3', $plugin_public, 'wheel_search_widget_v3');
        $this->loader->add_shortcode('fbf_accessory_search_widget', $plugin_public, 'accessory_search_widget');
        $this->loader->add_shortcode('fbf_accessory_search_widget_v2', $plugin_public, 'accessory_search_widget_v2');
        $this->loader->add_shortcode('fbf_accessory_search_widget_v3', $plugin_public, 'accessory_search_widget_v3');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fbf_Wheel_Search_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Fbf_Wheel_Search_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
