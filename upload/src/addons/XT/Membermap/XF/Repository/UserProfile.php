<?php 

namespace XT\Membermap\XF\Repository;

/**
 * Class UserProfile
 * @package XT\Membermap\XF\Repository
 */
class UserProfile extends XFCP_UserProfile
{
    /** @return XF\Mvc\Entity\Finder **/
    public function findMapLocations($days = 180)
    {
        return $this->finder('XF:UserProfile')
            ->with('User')
            ->where('User.is_banned', false)
            ->where('User.user_state', 'valid')
            ->where('User.last_activity', '>', time() - ($days * 86400))
            ->keyedBy('user_id')
            ->where('xt_mm_show_on_map', '=', 1)
            ->where('xt_mm_location_lat', '<>', 0)
            ->where('xt_mm_location_long', '<>', 0);
    }

    public function fetchUserLocationById(int $user_id)
    {
        return $this->finder('XF:UserProfile')->whereId('user_id', $user_id);
    }
}