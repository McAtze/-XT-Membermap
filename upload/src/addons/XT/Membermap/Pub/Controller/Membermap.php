<?php

namespace XT\Membermap\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Dispatcher;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Router;
use XF\Util\Arr;

class Membermap extends \XF\Pub\Controller\AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        /** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
        
		if (!$visitor->canViewXtMembermap())
		{
			return $this->noPermission();
		}
        $UserProfiles = $this->findMapLocations()->fetch();
        $total = count($UserProfiles);

        $userData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            $userData[$user_id] = [
                'user' => $userProfile->User
            ];
        }

        $viewParams = [
            'userData' => $userData,
            'total' => $total,
        ];
        return $this->view('XT\Membermap:Index', 'xt_mm_index', $viewParams);
    }

    public function actionMapData($canonical = false)
    {
        $app = \XF::app();
		$options = $app->options();

        $pather = \XF::app()['request.pather'];

        $this->assertPostOnly();

        $UserProfiles = $this->findMapLocations()->fetch();

        if($options->xtMMDefaultMapMarkerIcon)
        {      
            $iconUrl = htmlspecialchars($pather ? $pather($options->xtMMDefaultMapMarkerIcon, 'base') : $options->xtMMDefaultMapMarkerIcon);
        }
        else
        {
            $iconUrl = htmlspecialchars($pather ? $pather('styles/default/xt/membermap/map_markers/red-dot.png', 'base') : 'styles/default/xt/membermap/map_markers/red-dot.png');
        }

        $clusterPath = htmlspecialchars($pather ? $pather('styles/default/xt/membermap/map_markers/cluster', 'base') : 'styles/default/xt/membermap/map_markers/cluster');

        $mapData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            $mapData[$user_id] = [
                'coords' => [
                    'lat' => $userProfile->xt_mm_location_lat,
                    'lng' => $userProfile->xt_mm_location_long,
                ],
                'iconUrl' => [
                    'url' => $iconUrl,
                ],
                'clusterPath' => $clusterPath,
                'title' => '',
                'content' => $this->app->templater()->renderTemplate(
                    'public:xt_mm_useritem', 
                    ['user' => $userProfile->User]
                ),
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
     * @return XF\Mvc\Entity\Finder
     */
    public function findMapLocations()
    {
        /**
         * @var Finder $locationFinder
         */
        $locationFinder = \XF::finder('XF:UserProfile')
            ->with('User')
            ->keyedBy('user_id')
            ->where('xt_mm_show_on_map', '=', 1)
            ->where('xt_mm_location_lat', '<>', 0)
            ->where('xt_mm_location_long', '<>', 0);

        return $locationFinder;
    }

    public function getUserGroupId()
    {
        /**
         * @var Finder $userGroup
         */
        $userGroup = \XF::finder('XF:UserGroup')
            ->where('xt_mm_markerPin', '<>', '');

        return $userGroup;
    }

    public function getUserGroupMarker(\XF\Entity\User $user = null)
	{
		
	}

    public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xt_mm_viewing_membermap');
	}
}