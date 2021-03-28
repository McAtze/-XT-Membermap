<?php

namespace XT\Membermap\XF\Pub\Controller;

class Account extends XFCP_Account
{
	protected function accountDetailsSaveProcess(\XF\Entity\User $visitor)
	{
		$form = parent::accountDetailsSaveProcess($visitor);
		
		$input = $this->filter([
			'profile' => [
				'xt_mm_show_on_map' => 'bool',
				'xt_mm_location_lat' => 'float',
				'xt_mm_location_long' => 'float'
			],
		]);

		/** @var \XF\Entity\UserProfile $userProfile */
		$userProfile = $visitor->getRelationOrDefault('Profile');
		$form->setupEntityInput($userProfile, $input['profile']);
		
		return $form;
	}
}