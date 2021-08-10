<?php

namespace XT\Membermap\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;
use XF\Repository\UserGroup;
use XF\Repository\UserProfile;

use XT\Membermap\XF\Entity\User;

class Membermap extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        /** @var User $visitor */
		$visitor = \XF::visitor();
        $options = \XF::app()->options();

		if (!$visitor->canViewXtMembermap())
		{
			return $this->noPermission();
		}

        $UserProfilesFinder = $this->getUserProfileRepo()->findMapLocations();
        $total = $UserProfilesFinder->total();
        $UserProfiles = $UserProfilesFinder->fetch();

        $defCluster = 'styles/default/xt/membermap/map_markers/cluster';
        $clusterPath = $this->getIconDataUrl($defCluster);

        $userData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            /** Unset ignored users and user with no permission **/
            $noView = !$userProfile->User->canViewXtMembermap();
            $noShow = !$userProfile->User->canShowXtMembermap();
            if ($userProfile->isIgnored() || $noView || $noShow)
            {
                unset($UserProfiles[$user_id]);
                continue;
            }
            
            $userData[$user_id] = [
                'user' => $userProfile->User,
            ];
        }

        $viewParams = [
            'userData' => $userData,
            'total' => $total,
            'clusterPath' => $clusterPath,
        ];

        return $this->view('XT\Membermap:Index', 'xt_mm_index', $viewParams);
    }

    public function actionMapData($canonical = false)
    {
        $visitor = \XF::visitor();
        $options = \XF::app()->options();

        $this->assertPostOnly();

        $userGroupMarkers = $this->getUserGroupRepo()->findUserGroupsWithMapMarker()->fetch();
        $UserProfiles = $this->getUserProfileRepo()->findMapLocations()->fetch();

        /** Get path with function **/
        $defMarker = $options->xtMMDefaultMapMarkerIcon;
        $styleMarker = 'styles/default/xt/membermap/map_markers/red-dot.png';
        $iconUrl = (!empty($defMarker) ? $this->getIconDataUrl($defMarker) : $this->getIconDataUrl($styleMarker));

        $mapData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            /** Unset ignored users and user with no permission **/
            $noView = !$userProfile->User->canViewXtMembermap();
            $noShow = !$userProfile->User->canShowXtMembermap();
            if ($userProfile->isIgnored() || $noView || $noShow)
            {
                unset($UserProfiles[$user_id]);
                continue;
            }
            
            if (!empty($userGroupMarkers[$userProfile->User->display_style_group_id]))
            {
                $userGroupMarker = $userGroupMarkers[$userProfile->User->display_style_group_id]->xt_mm_markerPin;
                $groupIcon = $this->getIconDataUrl($userGroupMarker);
                $iconMarker = ($groupIcon ?: $iconUrl);
            }
            else
            {
                $iconMarker = $iconUrl;
            }

            if($visitor->user_id)
            {
                $mapData[$user_id] = [
                    'coords' => [
                        'lat' => $userProfile->xt_mm_location_lat,
                        'lng' => $userProfile->xt_mm_location_long,
                    ],
                    'iconUrl' => [
                        'url' => $iconMarker,
                    ],
                    'title' => $userProfile->User->username,
                    'infoUrl' => $this->buildLink('members',$userProfile->User, ['tooltip' => true]),
                    'content' => '',
                ];
            }
        }

        $jsonParams = [
            'mapData' => $mapData,
        ];

        $reply = $this->view('XT\Membermap:MapData','', []);
        $reply->setJsonParams($jsonParams);

        return $reply;
    }

    /**
     * @param string $iconData icon path
     * Make $pather as a function
     * @return string working image path
     * */
    protected function getIconDataUrl($iconData)
    {
        $pather = \XF::app()['request.pather'];
        $result = htmlspecialchars($pather ? $pather($iconData, 'base') : $iconData);

        return $result;
    }

    public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xt_mm_viewing_membermap');
	}

    /**
     * @return UserGroup
     */
    protected function getUserGroupRepo()
    {
        return $this->repository('XF:UserGroup');
    }

    /**
     * @return UserProfile
     */
    protected function getUserProfileRepo()
    {
        return $this->repository('XF:UserProfile');
    }

}
