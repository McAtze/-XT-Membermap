<?php

namespace XT\Membermap\Repository;

use XF\Mvc\Entity\Repository;
use XF\Mvc\Entity\Finder;

class Log extends Repository
{
    /**
     * @return Finder
     */
    public function findApiLogForList()
    {
        return $this->finder('XT\Membermap:Log')
            ->with('User')
            ->setDefaultOrder('request_date', 'DESC');
    }

	public function getUsersInLog()
	{
		return $this->db()->fetchPairs("
			SELECT user.user_id, user.username
			FROM (
				SELECT DISTINCT user_id FROM xf_xt_mm_log
			) AS log
			INNER JOIN xf_user AS user ON (log.user_id = user.user_id)
			ORDER BY user.username
		");
	}
}