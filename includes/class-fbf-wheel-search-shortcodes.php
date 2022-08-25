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
        $html = <<<HTML
<div id="wheel-search-widget-v2_{$atts['id']}" class="wheel-search-widget-v2">
    <h3 class="wheel-search-widget-v2__title d-lg-none">Search by vehicle</h3>
    <div class="wheel-search-widget-v2__content">
        <h3 class="wheel-search-widget-v2__heading d-none d-lg-block mt-2 mb-4" aria-hidden="true">Search by vehicle</h3>
        <div class="wheel-search-widget-v2__row row no-gutters">
            {$manu}
            {$chassis}
        </div>
        <div class="wheel-search-widget-v2__row row no-gutters">
            <div class="form-group col-12 col-lg-3 pr-lg-3">
                <label for="fbf-wheel-search-postcode_{$atts['id']}">Postcode</label>
                <input type="text" id="fbf-wheel-search-postcode_{$atts['id']}" name="fbf-wheel-search-postcode" class="mb-2 mb-lg-0"/>
            </div>
            <div class="form-group col-12 col-lg-3 pl-lg-3 pr-lg-3 d-lg-flex flex-lg-column justify-content-lg-end">
                <button id="tyre-reg-search--reg-button_{$atts['id']}" class="wheel-search-widget-v2__button" type="button" rel="nofollow" role="button" disabled>
                    See products
                    <span class="icon">
                        <i class="fa fa-spinner fa-pulse"></i>
                    </span>
                </button>
            </div>
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
}
