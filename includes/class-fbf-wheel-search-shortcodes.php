<?php


class Fbf_Wheel_Search_Shortcodes
{
    public function wheel_search($atts)
    {
        $html= Fbf_Wheel_Search_Public::manufacturers_dropdown();
        $html.= Fbf_Wheel_Search_Public::chasis_dropdown();

        return $html;
    }

    public function wheel_search_v2($atts)
    {
        $manu = Fbf_Wheel_Search_Public::manufacturers_dropdown_v2($atts['id']);
        $chassis = Fbf_Wheel_Search_Public::chasis_dropdown_v2($atts['id']);
        if(!empty($atts['bg-image'])){
            $bg_style = sprintf('style="background-image: url(\'%s\');"', $atts['bg-image']);
        }else{
            $bg_style = '';
        }
        $html = <<<HTML
<div id="wheel-search-widget-v2_{$atts['id']}" class="wheel-search-widget-v2">
    <h3 class="wheel-search-widget-v2__title d-lg-none">Search by vehicle</h3>
    <div class="wheel-search-widget-v2__content" {$bg_style}>
        <h3 class="wheel-search-widget-v2__heading d-none d-lg-block mt-2 mb-4" aria-hidden="true">Search by vehicle</h3>
        <form id="wheel-search-widget-v2--{$atts['id']}" class="wheel-search-widget-v2__form" action="post">
            <div class="wheel-search-widget-v2__row row no-gutters">
                {$manu}
                {$chassis}
            </div>
            <div class="wheel-search-widget-v2__row row no-gutters">
                <div class="fbf-form-group form-group col-12 col-lg-3 pr-lg-3">
                    <input type="text" id="fbf-wheel-search-postcode_{$atts['id']}" name="fbf-wheel-search-postcode" class="wheel-widget-v2__form-field fbf-wheel-search-postcode-v2 mb-2 mb-lg-0"/>
                    <label for="fbf-wheel-search-postcode_{$atts['id']}" class="control-label"><span class="floating-label">Postcode</span></label>
                </div>
                <div class="form-group col-12 col-lg-3 pl-lg-3 pr-lg-3 d-lg-flex flex-lg-column justify-content-lg-start">
                    <button id="tyre-reg-search--reg-button_{$atts['id']}" class="wheel-search-widget-v2__button" type="submit" rel="nofollow" role="button" disabled>
                        See products
                        <span class="icon">
                            <i class="fa fa-spinner fa-pulse"></i>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
HTML;
        return $html;
    }

    public function wheel_search_v3($attrs)
    {
        if(!is_array($attrs)){
            $id = '';
        }else{
            if(isset($attrs['id'])){
                $id = $attrs['id'];
            }else{
                $id = '1';
            }
        }
        $manu = Fbf_Wheel_Search_Public::manufacturers_dropdown_v3($id);
        $chassis = Fbf_Wheel_Search_Public::chasis_dropdown_v3($id);

        if(is_plugin_active('litespeed-cache/litespeed-cache.php')){
            $postcode_field = apply_filters('litespeed_esi_url', 'esi_postcode_form_block', 'Postcode form block', ['id' => sprintf('sc-fbf-wheel-search--postcode_%s', $id), 'type' => '', 'classes' => 'fbf-wheel-search-postcode-v2 sc-fbf-wheel-search__form-field']);
        }else{
            $postcode_field = sprintf('<input id="sc-fbf-wheel-search--postcode_%s" type="text" class="fbf-wheel-search-postcode-v2 sc-fbf-wheel-search__form-field postcode" data-search_postcode="%s" disabled />', $id, WC()->session->get('search_postcode'));
        }

        $html = <<<HTML
<div id="sc-fbf-wheel-search_{$id}" class="sc-fbf-wheel-search">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item flex-grow-1 d-flex">
            <a class="nav-link flex-grow-1 active" id="sc-fbf-wheel-search__vehicle-tab" data-toggle="tab" href="#sc-fbf-wheel-search--vehicle" role="tab" aria-controls="sc-fbf-wheel-search--vehicle" aria-selected="true">Search by vehicle</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="sc-fbf-wheel-search__tab-pane tab-pane fade show active" id="sc-fbf-wheel-search--vehicle" role="tabpanel" aria-labelledby="sc-fbf-wheel-search__vehicle-tab">
            
            <form id="sc-fbf-wheel-search__form-{$id}" class="sc-fbf-wheel-search__form" action="post">
                {$manu}
                {$chassis}
                <div class="fbf-form-group sc-fbf-wheel-search__form--row">
                    <select id="sc-fbf-wheel-search--size-fitting_{$id}" class="sc-fbf-wheel-search__form-field fitting" disabled>
                        <option value="">Fitted or delivered?</option>
                        <option value="fitted">Fitted</option>
                        <option value="delivered">Delivered</option>
                    </select>
                    <label for="sc-fbf-wheel-search--size-fitting_{$id}" class="control-label">
                        <span class="floating-label">Fitted or delivered?</span>
                    </label>
                </div>
                <div class="fbf-form-group sc-fbf-wheel-search__form--row">
                    {$postcode_field}
                    <label for="sc-fbf-wheel-search--postcode_{$id}" class="control-label">
                        <span class="floating-label">Postcode</span>
                    </label>
                </div>
                <div class="sc-fbf-wheel-search__form--row">
                    <button id="sc-fbf-wheel-search--size-button_{$id}" class="sc-fbf-wheel-search__button size" type="submit" rel="nofollow" role="button" disabled>
                        See products
                        <span class="icon">
                            <i class="fa fa-spinner fa-pulse"></i>
                        </span>
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</div>
HTML;
        return $html;
    }

    public function package_search($atts)
    {
        $html= Fbf_Wheel_Search_Public::manufacturers_dropdown_package();
        $html.= Fbf_Wheel_Search_Public::chasis_dropdown_package();

        return $html;
    }

    public function accessory_search($atts)
    {
        $manu = Fbf_Wheel_Search_Public::manufacturers_dropdown_accessories();
        $chassis = Fbf_Wheel_Search_Public::chasis_dropdown_accessories();
        $html= '<form class="fbf-accessory-search">';
        $html.= $manu;
        $html.= $chassis;
        $html.= '</form>';
        return $html;
    }

    public function accessory_search_v2($atts)
    {
        $manu = Fbf_Wheel_Search_Public::manufacturers_dropdown_accessories_v2($atts['id']);
        $chassis = Fbf_Wheel_Search_Public::chasis_dropdown_accessories_v2($atts['id']);
        if(!empty($atts['bg-image'])){
            $bg_style = sprintf('style="background-image: url(\'%s\');"', $atts['bg-image']);
        }else{
            $bg_style = '';
        }
        $html = <<<HTML
<div id="accessory-search-widget-v2_{$atts['id']}" class="accessory-search-widget-v2">
    <h3 class="accessory-search-widget-v2__title d-lg-none">Search by vehicle</h3>
    <div class="accessory-search-widget-v2__content" {$bg_style}>
        <h3 class="accessory-search-widget-v2__heading d-none d-lg-block mt-2 mb-4" aria-hidden="true">Search by vehicle</h3>
        <form id="accessory-search-widget-v2--{$atts['id']}" class="accessory-search-widget-v2__form" action="post">
            <div class="accessory-search-widget-v2__row row no-gutters">
                {$manu}
                {$chassis}
            </div>
            <div class="accessory-search-widget-v2__row row no-gutters">
                <div class="fbf-form-group form-group col-12 col-lg-3 pr-lg-3">
                    <input type="text" id="fbf-accessory-search-postcode_{$atts['id']}" name="fbf-accessory-search-postcode" class="accessory-widget-v2__form-field fbf-accessory-search-postcode-v2 mb-2 mb-lg-0"/>
                    <label for="fbf-accessory-search-postcode_{$atts['id']}" class="control-label"><span class="floating-label">Postcode</span></label>
                </div>
                <div class="form-group col-12 col-lg-3 pl-lg-3 pr-lg-3 d-lg-flex flex-lg-column justify-content-lg-start">
                    <button id="tyre-reg-search--reg-button_{$atts['id']}" class="accessory-search-widget-v2__button" type="button" rel="nofollow" role="submit" disabled>
                        See products
                        <span class="icon">
                            <i class="fa fa-spinner fa-pulse"></i>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
HTML;
        return $html;
    }
}
