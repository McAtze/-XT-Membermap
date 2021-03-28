<?php

namespace XT\Membermap\Pub\Controller;

use XF\Mvc\ParameterBag;

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

        $viewParams = [
            'mapUsers' => $UserProfiles,
        ];
        return $this->view('XT\Membermap:Index', 'xt_mm_index', $viewParams);
    }

    public function actionMapData($canonical = false)
    {
        $app = \XF::app();
		$options = $app->options();

        $this->assertPostOnly();

        $UserProfiles = $this->findMapLocations()->fetch();

        $defaultMapPin = ($options->xtMMDefaultMapMarkerIcon ? $options->xtMMDefaultMapMarkerIcon : 'styles/default/xt/membermap/map_markers/red-dot.png');

        $defaultLat = $options->xtMMdefaultLatLong['lat'];
        $defaultLong = $options->xtMMdefaultLatLong['long'];
        $defaultZoom = $options->xtMMdefaultZoom;

        $mapData = [];

        foreach($UserProfiles as $user_id => $userProfile)
        {
            $mapData[$user_id] = [
                'coords' => [
                    'lat' => $userProfile->xt_mm_location_lat,
                    'lng' => $userProfile->xt_mm_location_long,
                ],
                'iconUrl' => [
                    //'url' => $app->applyExternalDataUrl($defaultMapPin, $canonical),
                    'url' => $options->boardUrl .'/'. $defaultMapPin,
                ],
                'title' => '',
                'content' => $this->app->templater()->renderTemplate(
                    'public:xt_mm_useritem', 
                    ['user' => $userProfile->User]
                ),
            ];
        }

        $jsonParams = [
            'mapData' => $mapData,
            'defaultLat' => $defaultLat,
            'defaultLong' => $defaultLong,
            'defaultZoom' => $defaultZoom
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

    public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xt_mm_viewing_membermap');
	}
}