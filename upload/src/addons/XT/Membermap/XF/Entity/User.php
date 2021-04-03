<?php

namespace XT\Membermap\XF\Entity;

use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
	public function canViewXtMembermap()
	{
		return $this->hasPermission('xt_membermap', 'view');
	}
}