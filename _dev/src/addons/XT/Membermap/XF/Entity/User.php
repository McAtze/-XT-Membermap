<?php

namespace XT\Membermap\XF\Entity;

use XF\Mvc\Entity\Structure;
use XF\Mvc\Entity\Finder;

class User extends XFCP_User
{
	public function canViewXtMembermap()
	{
		return $this->hasPermission('xt_membermap', 'view');
	}

    public function canShowXtMembermap()
    {
        return $this->hasPermission('xt_membermap', 'show');
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

    public function removeLocationData($save = false)
    {
        $this->Profile->xt_mm_show_on_map = 0;
        $this->Profile->xt_mm_location_lat = 0;
        $this->Profile->xt_mm_location_long = 0;
        if ($save)
        {
			$this->removeMinimapIfExists();
			$this->Profile->save();
        }
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
			if (\XF::options()->xtMMGoogleMapsApiKey === '')
			{
				return false;
			}

			$googleService = $this->app()->service('XT\Membermap::GoogleApi');
			$imageUrl = $googleService->fetchStaticLocationImageUser($this->Profile, $this->getMapMarkerIconPath());

			$error = null;
			$streamFile = \XF\Util\File::getTempFile();

			try
			{
				$options = [
					'headers' => [
						'Accept' => 'image/*,*/*;q=0.8',
					],
				];
				$limits = [
					'time' => 8,
					'bytes' => 100 * 1024,
				];

				$response = $this->app()->http()->reader()->getUntrusted(
					$imageUrl,
					$limits,
					$streamFile,
					$options,
					$error
				);
			}
			catch (\Exception $e)
			{
				\XF::logException($e);

				return false;
			}

			if ($response)
			{
				$response->getBody()->close();

				if ($response->getStatusCode() !== 200)
				{
					\XF::logError("Failed to fetch Minimap from $imageUrl");
					return false;
				}
			}

			if (!\XF\Util\File::copyFileToAbstractedPath($streamFile, $minimapPath))
			{
				return false;
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