<?php

namespace XT\Membermap\XF\Searcher;

use XF\Mvc\Entity\Finder;

class User extends XFCP_User
{
    protected function applySpecialCriteriaValue(Finder $finder, $key, $value, $column, $format, $relation)
    {
        if ($key == 'xt_mm_location')
        {
            if (!is_array($value))
            {
                $value = [$value];
            }

            $conditions = [];

            if (in_array('0', $value))
            {
                $conditions[] = ['Profile.location', '=', ''];
            }
            if (in_array('1', $value))
            {
                $conditions[] = ['Profile.location', '<>', ''];
            }

            if (count($conditions) < 2)
            {
                $finder->whereOr($conditions);
            }

            return true;
        }

        if ($key == 'xt_mm_show_on_map')
        {
            if (!is_array($value))
            {
                $value = [$value];
            }

            $conditions = [];

            if (in_array('0', $value))
            {
                $conditions[] = ['Profile.xt_mm_show_on_map', '=', '0'];
            }
            if (in_array('1', $value))
            {
                $conditions[] = ['Profile.xt_mm_show_on_map', '=', '1'];
            }

            if (count($conditions) < 2)
            {
                $finder->whereOr($conditions);
            }

            return true;
        }

        return parent::applySpecialCriteriaValue($finder, $key, $value, $column, $format, $relation);
    }
}