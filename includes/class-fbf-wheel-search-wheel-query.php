<?php

/**
 * Run the wheel query and display items
 *
 * @link       https://www.chapteragency.com
 * @since      1.0.0
 *
 * @package    Fbf_Wheel_Search
 * @subpackage Fbf_Wheel_Search/includes
 */

class Fbf_Wheel_Search_Wheel_Query
{
    public $chasis_id;
    public $vehicle_name;
    private $num_pages;
    private $plugin_name = 'fbf-wheel-search';
    private $option_name = 'fbf_wheel_search';

    public function __construct($chasis_id, $vehicle, $num_pages=8)
    {
        $this->chasis_id = $chasis_id;
        $this->vehicle_name = $vehicle;
        $this->num_pages = $num_pages;
    }

    public function get_title()
    {
        return '<h2>' . $this->vehicle_name . '</h2>';
    }

    public function get_wheels($chasis_id)
    {
        $key = "skus_for_chasis_{$chasis_id}";
        $transient = get_transient($key);

        if(!empty($transient)){
            $skus = $transient;
        }else{
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fbf-wheel-search-boughto-api.php';
            $api = new Fbf_Wheel_Search_Boughto_Api($this->option_name, $this->plugin_name);
            $wheel_data = $api->get_wheels($chasis_id);

            if(!is_wp_error($wheel_data)){
                $skus_ids = [];
                foreach($wheel_data['data'] as $wheel){
                    $product_id = wc_get_product_id_by_sku($wheel['ean']);
                    if($product_id){
                        $product = wc_get_product($product_id);
                        $skus_ids[$wheel['ean']] = [
                            'id' => $product_id,
                            'title' => $product->get_title(),
                            'stock' => $product->get_stock_quantity(),
                            'link' => get_permalink($product_id),
                        ];
                    }
                }
                return $skus_ids;
            }else{
                return false;
            }
        }
    }
}
