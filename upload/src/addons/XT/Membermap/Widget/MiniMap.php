<?php

namespace XT\Membermap\Widget;

use XF\Widget\AbstractWidget;

class MiniMap extends AbstractWidget
{
    public function render()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if (!method_exists($visitor, 'canViewXtMembermap') || !$visitor->canViewXtMembermap())
		{
			return '';
		}

        if ($visitor->Profile->xt_mm_show_on_map != 1)
        {
            return '';
        }

        $viewParams = [
            // 'image' => $visitor->getStaticLocationImage(),
        ];
		return $this->renderer('xt_mm_widget_minimap', $viewParams);
	}

	public function getOptionsTemplate()
	{
		return;
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