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
				ORDER BY user_id
			", $batch
		), $start);
	}

	protected function rebuildById($id)
	{
        /** @var \XT\Membermap\Repository\Membermap $userRepo */
		$userRepo = $this->app->repository('XT\Membermap:Membermap');
		$userLocation = $userRepo->getUserLocation($id);

		if(!empty($userLocation))
		{
			/**
			 * @var \XT\Membermap\Service\GoogleApi $googleService
			 */
			$googleService = $this->app->service('XT\Membermap::GoogleApi');

			$xt_mm_location_lat = isset($googleService->fetchLocationData($userLocation)['latitude']) ? $googleService->fetchLocationData($userLocation)['latitude'] : 0;
			$xt_mm_location_long = isset($googleService->fetchLocationData($userLocation)['longitude']) ? $googleService->fetchLocationData($userLocation)['longitude'] : 0;

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