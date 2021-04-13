<?php

namespace XT\Membermap\Widget;

use XF\Widget\AbstractWidget;

class MapStatistics extends AbstractWidget
{
    protected $defaultOptions = [
		'limit' => 2,
		'style' => 'name',
	];

    public function render()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if (!method_exists($visitor, 'canViewXtMembermap') || !$visitor->canViewXtMembermap()) {
			return '';
		}

		$userData = $this->contextParams['userdata'] ?? null;

		$options = $this->options;
		$limit = $options['limit'];
		$style = $options['style'];

		if (empty($userData))
		{
			$finder = $this->getUserProfileRepo()->findMapLocations();
			$total = $finder->total();
			if ($limit > 0) 
			{
				$UserProfiles = $finder->fetch(max($limit * 2, 10));
				$UserProfiles = $UserProfiles->slice(0, $limit, true);
			} 
			else 
			{
				$UserProfiles = $finder->fetch();
			}

			foreach ($UserProfiles as $user_id => $userProfile) 
			{
				$userData[$user_id] = [
					'user' => $userProfile->User
				];
			}
		}
		else
		{
			$total = count($userData);
			if ($limit > 0)
			{
				$userData = array_slice($userData, 0 , $limit, true);
			}
		}

		$unseen = ($limit ? max($total - $limit, 0) : 0);

        $viewParams = [
			'total' => $total,
			'unseen' => $unseen,
            'userData' => $userData,
			'style' => $style
		];
		
		return $this->renderer('xt_mm_widget_members_on_map', $viewParams);
	}

    public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'style' => 'str',
		]);

		return true;
	}

	/**
	 * @return \XF\Repository\UserGroup
	 */
	protected function getUserGroupRepo()
	{
		return $this->repository('XF:UserGroup');
	}

	/**
	 * @return \XF\Repository\UserProfile
	 */
	protected function getUserProfileRepo()
	{
		return $this->repository('XF:UserProfile');
	}
}