<?php

namespace XT\Membermap;

use XF\Mvc\Entity\Entity;
use XF\Service\User\DeleteCleanUp;
use XF\Service\User\Merge;

class Listener
{
    public static function userProfileEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
		$structure->columns['xt_mm_location_lat'] = ['type' => Entity::FLOAT, 'default' => 0, 'changeLog' => false];
		$structure->columns['xt_mm_location_long'] = ['type' => Entity::FLOAT, 'default' => 0, 'changeLog' => false];
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

	public static function entityPostSaveUser(\XF\Mvc\Entity\Entity $entity)
	{
		$userChanges = ($entity->isChanged(['user_group_id', 'secondary_group_ids']) || $entity->isStateChanged('user_state', 'disabled') === 'enter');
		
		if ($userChanges 
			&& $entity->Profile->xt_mm_show_on_map 
			&& !$entity->canViewXtMembermap()
		)
		{
			$entity->removeLocationData(true);
		}

		if ($entity->isChanged(['user_group_id', 'secondary_group_ids', 'permission_combination_id', 'user_state']))
		{
			$entity->clearCache('PermissionSet');
		}
	}

	public static function entityPostSaveUserBan(\XF\Mvc\Entity\Entity $entity)
	{
		$user = $entity->User;		
		$user->removeLocationData(true);
	}

	public static function entityPostDeleteUser(\XF\Mvc\Entity\Entity $entity)
	{
		$entity->removeMinimapIfExists();
	}

	public static function userDeleteCleanInit(\XF\Service\User\DeleteCleanUp $deleteService, array &$deletes)
    {
        $deletes['xf_xt_mm_log'] = 'user_id = ?';
    }

    public static function userMergeCombine(\XF\Entity\User $target, \XF\Entity\User $source, \XF\Service\User\Merge $mergeService)
	{
		if($target->Profile->xt_mm_show_on_map && $source->Profile->xt_mm_show_on_map)
		{
			$target->Profile->xt_mm_show_on_map += $source->Profile->xt_mm_show_on_map;
			$target->Profile->xt_mm_location_lat += $source->Profile->xt_mm_location_lat;
			$target->Profile->xt_mm_location_long += $source->Profile->xt_mm_location_long;
			$source->removeMinimapIfExists();
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
	
	/**
	 * @return \XT\Membermap\XF\Entity\User
	 */
	public static function visitor()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor;
	}
}