<?php

namespace XT\Membermap\Option;

use XF\Option\AbstractOption;

class GoogleApi extends AbstractOption
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public static function verifyApiKey(&$value, \XF\Entity\Option $option)
    {
        if (!empty($value))
        {
            /**
             * @var \XT\Membermap\Service\GoogleApi $googleService
             */
            $googleService = \XF::app()->service('XT\Membermap::GoogleApi');
            
            if (!$googleService->isAvaiable($value))
            {
                $option->error(\XF::phrase('xt_mm_the_api_code_seems_to_be_invalid'), $option->option_id);
                return false;
            }
        }

        return true;
    }
}
