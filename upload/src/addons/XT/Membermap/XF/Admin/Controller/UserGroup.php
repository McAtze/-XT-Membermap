<?php 

namespace XT\Membermap\XF\Admin\Controller;

use XF\Mvc\FormAction;

class UserGroup extends XFCP_UserGroup
{
	protected function userGroupSaveProcess(\XF\Entity\UserGroup $userGroup)
	{
		$markerPin = $this->filter(
			['userGroup' => [
				'xt_mm_markerPin' => 'str',
				]
			]
		);
		
		$formAction = parent::userGroupSaveProcess($userGroup);
		$formAction->setup(function() use ($userGroup, $markerPin)
		{
			$userGroup->xt_mm_markerPin = $markerPin['userGroup']['xt_mm_markerPin'];
		});
		
		return $formAction;
	}
}