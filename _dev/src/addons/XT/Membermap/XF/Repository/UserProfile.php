<?php 

namespace XT\Membermap\XF\Repository;

/**
 * Class UserProfile
 * @package XT\Membermap\XF\Repository
 */
class UserProfile extends XFCP_UserProfile
{
    /** @return XF\Mvc\Entity\Finder **/
    public function findMapLocations()
    {
        $finder = $this->finder('XF:UserProfile');

        $finder->with('User')
            ->where('User.is_banned', false)
            ->where('User.user_state', 'valid');
        
        $activeLimit = $this->options()->xtMMuserActivity;
        if (!empty($activeLimit['enabled']))
		{
			$finder->where('User.last_activity', '>=', \XF::$time - 86400 * $activeLimit['days']);
		}
        
        $finder->keyedBy('user_id')
            ->where('xt_mm_show_on_map', '=', 1)
            ->where('xt_mm_location_lat', '<>', 0)
            ->where('xt_mm_location_long', '<>', 0);

        return $finder;
    }

    public function fetchUserLocationById(int $user_id)
    {
        return $this->finder('XF:UserProfile')->whereId('user_id', $user_id);
    }
}