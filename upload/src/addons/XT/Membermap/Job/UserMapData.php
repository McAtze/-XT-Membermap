<?php

namespace XT\Membermap\Job;

use XF\Job\AbstractRebuildJob;

class UserMapData extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
	{
		$db = $this->app->db();

		return $db->fetchAllColumn($db->limit(
			"
				SELECT user_id
				FROM xf_user_profile
				WHERE user_id > ?
					AND xt_mm_show_on_map = 1
				ORDER BY user_id
			", $batch
		), $start);
	}

	protected function rebuildById($id)
	{
		/** @var \XF\Repository\UserProfile $userProfileRepo */
		$userProfileRepo = $this->app->repository('XF:UserProfile');
		$userLocation = $userProfileRepo->fetchUserLocationById($id)->fetchOne();

		//\XF::dump($userLocation);

		if(!empty($userLocation))
		{
			/**
			 * @var \XT\Membermap\Service\GoogleApi $googleService
			 */
			$googleService = \XF::app()->service('XT\Membermap::GoogleApi');
			$locationData = $googleService->fetchLocationData($userLocation);

			if (!empty($locationData) 
				&& array_key_exists('latitude', $locationData) 
				&& array_key_exists('longitude', $locationData)
			)
			{
				$xt_mm_location_lat = $locationData['latitude'];
				$xt_mm_location_long = $locationData['longitude'];

			}
			else
			{
				$xt_mm_location_lat = 0;
				$xt_mm_location_long = 0;
			}

			$this->app->db()->update('xf_user_profile', [
				'xt_mm_location_lat' => $xt_mm_location_lat,
				'xt_mm_location_long' => $xt_mm_location_long
			], 'user_id = ?', $id);
		}
	}

    protected function getStatusType()
	{
		return \XF::phrase('xt_mm_rebuilding_userMapData');
	}
}