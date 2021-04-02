<?php

namespace XT\Membermap;

use XF\Mvc\Entity\Entity;

class Listener
{
    public static function userProfileEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
		$structure->columns['xt_mm_location_lat'] = ['type' => Entity::FLOAT, 'default' => 0];
		$structure->columns['xt_mm_location_long'] = ['type' => Entity::FLOAT, 'default' => 0];
		$structure->columns['xt_mm_show_on_map'] = ['type' => Entity::BOOL, 'default' => true, 'changeLog' => false];
    }

	public static function userGroupEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
	{
		$structure->columns['xt_mm_markerPin'] = ['type' => Entity::STR, 'default' => NULL, 'nullable' => true];
	}

    public static function entityPreSaveUserProfile(\XF\Mvc\Entity\Entity $entity)
    {
		$location = $entity->location;

		/**
		 * @var \XT\Membermap\Service\GoogleApi $googleService
		 */
		$googleService = $entity->app()->service('XT\Membermap::GoogleApi');

		if (!$entity->xt_mm_show_on_map)
		{
			$entity->xt_mm_location_lat = 0;
			$entity->xt_mm_location_long = 0;
		}
		elseif ($location && $googleService->isAvaiable())
		{
			$entity->xt_mm_location_lat = isset($googleService->fetchLocationData($location)['latitude']) ? $googleService->fetchLocationData($location)['latitude'] : 0;
			$entity->xt_mm_location_long = isset($googleService->fetchLocationData($location)['longitude']) ? $googleService->fetchLocationData($location)['longitude'] : 0;
		}
		
    }
}