<?php

namespace XT\Membermap\XF\Entity;

use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
	public function canViewXtMembermap()
	{
		return $this->hasPermission('xt_membermap', 'view');
	}

	public function getAbstractedMinimapPath()
	{
		$userId = $this->user_id;

		return sprintf(
			'data://xtminimap/%d/%d.png',
			floor($userId / 1000),
			$userId
		);
	}

	public function getMinimapUrl($canonical = false)
	{

		if (!$this->Profile->xt_mm_show_on_map)
		{
			return false;
		}

		$app = $this->app();
		$group = floor($this->user_id / 1000);
		return $app->applyExternalDataUrl(
			"xtminimap/{$group}/{$this->user_id}.png",
			$canonical
		);
	}

	public function removeMinimapIfExists()
	{
		// try to find a saved image
        $minimapPath = $this->getAbstractedMinimapPath();

        if (\XF\Util\File::abstractedPathExists($minimapPath)) {
            \XF\Util\File::deleteFromAbstractedPath($minimapPath);
        }
	}

	public function getStaticLocationImage($size = 'l')
	{
		// try to find a saved image
		$minimapUrl = $this->getMinimapUrl();
		$minimapPath = $this->getAbstractedMinimapPath();

		if ($minimapUrl AND !\XF\Util\File::abstractedPathExists($minimapPath)) 
		{
			$googleService = $this->app()->service('XT\Membermap::GoogleApi');
			$image = $googleService->fetchStaticLocationImageUser($this->Profile, $this->getMapMarkerIconPath());
			if (!\XF\Util\File::copyFileToAbstractedPath($image, $minimapPath)) 
			{
				$minimapUrl = false;
			}
		}

		return $minimapUrl;
	}

	public function getMapMarkerIconPath()
	{
		$iconFallback = 'styles/default/xt/membermap/map_markers/red-dot.png';
		$pather = \XF::app()['request.pather'];

		$userGroup = $this->finder('XF:UserGroup')->whereId($this->display_style_group_id)->fetchOne();
		
		if ($userGroup->xt_mm_markerPin)
		{
			$icon = $userGroup->xt_mm_markerPin;
		}
		elseif (\XF::options()->xtMMDefaultMapMarkerIcon)
		{
			$icon = \XF::options()->xtMMDefaultMapMarkerIcon;
		}
		else
		{
			$icon = $iconFallback;
		}
		$iconPath =	htmlspecialchars($pather ? $pather($icon, 'full') : $icon);
		
		return $iconPath;
	}
}