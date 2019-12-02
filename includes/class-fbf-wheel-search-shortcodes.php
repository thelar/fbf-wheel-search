<?php


class Fbf_Wheel_Search_Shortcodes
{
    public function wheel_search($atts)
    {
        $html= Fbf_Wheel_Search_Public::manufacturers_dropdown();
        $html.= Fbf_Wheel_Search_Public::chasis_dropdown();

        return $html;
    }

    public function package_search($atts)
    {
        $html= Fbf_Wheel_Search_Public::manufacturers_dropdown_package();
        $html.= Fbf_Wheel_Search_Public::chasis_dropdown_package();

        return $html;
    }
}
