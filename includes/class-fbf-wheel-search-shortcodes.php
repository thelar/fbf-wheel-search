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
