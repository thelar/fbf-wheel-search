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
            $this->get_wheels(11.1);
            exit;
        }

        if($wp->request == 'api/v2/pb_get_tyre_sizes'){
            $this->get_tyre_sizes();
            exit;
        }

        if($wp->request == 'api/v2/pb_get_accessories'){
            $this->get_accessories(11.1);
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

    private function get_wheels($pc=0)
    {
        $chassis_id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
        require_once plugin_dir_path(WP_PLUGIN_DIR . '/fbf-wheel-search/fbf-wheel-search.php') . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new \Fbf_Wheel_Search_Boughto_Api('fbf_wheel_search', 'fbf-wheel-search');
        $wheel_data = $api->get_wheels($chassis_id);
        $included_brands = get_field('included_wheel_brands', 'options')?:[];

        if(!is_wp_error($wheel_data)&&!array_key_exists('error', $wheel_data)) {
            $skus_ids = [];
            foreach ($wheel_data['data'] as $wheel) {
                $product_id = wc_get_product_id_by_sku($wheel['ean']);
                if ($product_id) {
                    $product = wc_get_product($product_id);

                    //Category
                    $category_term = get_term_by('id', $product->get_category_ids()[0], 'product_cat');
                    $category = $category_term->name;

                    //Brand logo
                    $brand_logo = '';
                    $brand_terms = get_the_terms($product_id, 'pa_brand-name');

                    foreach($brand_terms as $brand_term){ //In reality there's only ever going to be 1 brand per product
                        $st = $brand_term->taxonomy . '_' . (string)$brand_term->term_id;
                        $link = get_term_link($brand_term->term_id, 'pa_brand-name');
                        $brand_id = $brand_term->term_id;
                        if(!empty(get_field('brand_logo', $st))){
                            $logo = get_field('brand_logo', $st)['sizes']['fbf-300-x'];
                            $brand_logo = sprintf('<a href="%3$s"><img src="%1$s" alt="%2$s"/></a>', $logo, $brand_term->name, $link);
                        }
                    }
                    $price = number_format(wc_get_price_including_tax($product), 2);
                    $price_exc = number_format(wc_get_price_excluding_tax($product), 2);
                    if($pc > 0){
                        $price+= ($price/100) * $pc;
                        $price_exc+= ($price_exc/100) * $pc;
                        $price = number_format($price, 2);
                        $price_exc = number_format($price_exc, 2);
                    }

                    if ($product->is_in_stock()) {
                        if(in_array($brand_id, $included_brands)){
                            $skus_ids[] = [
                                //Add all the product data here
                                'id' => $product_id,
                                'name' => get_the_title($product_id),
                                'price' => $price,
                                'price_exc' => $price_exc,
                                'currency' => get_woocommerce_currency_symbol(),
                                'image' => has_post_thumbnail($product_id)?wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'fbf-300-x')[0]:wc_placeholder_img_src('fbf-300-x'),
                                'image_lg' => has_post_thumbnail($product->get_id())?wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'fbf-1200-x')[0]:wc_placeholder_img_src('fbf-1200-x'),
                                'stock' => $product->get_stock_quantity(),
                                'brand' => [
                                    'name' => $brand_term->name,
                                    'logo' => isset($logo)?$logo:null,
                                ],
                                'details' => [
                                    'brand_name' => $product->get_attribute('pa_brand-name'),
                                    'product_type' => $category,
                                    'color' => $product->get_attribute('pa_wheel-colour'),
                                    'weight' => $product->get_weight(),
                                    'wheel_size' => $product->get_attribute('pa_wheel-size'),
                                    'wheel_width' => $product->get_attribute('pa_wheel-width'),
                                    'load_rating' => $product->get_attribute('pa_wheel-load-rating'),
                                    'offset' => $product->get_attribute('pa_wheel-offset'),
                                    'pcd' => $product->get_attribute('pa_wheel-pcd'),
                                    'sku' => $product->get_sku(),
                                    'ean' => $product->get_attribute('ean'),
                                    'centre_bore' => $product->get_attribute('pa_centre-bore')
                                ]
                            ];
                        }
                    }
                }
            }
        }

        $this->render_json($skus_ids);
    }

    private function get_tyre_sizes()
    {
        $data = [];
        $wheel_id = filter_var($_REQUEST['wheel_id'], FILTER_SANITIZE_STRING);
        $chassis = filter_var($_REQUEST['chassis_id'], FILTER_SANITIZE_STRING);

        $wheel_width_terms = wc_get_product_terms($wheel_id, 'pa_wheel-width');
        $wheel_diameter_terms = wc_get_product_terms($wheel_id, 'pa_wheel-size');
        $wheel_offset_terms = wc_get_product_terms($wheel_id, 'pa_wheel-offset');

        include_once(ABSPATH.'wp-admin/includes/plugin.php');
        require_once plugin_dir_path(WP_PLUGIN_DIR . '/fbf-wheel-search/fbf-wheel-search.php') . 'includes/class-fbf-wheel-search-boughto-api.php';
        $api = new \Fbf_Wheel_Search_Boughto_Api('fbf_wheel_search', 'fbf-wheel-search');
        $tyres = $api->tyres_for_wheels($wheel_id, $chassis, $wheel_width_terms[0]->name, $wheel_diameter_terms[0]->name, $wheel_offset_terms[0]->name);

        if(!key_exists('error', $tyres)&&key_exists('data', $tyres)){
            $data = $tyres['data'];
        }

        $this->render_json($data);
    }

    private function get_accessories($pc=null)
    {
        global $wp_query;
        $response = [];

        if(isset($_REQUEST['chassis'])&&isset($_REQUEST['wheel_id'])){
            $chassis = filter_var($_REQUEST['chassis'], FILTER_SANITIZE_STRING);
            $product_id = filter_var($_REQUEST['wheel_id'], FILTER_SANITIZE_STRING);

            $wheel = false;
            $steel_wheel = false;

            $product = wc_get_product($product_id);
            $category = $product->get_category_ids()[0];
            if($category){
                $term = get_term_by('id', $category, 'product_cat');
                if($term->name=='Alloy Wheel'||$term->name=='Steel Wheel'){
                    $wheel = $product;

                    if($term->name=='Steel Wheel'){
                        $steel_wheel = $product;
                    }
                }
            }

            //Look for wheel nuts here - can only offer them when we've searched for a wheel and have the chassis id
            if($chassis && $chassis != 'undefined'){
                require_once plugin_dir_path(WP_PLUGIN_DIR . '/fbf-wheel-search/fbf-wheel-search.php') . 'includes/class-fbf-wheel-search-boughto-api.php';
                $api = new \Fbf_Wheel_Search_Boughto_Api('fbf_wheel_search', 'fbf-wheel-search');

                //Retrieve the selected manufacturer data
                //Get the manufacturer id first via a transient, then by session if not set
                $trans_key = "boughto_chassis_{$chassis}_manufacturer";
                if(get_transient($trans_key)) {
                    $manufacturer_id = get_transient($trans_key);
                }else{
                    $manufacturer_id = WC()->session->get('fbf_manufacturer_id');
                }
                $all_chassis = $api->get_chasis($manufacturer_id);
                $index = array_search($chassis, array_column($all_chassis, 'id'));
                $chassis_data = $all_chassis[$index];

                //Retrieve the wheel data
                $all_wheel_data = $api->get_wheels($chassis)['data'];
                $sku = $wheel->get_sku();
                $index = array_search($sku, array_column($all_wheel_data, 'ean'));
                $wheel_data = $all_wheel_data[$index];

                if(!empty($wheel_data)&&!empty($chassis_data)){
                    //We can gather the bits of data for the wheel nut skus:
                    $sku = sprintf($chassis_data['nutBolt_thread_type'] . $chassis_data['nut_or_bolt'] . $chassis_data['nut_bolt_hex'] . '%1$s', $wheel_data['seat_type']=='Flat'?'FLAT':'');
                    $nuts = [
                        'title' => 'Wheel nuts for your wheel and vehicle:',
                        'text' => sprintf('Display Accessories whose SKU\'s begin with: <strong>' . $chassis_data['nutBolt_thread_type'] . $chassis_data['nut_or_bolt'] . $chassis_data['nut_bolt_hex'] . '%1$s' . '</strong>', $wheel_data['seat_type']=='Flat'?'FLAT':''),
                        'item' => [
                            'nutBolt_thread_type' => $chassis_data['nutBolt_thread_type'],
                            'nut_or_bolt' => $chassis_data['nut_or_bolt'],
                            'nub_bolt_hex' => $chassis_data['nut_bolt_hex'],
                            'family_tags' => $wheel_data['family']['tags'][0],
                            'seat_type' => $wheel_data['seat_type'],
                            'sku' => $sku
                        ],
                    ];
                    if($items = $this->get_upsell_items($chassis, $sku, 1, $pc)){
                        $nuts['items'] = $items;
                    }
                    $response[] = $nuts;
                }

                if($steel_wheel){
                    $sku = 'centrecap' . $wheel_data['centreBore'];
                    $caps = [
                        'title' => 'Centre cap for your wheel and vehicle:',
                        'text' => 'Display Accessories whose SKU\'s begin with: <strong>' . $sku . '</strong>',
                        'item' => [
                            'prefix' => 'centrecap',
                            'centreBore' => $wheel_data['centreBore'],
                            'sku' => $sku,
                        ]
                    ];
                    if($items = $this->get_upsell_items($chassis, $sku, 1, $pc)){
                        $caps['items'] = $items;
                    }
                    $response[] = $caps;
                }
            }

            //Generic upsells
            $generic_upsells = get_field('upsells', 'options');
            $generic_upsell_ids = [];
            if($generic_upsells){
                $generic = [
                    'title' => 'Generic upsells:',
                ];
                foreach($generic_upsells as $upsell){
                    $generic_upsell_ids[] = $upsell->ID;
                    $generic['item'][] = [
                        'id' => $upsell->ID,
                        'title' => get_the_title($upsell->ID),
                    ];
                }
                if($items = $this->get_upsell_items($chassis, false, 1, $pc, $generic_upsell_ids)){
                    $generic['items'] = $items;
                }
                $response[] = $generic;
            }
        }


        $this->render_json($response);
    }

    private function render_json($data){
        header('Content-Type: application/json');
        echo json_encode([
            'results' => $data
        ]);
    }

    private function get_upsell_items($chassis, $sku, $qty, $pc=null, $ids=false)
    {
        //Pull out the matching products
        if($sku!==false){
            global $wpdb;
            if(preg_match('/FLAT$/', $sku)){
                $query = 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = \'_sku\' AND meta_value LIKE \'' . strtoupper($sku) . '%\'';
            }else{
                $query = 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = \'_sku\' AND meta_value LIKE \'' . strtoupper($sku) . '%\' AND meta_value NOT LIKE \'%FLAT%\'';
            }

            $results = $wpdb->get_results($query);
            $ids = [];
        }else{
            $results = true;
        }


        if(!empty($results)){
            if(is_array($results)){
                foreach ($results as $result) {
                    $ids[] = $result->post_id;
                }
            }

            $args = [
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post__in' => $ids,
                'meta_query' => [
                    [
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '=',
                    ]
                ]
            ];

            $upsell_items = get_posts($args);
            if($upsell_items){
                $items = [];
                foreach($upsell_items as $upsell_item){
                    $product = wc_get_product($upsell_item->ID);
                    $product_stock = $product->get_stock_quantity();
                    if($product_stock < 30){ //Allowed up to 30 apparently
                        $max_packages = $product_stock;
                    }else{
                        $max_packages = 30;
                    }
                    $options = '';
                    for($i=1;$i<=$max_packages;$i++){
                        $options.= sprintf('<option value="%1$s" %2$s>%1$s</option>', $i, $i==1?'selected':'');
                    }
                    $single_price = wc_get_price_including_tax($product);
                    $price = number_format((wc_get_price_including_tax($product)), 2);
                    $price_exc = number_format(wc_get_price_excluding_tax($product), 2);
                    if(!is_null($pc)&&$pc>0){
                        $price+= ($price/100) * $pc;
                        $price_exc+= ($price_exc/100) * $pc;
                        $price = number_format($price, 2);
                        $price_exc = number_format($price_exc, 2);
                    }
                    $button = sprintf('<a href="/?add-multiple-to-cart=%1$s:1" data-quantity="1" class="button product_type_simple add_multiple_to_cart_button ajax_add_to_cart" data-product_id="%1$s" data-product_sku="%2$s" data-chassis-id="%4$s" rel="nofollow">Add to basket</a>', $product->get_id(), $product->get_sku(), $max_packages, $chassis);

                    //Category
                    $category_term = get_term_by('id', $product->get_category_ids()[0], 'product_cat');
                    $category = $category_term->name;

                    //Brand logo
                    $brand_logo = '';
                    $brand_terms = get_the_terms($product->get_id(), 'pa_brand-name');

                    foreach($brand_terms as $brand_term){ //In reality there's only ever going to be 1 brand per product
                        $st = $brand_term->taxonomy . '_' . (string)$brand_term->term_id;
                        if(!empty(get_field('brand_logo', $st))){
                            $link = get_term_link($brand_term->term_id, 'pa_brand-name');
                            $logo = get_field('brand_logo', $st)['sizes']['fbf-300-x'];
                            $brand_logo = sprintf('<a href="%3$s"><img src="%1$s" alt="%2$s"/></a>', $logo, $brand_term->name, $link);
                        }
                    }

                    //Tax
                    $tax = '';
                    $rates = \WC_Tax::get_rates();
                    if(!empty($rates)){
                        $tax_types = array_column($rates, 'label');
                        $tax = 'inc. ' . join(',', $tax_types);
                    }
                    $item = [
                        'id' => $product->get_id(),
                        'title' => $product->get_title(),
                        'url' => $product->get_permalink(),
                        'price' => $price,
                        'price_exc' => $price_exc,
                        'single_price' => $single_price,
                        'currency' => get_woocommerce_currency_symbol(),
                        'sku' => $product->get_sku(),
                        'image' => has_post_thumbnail($product->get_id())?wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'fbf-300-x')[0]:wc_placeholder_img_src('fbf-300-x'),
                        'image_lg' => has_post_thumbnail($product->get_id())?wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'fbf-1200-x')[0]:wc_placeholder_img_src('fbf-1200-x'),
                        'button' => $button,
                        'options' => $options,
                        'stock' => $product->get_stock_quantity(),
                        'tax' => $tax,
                        'brand' => [
                            'name' => $brand_term->name,
                            'logo' => $logo
                        ],
                        'details' => [
                            'brand_name' => $product->get_attribute('pa_brand-name'),
                            'product_type' => $category,
                            'weight' => $product->get_weight(),
                            'sku' => $product->get_sku(),
                            'ean' => $product->get_attribute('ean')
                        ]
                    ];
                    $items[] = $item;
                }
                return $items;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
