<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/public
 * @author     Kevin Price-Ward <kevin.price-ward@chapteragency.com>
 */
class Fbf_Wheel_Search_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The options name to be used in this plugin
     *
     * @since  	1.0.0
     * @access 	private
     * @var  	string 		$option_name 	Option name of this plugin
     */
    private $option_name = 'fbf_wheel_search';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbf_Wheel_Search_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbf_Wheel_Search_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fbf-wheel-search-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbf_Wheel_Search_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbf_Wheel_Search_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fbf-wheel-search-public.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name, 'fbf_wheel_search_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce($this->option_name),
        ]);

	}

    public function wheel_search_widget($atts)
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-shortcodes.php';
        $sc = new Fbf_Wheel_Search_Shortcodes();
        echo $sc->wheel_search($atts);
	}

    public static function manufacturers_dropdown()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1";
        $manufacturers = $wpdb->get_results($sql);
        $html = '';
        $html.= sprintf('<select class="form-control mb-4" id="%s">', 'fbf-wheel-search-manufacturer-select');
        $html.= sprintf('<option value="">Select manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        return $html;
	}

    public static function chasis_dropdown()
    {
        $html = sprintf('<select class="form-control mb-0" id="%s">', 'fbf-wheel-search-chasis-select');
        $html.= sprintf('<option value="">Select Chasis</option>');
        $html.= '</select>';
        return $html;
	}

    public function fbf_wheel_search_get_chasis()
    {
        check_ajax_referer($this->option_name, 'ajax_nonce');
        $id = filter_var($_POST['manufacturer_id'], FILTER_SANITIZE_STRING);
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);
        $data = $api->get_chasis($id);

        if(is_wp_error($data)){
            echo json_encode([
                'status' => 'error',
                'error' => 'Boughto API returned a WP_error',
                'wp_error' => $data
            ]);
        }else{
            if(array_key_exists('error', $data)){
                echo json_encode([
                    'status' => 'error',
                    'error' => $data['error']['message'],
                    'code' => $data['error']['code']
                ]);
            }else{
                echo json_encode([
                    'status' => 'success',
                    'manufacturer_id' => $id,
                    'data' => $data
                ]);
            }
        }
        die();
	}

    /*public function fbf_wheel_search_query_vars($vars)
    {
        $vars[] = "chasis";
        $vars[] = "vehicle";
        return $vars;
    }*/
}
