<?php

namespace XT\Membermap\XF\Admin\Controller;

class User extends XFCP_User
{
	protected function userSaveProcess(\XF\Entity\User $user)
	{
		$form = parent::userSaveProcess($user);
		
		$input = $this->filter([
			'profile' => [
				'xt_mm_show_on_map' => 'bool',
				'xt_mm_location_lat' => 'float',
				'xt_mm_location_long' => 'float'
			],
		]);
		
		/** @var \XF\Entity\UserProfile $userProfile */
		$userProfile = $user->getRelationOrDefault('Profile');
		$userProfile->setOption('admin_edit', true);
		$form->setupEntityInput($userProfile, $input['profile']);
		
		return $form;
	}
}