<?php

namespace XT\Membermap\Widget;

use XF\Widget\AbstractWidget;

class MapLegend extends AbstractWidget
{
    public function render()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
        $noView = !$visitor->canViewXtMembermap();
        $noShow = !$visitor->canShowXtMembermap();

        if (!method_exists($visitor, 'canViewXtMembermap') || $noView || $noShow) {
            return '';
        }

        $pather = \XF::app()['request.pather'];

        /** @var XT\Membermap\XF\Repository\UserGroup $userGroupRepo */
        $userGroupRepo = $this->getUserGroupRepo();
        $finder = $userGroupRepo->findUserGroupsWithMapMarker();
        $groupDatas = $finder->fetch();
        $groups = [];

        foreach($groupDatas AS $id => $groupData)
        {
            if($groupData['user_group_id'] !== 1)
            {
                $groupIcon = htmlspecialchars($pather ? $pather($groupData['xt_mm_markerPin'], 'base') : $groupData['xt_mm_markerPin']);
                $groups[$id] = [
                    'icon' => $groupIcon,
                    'title' => $groupData['title']
                ];
            }
        }

        $viewParams = [
            'groupData' => $groups,
        ];
		return $this->renderer('xt_mm_widget_map_legend', $viewParams);
	}

	public function getOptionsTemplate()
	{
		return;
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