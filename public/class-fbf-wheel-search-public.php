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

    public function wheel_search_widget_v2($atts)
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-shortcodes.php';
        $sc = new Fbf_Wheel_Search_Shortcodes();
        echo $sc->wheel_search_v2($atts);
	}

    public function package_search_widget($atts)
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-shortcodes.php';
        $sc = new Fbf_Wheel_Search_Shortcodes();
        echo $sc->package_search($atts);
	}

    public function accessory_search_widget($atts)
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-shortcodes.php';
        $sc = new Fbf_Wheel_Search_Shortcodes();
        echo $sc->accessory_search($atts);
    }

    public function accessory_search_widget_v2($atts)
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-shortcodes.php';
        $sc = new Fbf_Wheel_Search_Shortcodes();
        echo $sc->accessory_search_v2($atts);
    }

    public static function manufacturers_dropdown()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '<div class="fbf-form-group form-group">';
        $html.= sprintf('<select class="form-control mb-4" id="%s">', 'fbf-wheel-search-manufacturer-select');
        $html.= sprintf('<option value="">Manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        $html.= sprintf('<label for="%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-manufacturer-select', 'Manufacturer');
        $html.= '</div>';
        return $html;
	}

    public static function manufacturers_dropdown_v2($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '<div class="fbf-form-group form-group col-12 col-lg-6 pr-lg-3 mb-0">';
        $html.= sprintf('<select class="wheel-widget-v2__form-field fbf-wheel-search-manufacturer-select-v2 mb-3" id="%s_%s">', 'fbf-wheel-search-manufacturer-select', $id);
        $html.= sprintf('<option value="">Manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-manufacturer-select', $id, 'Make');
        $html.= '</div>';
        return $html;
	}

    public static function manufacturers_dropdown_accessories($id=1)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '<div class="fbf-form-group form-group">';
        $html.= sprintf('<select class="form-control accessories-widget__form-field fbf-accessories-search-manufacturer-select mb-3" id="%s_%s">', 'fbf-accessories-search-manufacturer-select', $id);
        $html.= sprintf('<option value="">Manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-manufacturer-select', $id, 'Make');
        $html.= '</div>';
        return $html;
	}

    public static function manufacturers_dropdown_accessories_v2($id=1)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '<div class="fbf-form-group form-group col-12 col-lg-6 pr-lg-3 mb-0">';
        $html.= sprintf('<select class="accessories-widget__form-field fbf-accessories-search-manufacturer-select mb-3" id="%s_%s">', 'fbf-accessories-search-manufacturer-select', $id);
        $html.= sprintf('<option value="">Manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-manufacturer-select', $id, 'Make');
        $html.= '</div>';
        return $html;
	}

    public static function manufacturers_dropdown_package()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '';
        $html.= sprintf('<select class="form-control mb-4" id="%s">', 'fbf-package-search-manufacturer-select');
        $html.= sprintf('<option value="">Manufacturer</option>');
        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        return $html;
	}

    public static function manufacturers_dropdown_fitment()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1 ORDER BY display_name";
        $manufacturers = $wpdb->get_results($sql);
        $html = '';

        if(isset(WC()->session->get('fbf_last_chassis_search')['manufacturer_id'])){
            $manu_id = WC()->session->get('fbf_last_chassis_search')['manufacturer_id'];
            $chassis_id = WC()->session->get('fbf_last_chassis_search')['chassis_id'];
            $html.= sprintf('<select class="single-product__fitment-select mb-4" id="%s" data-init_id="%s" data-chassis_id="%s">', 'fbf-fitment-manufacturer-select', $manu_id, $chassis_id);
        }else{
            $html.= sprintf('<select class="single-product__fitment-select mb-4" id="%s">', 'fbf-fitment-manufacturer-select');
            $manu_id = '';
        }

        $html.= sprintf('<option value="">Manufacturer</option>');

        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $html.= sprintf('<option value="%s"%s>%s</option>', $manufacturer->boughto_id, $manu_id==$manufacturer->boughto_id?' selected':'', $manufacturer->display_name);
            }
        }
        $html.= '</select>';
        return $html;
	}

    public static function chasis_dropdown()
    {
        $html = '<div class="fbf-form-group form-group mt-4">';
        $html.= sprintf('<select class="form-control mb-0" id="%s">', 'fbf-wheel-search-chasis-select');
        $html.= sprintf('<option value="">Model</option>');
        $html.= '</select>';
        $html.= sprintf('<label for="%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-chasis-select', 'Model');
        $html.= '</div>';
        return $html;
	}

    public static function chasis_dropdown_v2($id)
    {
        $html = '<div class="fbf-form-group form-group right col-12 col-lg-6 mb-0">';
        $html.= sprintf('<select class="fbf-wheel-search-chassis-select-v2 wheel-widget-v2__form-field mb-3" id="%s_%s">', 'fbf-wheel-search-chasis-select', $id);
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-wheel-search-chasis-select', $id, 'Model');
        $html.= '</div>';
        return $html;
	}

    public static function chasis_dropdown_accessories($id=1)
    {
        $html = '<div class="fbf-form-group form-group mt-4">';
        $html.= sprintf('<select class="form-control fbf-accessories-search-chassis-select accessories-widget__form-field mb-3" id="%s_%s">', 'fbf-accessories-search-select', $id);
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-accessories-search-chasis-select', $id, 'Model');
        $html.= '</div>';
        return $html;
	}

    public static function chasis_dropdown_accessories_v2($id=1)
    {
        $html = '<div class="fbf-form-group form-group right col-12 col-lg-6 mb-0">';
        $html.= sprintf('<select class="fbf-accessories-search-chassis-select accessories-widget__form-field mb-3" id="%s_%s">', 'fbf-accessories-search-select', $id);
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        $html.= sprintf('<label for="%s_%s" class="control-label"><span class="floating-label">%s</span></label>', 'fbf-accessories-search-chasis-select', $id, 'Model');
        $html.= '</div>';
        return $html;
	}

    public static function chasis_dropdown_package()
    {
        $html = sprintf('<select class="form-control mb-0" id="%s">', 'fbf-package-search-chasis-select');
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        return $html;
	}

    public static function chasis_dropdown_fitment($product_id)
    {
        $html = sprintf('<select class="single-product__fitment-select mb-0" id="%s" data-product_id="%s">', 'fbf-fitment-chasis-select', $product_id);
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        return $html;
	}

    public function fbf_wheel_fitment ()
    {
        check_ajax_referer($this->option_name, 'ajax_nonce');
        $chassis_id = filter_var($_POST['chassis_id'], FILTER_SANITIZE_STRING);
        $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
        $vehicle = filter_var($_POST['vehicle'], FILTER_SANITIZE_STRING);

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);

        $fits = $api->wheel_fits_chassis($product_id, $chassis_id);
        echo json_encode([
            'status' => 'success',
            'fits' => $fits,
            'vehicle' => $vehicle,
            'id' => $chassis_id,
        ]);
        die();
	}

    public function fbf_postcode_check()
    {
        check_ajax_referer($this->option_name, 'ajax_nonce');
        $resp = [];
        $resp['status'] = 'success';

        $country = $this->fbf_wheel_search_country();
        $postcode = filter_var($_POST['postcode'], FILTER_SANITIZE_STRING);

        if(WC_Validation::is_postcode($postcode, $country)){
            $resp['status'] = 'success';
            WC()->session->set('customer_postcode', $postcode);
        }else{
            $resp['status']  = 'error';
            $resp['error'] = 'Invalid postcode';
        }

        echo json_encode($resp);
        die();
    }

    public static function chasis_dropdown_landing($brand_term_id)
    {
        $html = sprintf('<select class="form-control mb-0" id="%s" data-brand="%s">', 'fbf-package-search-chasis-select', $brand_term_id);
        $html.= sprintf('<option value="">Select model</option>');
        $html.= '</select>';
        return $html;
	}

    public function fbf_wheel_search_get_chasis()
    {
        check_ajax_referer($this->option_name, 'ajax_nonce');
        $id = filter_var($_POST['manufacturer_id'], FILTER_SANITIZE_STRING);
        $update_session = $_POST['update_session'];
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);
        $data = $api->get_chasis($id);

        if(!empty($data)){
            $i = 0;
            foreach($data as $chassis){
                $ds = DateTime::createFromFormat('Y-m-d', $chassis['generation']['start_date']);
                $de = DateTime::createFromFormat('Y', $chassis['generation']['end_date']);
                if($ds){
                    $data[$i]['ds'] = $ds->format('Y');
                }
                if($de){
                    $data[$i]['de'] = $de->format('Y');
                }
                $name = str_replace(' All Engines', '', $data[$i]['chassis']['display_name']); // Remove ' All Engines' from string
                $data[$i]['chassis']['display_name'] =  $name;
                $i++;
            }
        }

        if(!empty($data)){
            usort($data, function($a, $b){
                return [$a['chassis']['display_name'], $b['ds']] <=> [$b['chassis']['display_name'], $a['ds']];
            });
        }

        //Store the manufacturer ID in session because we will need it
        if($update_session==='true'){
            WC()->session->set('fbf_manufacturer_id', $id);
        }

        if(is_wp_error($data)){
            echo json_encode([
                'status' => 'error',
                'error' => 'Boughto API returned a WP_error',
                'wp_error' => $data
            ]);
        }else if($data===false){
            echo json_encode([
                'status' => 'error',
                'error' => 'Boughto API returned $data as false',
                'data' => $data
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

    public function fbf_wheel_search_country()
    {
        //Country
        //see if there's a billing country
        if(!is_null(WC()->customer)){
            $country = WC()->customer->get_billing_country();
        }
        if(!$country){
            //Geolocate
            $geo = new \WC_Geolocation();
            $user_ip  = $geo->get_ip_address(); // Get user IP
            $user_geo = $geo->geolocate_ip( $user_ip ); // Get geolocated user data.
            $country  = $user_geo['country']; // Get the country code
        }
        return $country;
    }
}
