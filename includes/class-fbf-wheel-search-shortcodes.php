<?php


class Fbf_Wheel_Search_Shortcodes
{
    public function wheel_search($atts)
    {
        $html = '<div class="mb-5">';
        $html.= Fbf_Wheel_Search_Public::manufacturers_dropdown();
        $html.= Fbf_Wheel_Search_Public::chasis_dropdown();
        $html.= '</div>';

        return $html;
    }
}
