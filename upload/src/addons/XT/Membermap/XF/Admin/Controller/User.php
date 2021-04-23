<?php

namespace XT\Membermap\XF\Admin\Controller;

use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Entity\Entity;

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

	public function actionXtRemoveMinimap(ParameterBag $params)
    {
        $user = $this->assertUserExists($params->user_id);
		$this->assertCanEditUser($user);

        if ($this->isPost())
		{
			$user->removeMinimapIfExists();
			return $this->redirect($this->buildLink('users/edit', $user, ['success' => true]));
		}
		else
		{
			$viewParams = [
				'user' => $user
			];
			return $this->view('XT\Membermap:User\RemoveMinimap', 'xt_mm_user_remove_minimap', $viewParams);
		}
    }
}