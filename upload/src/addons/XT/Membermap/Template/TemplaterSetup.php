<?php

namespace XT\Membermap\Template;

class TemplaterSetup
{
	public function fnXtMinimap($templater, &$escape, \XT\Membermap\XF\Entity\User $user, $size = 'm', $href = '', $attributes = [])
	{
		$escape = false;

		$size = preg_replace('#[^a-zA-Z0-9_-]#s', '', $size);

		if ($href)
		{
			$tag = 'a';
			$hrefAttr = 'href="' . htmlspecialchars($href) . '"';
		}
		else
		{
			$tag = 'span';
			$hrefAttr = '';
		}

		/** @var \XF\Template\Templater $templater */
		$attributesString = $templater->getAttributesAsString($attributes);
		$mapUrl = $user->getStaticLocationImage();

		if ($mapUrl)
		{
			return "<{$tag} {$hrefAttr} class=\"xt-mimimap xt-minimap--{$size}\"{$attributesString}>"
				. '<img src="' . htmlspecialchars($mapUrl) . '" alt="'.\XF::phrase('xt_mm_minimap').'" />'
				. "</{$tag}>";
		}
		else
		{
			return '';
		}

	}
}