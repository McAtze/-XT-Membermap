<?php

namespace XT\Membermap\Widget;

use XF\Widget\AbstractWidget;

class MiniMap extends AbstractWidget
{
    protected $defaultOptions = [
        'xt_mm_location' => true,
        'xt_mm_latlong' => true
    ];

    public function render()
	{
		/** @var \XT\Membermap\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
        $noView = !$visitor->canViewXtMembermap();
        $noShow = !$visitor->canShowXtMembermap();

        if (!method_exists($visitor, 'canViewXtMembermap') || $noView || $noShow) {
            return '';
        }

        if ($visitor->Profile->xt_mm_show_on_map != 1)
        {
            return '';
        }

        $options = $this->options;
        $location = $options['xt_mm_location'];
        $latlong = $options['xt_mm_latlong'];

        $viewParams = [
            'user' => $visitor,
            'location' => $location,
            'latlong' => $latlong,
            // 'image' => $visitor->getStaticLocationImage(),
        ];
		return $this->renderer('xt_mm_widget_minimap', $viewParams);
	}

    public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
    {
        $options = $request->filter([
            'xt_mm_location' => 'bool',
            'xt_mm_latlong' => 'bool'
        ]);
        return true;
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