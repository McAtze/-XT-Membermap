<?php

namespace XT\Membermap\Widget;

use XF\Widget\AbstractWidget;

class MapStatistics extends AbstractWidget
{
    public function render()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewXtMembermap') || !$visitor->canViewXtMembermap())
		{
			return '';
		}

        $options = $this->options;
		$limit = $options['limit'];

        /** @var XF\Mvc\Entity\Finder $finder */
        $finder = $this->finder('XF:UserProfile');
		$finder
            ->with('User')
            ->keyedBy('user_id')
            ->where('xt_mm_show_on_map', '=', 1)
            ->where('xt_mm_location_lat', '<>', 0)
            ->where('xt_mm_location_long', '<>', 0);

        $UserProfiles = $finder->fetch();
        $total = count($UserProfiles);

        $userData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            $userData[$user_id] = [
                'user' => $userProfile->User
            ];
        }

        $viewParams = [
			'total' => $total,
            'userData' => $userData,
		];
		return $this->renderer('xt_mm_widget_members_on_map', $viewParams);
	}

	public function getOptionsTemplate()
	{
		return;
	}
}