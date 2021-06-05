<?php

namespace XT\Membermap\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Dispatcher;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Router;
use XF\Util\Arr;

class Membermap extends \XF\Pub\Controller\AbstractController
{

    public function actionIndex(ParameterBag $params)
    {
        /** @var \XT\Membermap\XF\Entity\User $visitor */
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
            /** Unset ignored user **/
            if ($userProfile->isIgnored()) {
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
            $noPerm = !$userProfile->User->canViewXtMembermap();
            if ($userProfile->isIgnored() OR $noPerm)
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
