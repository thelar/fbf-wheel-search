<?php


class Fbf_Wheel_Search_Boughto_Api
{
    private $option_name;
    private $plugin_name;
    private $api_url = 'https://boughtofeed.co.uk/api';
    private $location;
    private $api_key;
    private $authorization;
    private $headers;
    private $cache = true; // Flag for whether to use transient data or not, setting to (bool) false will force it to query boughto API for every call
    public function __construct($option_name, $plugin_name)
    {
        $this->option_name = $option_name;
        $this->plugin_name = $plugin_name;
        $this->api_key = get_option($this->option_name . '_api_key');
        $this->location = get_option($this->option_name . '_location_id');
        $this->authorization = get_option($this->option_name . '_bearer_token');
        $this->headers = [
            'headers' => [
                "Authorization" => "Bearer " . $this->authorization
            ],
            'timeout' => 20
        ];
    }

    public function get_manufactuers()
    {
        global $wpdb;
        $status = 'success';
        $table = $wpdb->prefix . 'fbf_vehicle_manufacturers';
        $url = sprintf('%s/vehicles/manufacturers', $this->api_url);
        $response = wp_remote_get($url, $this->headers);
        if (is_array($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);

            //Check response
            if($data['status']==='success'){
                $manufacturers = $data['manufacturers'];
            }
        }


        //Retrieved boughto ids
        $boughto_ids = [];

        //Get all current rows
        $sql = "SELECT * FROM $table";
        $ids = $wpdb->get_results($sql);
        $current_ids = [];
        if($ids!==false){
            foreach($ids as $row){
                $current_ids[] = $row->boughto_id;
            }
        }

        foreach ($manufacturers as $key => $manufacturer) {
            if (empty($manufacturer['name'])) {
                unset ($data[$key]);
            }else{
                $boughto_ids[] = $manufacturer['id'];
                $sql = "SELECT id FROM $table WHERE boughto_id = '" . $manufacturer['id'] . "'";
                $id = $wpdb->get_row($sql);

                if(null !== $id){
                    //ID exists - update it
                    $update = $wpdb->update(
                        $table,
                        [
                            'name' => $manufacturer['name'],
                            'display_name' => $manufacturer['name']
                        ],
                        [
                            'boughto_id' => $manufacturer['id']
                        ]
                    );
                    if($update===false){
                        $status = 'error';
                        $message = urlencode('<strong>Database error</strong> - could not update row');
                        break;
                    }
                }else{
                    //ID does not exist - create it
                    $insert = $wpdb->insert(
                        $table,
                        [
                            'boughto_id' => $manufacturer['id'],
                            'name' => $manufacturer['name'],
                            'display_name' => $manufacturer['name'],
                        ]
                    );
                    if($insert!==1){
                        $status = 'error';
                        $message = urlencode('<strong>Database error</strong> - could not insert row');
                        break;
                    }
                }
            }
        }

        //Now compare ids and remove any in $current_ids that aren't in $boughto_ids
        foreach($current_ids as $check){
            if(!in_array($check, $boughto_ids)){
                $delete = $wpdb->delete(
                    $table,
                    [
                        'boughto_id' => $check,
                    ]
                );
                if($delete===false){
                    $status = 'error';
                    $message = urlencode('<strong>Database error</strong> - could not delete row');
                    break;
                }
            }
        }

        if($status=='success'){
            $message = urlencode('<strong>Manufacturers updated</strong>');
        }
        wp_redirect(get_admin_url() . 'admin.php?page=' . $this->plugin_name . '&fbf_wheel_search_status=' . $status . '&fbf_wheel_search_message=' . $message);
    }

    public function get_chasis($manu_id, $use_cache = true)
    {
        if(!$use_cache){
            $this->cache = false;
        }

        if (empty($manu_id) || !is_numeric($manu_id)) {
            return false;
        }

        $key = "boughto_manufacturer_{$manu_id}_chassis";
        $transient = get_transient($key);

        if (!empty($transient)&&$this->cache) {
            return $transient;
        } else {
            $url = sprintf('%s/vehicles/manufacturers/%d/chassis', $this->api_url, (int)$manu_id);

            $response = wp_remote_get($url, $this->headers);

            if(!is_wp_error($response)&&is_array($response)){
                $data = json_decode(wp_remote_retrieve_body($response), true);

                if($data['status']==='success'){
                    set_transient($key, $data['chassis'], WEEK_IN_SECONDS);

                    //Set a transient that matches a chassis to manufacturer for recall in upsells
                    foreach($data['chassis'] as $chassis){
                        $key = "boughto_chassis_{$chassis['chassis']['id']}_manufacturer";
                        set_transient($key, $manu_id, WEEK_IN_SECONDS);
                    }
                    return $data['chassis'];
                }else{
                    return false;
                }


            }else{
                return $response;
            }
        }
    }

    public function get_chassis_detail($chassis_id)
    {
        $key = "boughto_chassis_{$chassis_id}";
        $transient = get_transient($key);

        if(!empty($transient)&&$this->cache){
            return $transient;
        }else{
            $url = sprintf('%s/vehicles/chassis/%d', $this->api_url, $chassis_id);
            $response = wp_remote_get($url, $this->headers);

            if(!is_wp_error($response)&&is_array($response)){
                $data = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($key, $data, WEEK_IN_SECONDS);
                return $data;
            }else{
                return $response;
            }
        }
    }

    public function get_wheels($chasis_id, $use_cache = true)
    {
        if(!$use_cache){
            $this->cache = false;
        }

        $num_results = 500;
        $key = "boughto_wheels_for_chasis_{$chasis_id}";
        $transient = get_transient($key);

        $upsteps = $this->get_upsteps($chasis_id);

        if(!empty($transient)&&$this->cache){
            return $transient;
        }else{
            $url = sprintf('%s/search/wheels?chassis_id=%d&ignore_no_price=1&ignore_no_stock=1&itemsPerPage=%d&use_load_rating=1', $this->api_url, $chasis_id, $num_results);

            $response = wp_remote_get($url, $this->headers);

            if(!is_wp_error($response)&&is_array($response)){
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $results = $data['results'];

                // Check if we are getting all the results
                if($data['pagination']['current_page'] < $data['pagination']['total_pages']){
                    // Call again for each page and add to results
                    for($i = $data['pagination']['current_page'] + 1;$i <= $data['pagination']['total_pages']; $i++){
                        $page_url = $url . '&page=' . $i;
                        $response = wp_remote_get($page_url, $this->headers);

                        if(!is_wp_error($response)&&is_array($response)){
                            $page_data = json_decode(wp_remote_retrieve_body($response), true);
                            $results = array_merge($results, $page_data['results']);
                        }
                    }
                }
                $results = $this->process_upsteps($results, $upsteps['upsteps']);
                //$data['results'] = $results;
                $data['results'] = $this->simplify($results, ['product_code', 'id', 'seat_type', 'center_bore', 'family', 'upstep']); // We only need to store a fraction of the amount of data returned from boughto
                set_transient($key, $data, WEEK_IN_SECONDS);
                return $data;
            }else{
                return $response;
            }
        }
    }

    public function get_wheel_by_sku($sku)
    {
        $url = sprintf('%s/wheels/product_code/%s', $this->api_url, $sku);
        $response = wp_remote_get($url, $this->headers);

        if(!is_wp_error($response)&&is_array($response)){
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data;
        }else{
            return $response;
        }
    }

    public function get_wheels_v4($chasis_id)
    {
        $key = "boughto_v4_wheels_for_chasis_{$chasis_id}";
        $transient = get_transient($key);

        if(!empty($transient)&&$this->cache){
            return $transient;
        }else{
            $url = sprintf('%s/search/wheels?chassis_id=%d&itemsPerPage=500', $this->api_url, $chasis_id);
            $response = wp_remote_get($url, $this->headers);

            if(!is_wp_error($response)&&is_array($response)){
                $data = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($key, $data, WEEK_IN_SECONDS);
                return $data;
            }else{
                return $response;
            }
        }
    }

    public function tyres_for_wheels($product_id, $chassis, $width, $diameter, $offset)
    {
        $wheels_data_key = "boughto_wheels_for_chasis_{$chassis}";
        if(!$wheels_data = get_transient($wheels_data_key)){
            $wheels_data = $this->get_wheels($chassis);
        }
        if(is_array($wheels_data) && !empty($wheels_data)){
            $product = wc_get_product($product_id);
            $sku = $product->get_sku();
            $wheel_id = $wheels_data['results'][array_search($sku, array_column($wheels_data['results'], 'product_code'))]['id'];
        }
        if($wheel_id){
            $key = "boughto_tyre_for_wheel_{$wheel_id}_{$chassis}";
            $transient = get_transient($key);

            if(!empty($transient)&&$this->cache){
                $data = $transient;
            }else{
                // Tyres
                //$url = sprintf("%s/search/tyres-for-wheel/%s/%s/%s?location=%d&upstep=both&wheel_offset=%d", $this->api_url, (int)$chassis, (float)$width, (int)$diameter, $this->location, $offset);

                $url = sprintf("%s/search/wheels/%d/tyre-sizes?wheel_id=%d", $this->api_url, (int)$chassis, (int)$wheel_id);

                $response = wp_remote_get($url, $this->headers);

                $tyre_sizes = array();
                if (is_array($response)) {
                    $data = json_decode(wp_remote_retrieve_body($response), true);
                    $data['url'] = $url;
                    set_transient($key, $data, WEEK_IN_SECONDS);
                }
            }
            return $data;
        }else{
            return false;
        }
    }

    public function tyre_sizes($chassis, $wheel_id)
    {
        $key = "boughto_tyre_for_wheel_{$wheel_id}_{$chassis}";
        $transient = get_transient($key);

        if(!empty($transient)&&$this->cache){
            $data = $transient;
        }else{
            // Tyres
            //$url = sprintf("%s/search/tyres-for-wheel/%s/%s/%s?location=%d&upstep=both&wheel_offset=%d", $this->api_url, (int)$chassis, (float)$width, (int)$diameter, $this->location, $offset);

            $url = sprintf("%s/search/wheels/%d/tyre-sizes?wheel_id=%d", $this->api_url, (int)$chassis, (int)$wheel_id);

            $response = wp_remote_get($url, $this->headers);

            $tyre_sizes = array();
            if (is_array($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $data['url'] = $url;
                set_transient($key, $data, WEEK_IN_SECONDS);
            }
        }
        return $data;
    }

    /**
     * Check whether a given product_id (wheel) fits a chassis
     *
     * @param $product_id
     * @param $chassis_id
     * @return bool
     */
    public function wheel_fits_chassis($product_id, $chassis_id)
    {
        if($this->is_a_bot()){
            return true; // Always return true for Bots to fix discrepancy issue with Google feed (basically, for bots in stock products are available to purchase)
        }
        $wheels = $this->get_wheels($chassis_id);
        if(!empty($wheels['results'])){
            $sku = wc_get_product($product_id)->get_sku();
            $search = array_search($sku, array_column($wheels['results'], 'product_code'));
            if($search!==false){
                return true;
            }
        }
        return false;
    }

    /**
     * Get upsteps for chassis
     *
     * @param $chassis_id
     * @return array
     */
    public function get_upsteps($chassis_id)
    {
        $key = "boughto_upsteps_for_chassis_{$chassis_id}";
        $transient = get_transient($key);

        if(!empty($transient)&&$this->cache){
            $data = $transient;
        }else{
            $url = sprintf("%s/vehicles/chassis/%d/upsteps", $this->api_url, (int)$chassis_id);

            $response = wp_remote_get($url, $this->headers);

            if (is_array($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $data['url'] = $url;
                set_transient($key, $data, WEEK_IN_SECONDS);
            }
        }
        return $data;
    }

    public function is_a_bot(){

        $is_bot = false;

        $user_agents = array( 'GTmetrix', 'Googlebot', 'Storebot', 'Bingbot', 'BingPreview', 'msnbot', 'slurp', 'Ask Jeeves/Teoma', 'Baidu', 'DuckDuckBot', 'AOLBuild' );

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        foreach ( $user_agents as $agent ){
            if ( strpos( $user_agent, $agent) ){
                $is_bot = true;
            }
        }

        return $is_bot;

    }

    private function simplify($results, $keep){
        $return = [];
        foreach($results as $item){
            $new_item = [];
            foreach($item as $k => $v){
                if(in_array($k, $keep)){
                    $new_item[$k] = $v;
                }
            }
            $return[] = $new_item;
        }
        return $return;
    }

    private function process_upsteps($results, $upsteps)
    {
        foreach($results as $wi => $wheel){
            foreach($upsteps as $ui => $upstep){
                if($wheel['diameter']==$upstep['diameter']&&$wheel['width']==$upstep['width']){
                    if((float)$wheel['offset_et']>=(float)$upstep['min_offset']&&(float) $wheel['offset_et']<=(float)$upstep['max_offset']){
                        if($upstep['comment']==='IDEAL'||$upstep['comment']==='SPECIALIST'){
                            $results[$wi]['upstep'] = strtolower($upstep['comment']);
                        }else{
                            $results[$wi]['upstep'] = 'alternative';
                        }
                        break;
                    }
                }
            }
        }
        return $results;
    }
}
