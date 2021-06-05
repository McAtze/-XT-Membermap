<?php

namespace XT\Membermap\Option;

use XF\Entity\Option;
use XF\Option\AbstractOption;

class GoogleLatLng extends AbstractOption
{
    public static function verifyOption(&$value, Option $option)
    {
        if (empty($value['lat']))
        {
            $option->error(\XF::phrase('please_enter_value_for_required_field_x', ['field' => 'Default Lat/Long [Latitude]']), $option->option_id);
            return false;
        }
        if (empty($value['long']))
        {
            $option->error(\XF::phrase('please_enter_value_for_required_field_x', ['field' => 'Default Lat/Long [Longitude]']), $option->option_id);
            return false;
        }

        return true;
    }
}