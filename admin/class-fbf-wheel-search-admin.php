<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/admin
 * @author     Kevin Price-Ward <kevin.price-ward@chapteragency.com>
 */
class Fbf_Wheel_Search_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fbf-wheel-search-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fbf-wheel-search-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Register menu page
     *
     * @since 1.0.0
     */
    public function add_menu_page()
    {
        $this->plugin_screen_hook_sufix = add_menu_page(
            __( 'Wheel Search Settings', 'fbf-wheel-search' ),
            __( 'Wheel Search', 'fbf-wheel-search' ),
            'manage_options',
            $this->plugin_name,
            [$this, 'display_options_page'],
            'dashicons-admin-tools'
        );
    }

    /**
     * Register settings page
     */
    public function register_settings()
    {
        // Add a General section
        add_settings_section(
            $this->option_name . '_general',
            __( 'General', 'fbf-wheel-search' ),
            [$this, $this->option_name . '_general_cb'],
            $this->plugin_name
        );
        add_settings_field(
            $this->option_name . '_api_key',
            __( 'API Key', 'fbf-wheel-search' ),
            [$this, $this->option_name . '_api_key_cb'],
            $this->plugin_name,
            $this->option_name . '_general',
            ['label_for' => $this->option_name . '_api_key']
        );
        add_settings_field(
            $this->option_name . '_location_id',
            __( 'Location ID', 'fbf-wheel-search' ),
            [$this, $this->option_name . '_location_id_cb'],
            $this->plugin_name,
            $this->option_name . '_general',
            ['label_for' => $this->option_name . '_location_id']
        );
        register_setting( $this->plugin_name, $this->option_name . '_api_key', [$this, 'fbf_wheel_search_validate_api_key'] );
        register_setting( $this->plugin_name, $this->option_name . '_location_id', [$this, 'fbf_wheel_search_validate_location_id'] );
    }

    public function fbf_wheel_search_validate_api_key($input)
    {
        $option = get_option($this->option_name . '_api_key');
        $validated = sanitize_text_field($input);
        if($validated !== $input){
            $type = 'error';
            $message = __('API Key was not valid', 'fbf-wheel-search');
            $validated = $option;
        }else{
            $type = 'updated';
            $message = __('API Key updated', 'fbf-wheel-search');
        }
        add_settings_error(
            $this->option_name . '_api_key',
            esc_attr('settings_updated'),
            $message,
            $type
        );
        return $validated;
    }

    public function fbf_wheel_search_validate_location_id($input)
    {
        $option = get_option($this->option_name . '_location_id');
        $validated = sanitize_text_field($input);
        if($validated !== $input){
            $type = 'error';
            $message = __('Location ID was not valid', 'fbf-wheel-search');
            $validated = $option;
        }else{
            $type = 'updated';
            $message = __('Location ID updated', 'fbf-wheel-search');
        }
        add_settings_error(
            $this->option_name . '_location_id',
            esc_attr('settings_updated'),
            $message,
            $type
        );
        return $validated;
    }

    /**
     * Render the text for the general section
     *
     * @since  1.0.0
     */
    public function fbf_wheel_search_general_cb() {
        echo '<p>' . __( 'Please make changes to the Boughto API settings below.', 'fbf-rsp-generator' ) . '</p>';
    }

    /**
     * Render the min stock input for this plugin
     *
     * @since  1.0.0
     */
    public function fbf_wheel_search_api_key_cb() {
        $api_key = get_option( $this->option_name . '_api_key' );
        echo '<input type="text" name="' . $this->option_name . '_api_key' . '" id="' . $this->option_name . '_api_key' . '" value="' . $api_key . '"> ';
    }

    /**
     * Render the flat fee input for this plugin
     *
     * @since  1.0.0
     */
    public function fbf_wheel_search_location_id_cb() {
        $location_id = get_option( $this->option_name . '_location_id' );
        echo '<input type="text" name="' . $this->option_name . '_location_id' . '" id="' . $this->option_name . '_location_id' . '" value="' . $location_id . '"> ';
    }



    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function display_options_page() {
        include_once 'partials/fbf-wheel-search-admin-display.php';
    }


    public function fbf_wheel_search_sync_manufacturers()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';

        $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);
        var_dump($api->get_manufactuers());
        die('fbf_wheel_search_sync_manufacturers');
    }

    public function fbf_api_capture_data()
    {
        ini_set('xdebug.var_display_max_depth', '10');
        ini_set('xdebug.var_display_max_children', '5000');
        ini_set('xdebug.var_display_max_data', '1024');

        //Start by getting urls for chassis api calls
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1";
        $manufacturers = $wpdb->get_results($sql);

        $api_key = get_option('fbf_wheel_search' . '_api_key');
        $location = get_option('fbf_wheel_search' . '_location_id');

        $manu_api_calls = [];

        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                //$html.= sprintf('<option value="%s">%s</option>', $manufacturer->boughto_id, $manufacturer->display_name);
                $manu_api_calls[] = [
                    'id' => $manufacturer->boughto_id,
                    'key' => "boughto_bu_manufacturer_{$manufacturer->boughto_id}_chassis",
                    'url' => sprintf('%s/manufacturers/%d/chassis?location=%d', 'http://boughto.b8auto.com/api', (int)$manufacturer->boughto_id, $location)
                ];
            }

            //var_dump($manu_api_calls);

            $extraOptions = [
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "ApiKey: " . $api_key,
                ]
            ];

            //var_dump(array_column($manu_api_calls, 'url'));

            $manus = $this->paraCurl(array_column($manu_api_calls, 'url'), 10, $extraOptions);

            $chassis_api_calls = [];
            foreach($manus as $manu){
                if($manu['info']['http_code']===200){
                    $key = $manu_api_calls[array_search($manu['info']['url'], array_column($manu_api_calls, 'url'))]['key'];
                    $manu_id = $manu_api_calls[array_search($manu['info']['url'], array_column($manu_api_calls, 'url'))]['id'];
                    $url = $manu['info']['url'];
                    $data = json_decode($manu['body']);

                    set_transient($key, $data, YEAR_IN_SECONDS);

                    /*
                    var_dump($url);
                    var_dump($key);
                    var_dump($data);
                    */

                    foreach($data as $chassis){
                        $id = $chassis->id;
                        $key = "boughto_bu_chassis_{$id}_manufacturer";
                        set_transient($key, $manu_id, YEAR_IN_SECONDS);

                        $chassis_api_calls[] = [
                            'id' => $id,
                            'key' => "boughto_bu_wheels_for_chasis_{$id}",
                            'url' => sprintf('%s/search/wheels?location=%d&chassis=%d&returnStaggered=1&matchCentreBore=1&matchLoadRating=1&diameter=0&offset=0&limit=1000', 'http://boughto.b8auto.com/api', $location, $id)
                        ];
                    }
                }
            }

            //var_dump($chassis_api_calls);

            $chassis = $this->paraCurl(array_column($chassis_api_calls, 'url'), 10, $extraOptions);

            $sku_calls = [];
            foreach($chassis as $chas){
                var_dump($chas['info']['url'] . ' - ' . $chas['info']['http_code']);
                if($chas['info']['http_code']===200){
                    $key = $chassis_api_calls[array_search($manu['info']['url'], array_column($chassis_api_calls, 'url'))]['key'];
                    $chas_id = $chassis_api_calls[array_search($manu['info']['url'], array_column($chassis_api_calls, 'url'))]['id'];
                    $data = json_decode($chas['body']);
                    set_transient($key, $data, YEAR_IN_SECONDS);

                    //var_dump($data);

                    foreach($data->data as $wheel) {
                        $product_id = wc_get_product_id_by_sku($wheel->ean);
                        if ($product_id) {
                            $wheel_width_terms = wc_get_product_terms($product_id, 'pa_wheel-width');
                            $wheel_diameter_terms = wc_get_product_terms($product_id, 'pa_wheel-size');
                            $wheel_offset_terms = wc_get_product_terms($product_id, 'pa_wheel-offset');

                            $key = "boughto_bu_tyre_for_wheel_{$product_id}_{$chas_id}";

                            $sku_calls[] = [
                                'key' => $key,
                                'url' => sprintf("%s/search/tyres-for-wheel/%s/%s/%s?location=%d&upstep=both&wheel_offset=%d", 'http://boughto.b8auto.com/api', (int)$chas_id, (float)$wheel_width_terms[0]->name, (int)$wheel_diameter_terms[0]->name, $location, $wheel_offset_terms[0]->name)
                            ];
                        }
                    }
                }
            }

            var_dump($sku_calls);
        }

        die('fbf_api_capture_data');
    }

    public function fbf_wheel_search_enable_manufacturer()
    {
        $action = filter_var($_POST['toggle'], FILTER_SANITIZE_STRING);
        $id = filter_var($_POST[ $this->option_name . '_manufacturer_id'], FILTER_SANITIZE_STRING);
        $status = 'success';
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $update = $wpdb->update(
            $table,
            [
                'enabled' => $action
            ],
            [
                'id' => $id
            ]
        );
        if($update===false){
            $status = 'error';
            $message = urlencode('<strong>Database Error</strong> - there was an error updating the maufacturer');
        }else{
            $message = urlencode('<strong>Manufacturer updated</strong>');
        }
        wp_redirect(get_admin_url() . 'admin.php?page=' . $this->plugin_name . '&fbf_wheel_search_status=' . $status . '&fbf_wheel_search_message=' . $message);
    }

    /**
     * Admin notices
     */
    public function fbf_wheel_search_admin_notices()
    {
        if(isset($_REQUEST['fbf_wheel_search_status'])) {
            printf('<div class="notice notice-%s is-dismissible">', $_REQUEST['fbf_wheel_search_status']);
            printf('<p>%s</p>', $_REQUEST['fbf_wheel_search_message']);
            echo '</div>';
        }
    }

    public function print_manufacturer_rows()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $html = '';
        $sql = "SELECT * FROM $table";
        $rows = $wpdb->get_results($sql);
        if(!empty($rows)){
            foreach($rows as $row){
                $html.= sprintf('<tr style="background: %s;">', $row->enabled=='1'?'#dbffdd':'#fae3e3');
                $html.= sprintf('<td>%s</td>', $row->display_name);
                $html.= sprintf('<td><form action="%s" method="post" class="fbf-wheel-search-enable-manufacturer-form"><input type="hidden" name="action" value="fbf_wheel_search_enable_manufacturer"/><input type="hidden" name="%s" value="%s"/><input type="hidden" name="toggle" value="%s"/><button type="submit" class="no-styles fbf-rsp-generator-delete-rule">%s</button></form></td>', admin_url('admin-post.php'), $this->option_name . '_manufacturer_id', $row->id, $row->enabled=='1'?'0':'1', $row->enabled=='1'?'Disable':'Enable');
                $html.= '</tr>';
            }
        }
        return $html;
    }

    /**
     * Process any number of cURL requests in parallel, but limit
     * the number of simultaneous requests to $parallel.
     *
     * @param array $urls          Array with URLs to process
     * @param int   $parallel      Number of concurrent requests
     * @param array $extraOptions  User defined CURLOPTS
     * @return array[]
     */
    private function paraCurl($urls = [], $parallel = 10, $extraOptions = []) {

        // $extraOptions override the hardcoded ones.
        $options = $extraOptions + [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HEADER         => 1
            ];

        // The curl_multi handle.
        $mh = curl_multi_init();

        // Array with curl handles.
        $chs = [];

        // Create the individual curl handles and set options.
        foreach ($urls as $key => $url) {
            $chs[$key] = curl_init($url);
            curl_setopt_array($chs[$key], $options);
        }

        $curls = $chs;
        $open = null;

        // Perform the requests requests & dynamically (re)fill available slots
        // up to the specified limit ($parallel) until all urls are processed.
        while (0 < $open || 0 < count($curls)) {
            if ($open < $parallel && 0 < count($curls)) {
                curl_multi_add_handle($mh, array_shift($curls));
            }

            curl_multi_exec($mh, $open);
            usleep(11111);
        }

        // Extract downloaded data from curl handle.
        foreach ($chs as $key => $ch) {
            $res[$key]['info'] = curl_getinfo($ch);
            $response = curl_multi_getcontent($ch);

            // Separate response header & body.
            $res[$key]['head'] = substr($response, 0, $res[$key]['info']['header_size']);
            $res[$key]['body'] = substr($response, $res[$key]['info']['header_size']);

            curl_multi_remove_handle($mh, $ch);
        }

        // Close the curl_multi handle.
        curl_multi_close($mh);

        // Finally return all results.
        return isset($res) ? $res : [];
    }

}
