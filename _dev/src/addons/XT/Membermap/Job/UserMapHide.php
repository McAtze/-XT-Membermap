<?php

namespace XT\Membermap\Job;

use XF\Job\AbstractRebuildJob;

class UserMapHide extends AbstractRebuildJob
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
        $this->app->db()->update('xf_user_profile', [
			'xt_mm_show_on_map' => 0,
			'xt_mm_location_lat' => 0,
			'xt_mm_location_long' => 0
		], 'user_id = ?', $id);
	}

    protected function getStatusType()
	{
		return \XF::phrase('xt_mm_rebuilding_userMapShow');
	}
}