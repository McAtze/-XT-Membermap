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

		/** @var \XT\Membermap\Service\GoogleApi $googleService **/
		$googleService = $entity->app()->service('XT\Membermap::GoogleApi');

		if (!$entity->xt_mm_show_on_map)
		{
			$entity->xt_mm_location_lat = 0;
			$entity->xt_mm_location_long = 0;
            $entity->User->removeMinimapIfExists();
		}
		elseif ($location && $googleService->isAvaiable())
		{
			if ($entity->getPreviousValue('location') != $location || ($entity->getPreviousValue('xt_mm_location_lat') == '0' && $entity->getPreviousValue('xt_mm_location_long') == '0'))
			{
				$locationData = $googleService->fetchLocationData($location);
				if (is_array($locationData) && array_key_exists('latitude', $locationData))
				{
					$entity->xt_mm_location_lat = $locationData['latitude'];
					$entity->xt_mm_location_long = $locationData['longitude'];
				}
			}
			else
			{
				$entity->xt_mm_location_lat = $entity->getPreviousValue('xt_mm_location_lat');
				$entity->xt_mm_location_long = $entity->getPreviousValue('xt_mm_location_long');
			}
		}
    }

	public static function criteriaUser($rule, array $data, \XF\Entity\User $user, &$returnValue)
	{
		switch ($rule) {
			case 'xt_mm_show_in_map':
				if (!empty($user->Profile->xt_mm_show_on_map)) {
					$returnValue = true;
				}
				break;

			case 'xt_mm_hide_in_map':
				if (empty($user->Profile->xt_mm_show_on_map)) {
					$returnValue = true;
				}
				break;

			case 'location':
				if (!empty($user->Profile->location)) {
					$returnValue = true;
				}
				break;

			case 'not_location':
				if (empty($user->Profile->location)) {
					$returnValue = true;
				}
				break;
		}
	}

	public static function templaterSetup(\XF\Container $container, \XF\Template\Templater &$templater)
	{
		/** @var \XT\Membermap\Template\TemplaterSetup $templaterSetup */
		$class = \XF::extendClass('XT\Membermap\Template\TemplaterSetup');
		$templaterSetup = new $class();

		$templater->addFunction('xt_minimap', [$templaterSetup, 'fnXtMinimap']);
	}
}