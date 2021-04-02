<?php

namespace XT\Membermap\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\PrintableException;

class Membermap extends Repository
{
    public function getUserLocation($userId)
	{
		return $this->db()->fetchOne("
			SELECT location
			FROM xf_user_profile
			WHERE user_id = ?
		", $userId);
	}
}