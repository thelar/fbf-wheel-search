<?php


class Fbf_Wheel_Search_Api
{
    private $version;
    private $plugin;

    /**
     * The options name to be used in this plugin
     *
     * @since  	1.0.0
     * @access 	private
     * @var  	string 		$option_name 	Option name of this plugin
     */
    private $option_name = 'fbf_wheel_search';

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('parse_request', array($this, 'endpoint'), 0);
        add_action('init', array($this, 'add_endpoint'));
    }

    public function endpoint()
    {
        global $wp;

        if($wp->request == 'api/v2/pb_get_manufacturers'){
            $this->get_manufacturers();
            exit;
        }

        if($wp->request == 'api/v2/pb_get_chassis'){
            $this->get_chassis();
            exit;
        }

        if($wp->request == 'api/v2/pb_get_wheels'){
            $this->get_wheels();
            exit;
        }
    }

    public function add_endpoint()
    {

    }

    private function get_manufacturers()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $sql = "SELECT * FROM $table WHERE enabled = 1";
        $manufacturers = $wpdb->get_results($sql);
        $data = [];

        if($manufacturers!==false){
            foreach($manufacturers as $manufacturer){
                $data[] = [
                    'id' => $manufacturer->boughto_id,
                    'name' => $manufacturer->display_name
                ];
            }
        }
        $this->render_json($data);
    }

    private function get_chassis()
    {
        $manufacturer_id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);
        $data = $api->get_chasis($manufacturer_id);

        if(!empty($data)){
            $i = 0;
            foreach($data as $chassis){
                if(strpos(strtolower($chassis['name']), 'hidden')===false){
                    $ds = DateTime::createFromFormat(DATE_ISO8601, $chassis['year_start']);
                    $de = DateTime::createFromFormat(DATE_ISO8601, $chassis['year_end']);
                    if($ds){
                        $data[$i]['ds'] = $ds->format('Y');
                    }
                    if($de){
                        $data[$i]['de'] = $de->format('Y');
                    }
                }else{
                    unset($data[$i]);
                }
                $i++;
            }
        }

        if(!empty($data)){
            usort($data, function($a, $b){
                return [$a['name'], $b['ds']] <=> [$b['name'], $a['ds']];
            });
        }

        $this->render_json($data);
    }

    private function get_wheels()
    {
        $chassis_id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
        require_once plugin_dir_path(WP_PLUGIN_DIR . '/fbf-wheel-search/fbf-wheel-search.php') . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new \Fbf_Wheel_Search_Boughto_Api('fbf_wheel_search', 'fbf-wheel-search');
        $wheel_data = $api->get_wheels($chassis_id);

        if(!is_wp_error($wheel_data)&&!array_key_exists('error', $wheel_data)) {
            $skus_ids = [];
            foreach ($wheel_data['data'] as $wheel) {
                $product_id = wc_get_product_id_by_sku($wheel['ean']);
                if ($product_id) {
                    $product = wc_get_product($product_id);
                    if ($product->is_in_stock()) {
                        $skus_ids[] = [
                            //Add all the product data here
                            'id' => $product_id,
                            'name' => get_the_title($product_id),
                            'price' => number_format(wc_get_price_including_tax($product), 2),
                            'currency' => get_woocommerce_currency_symbol(),
                            'sku' => $product->get_sku(),
                            'image' => has_post_thumbnail($product_id)?wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'fbf-300-x')[0]:wc_placeholder_img_src('fbf-300-x'),
                            'stock' => $product->get_stock_quantity(),
                        ];
                    }
                }
            }
        }

        $this->render_json($skus_ids);
    }

    private function render_json($data){
        header('Content-Type: application/json');
        echo json_encode([
            'results' => $data
        ]);
    }
}
