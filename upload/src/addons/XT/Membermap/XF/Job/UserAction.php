<?php

namespace XT\Membermap\XF\Job;

use XF\Entity\User;
use XF\Entity\UserProfile;

class UserAction extends XFCP_UserAction
{
    protected $defaultData = [
        'actions' => []
    ];

    protected function executeAction(User $user)
    {
        if ($user->is_super_admin)
        {
            return; // no updating of super admins
        }

        if ($this->getActionValue('delete'))
        {
            if (!$user->is_admin && !$user->is_moderator)
            {
                $user->delete(false, false);
            }
            return; // no further action required
        }

        $this->applyInternalUserChange($user);
        $user->save(false, false);

        $this->applyExternalUserChange($user);
    }

    protected function applyInternalUserChange(User $user)
    {
        parent::applyInternalUserChange($user);

        /** @var UserProfile $profile */
        $profile = $user->getRelationOrDefault('Profile', false);

        if ($this->getActionValue('xt_mm_empty_location'))
        {
            $profile->location = '';
            $user->addCascadedSave($profile);
        }

        if ($this->getActionValue('xt_mm_hiding_from_map'))
        {
            $profile->xt_mm_show_on_map = '0';
            $user->addCascadedSave($profile);
        }
    }
}