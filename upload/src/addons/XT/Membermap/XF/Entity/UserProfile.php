<?php

namespace XT\Membermap\XF\Entity;

use XF\Mvc\Entity\Structure;

class UserProfile extends XFCP_UserProfile
{
    public function isIgnored()
    {
        return \XF::visitor()->isIgnoring($this->user_id);
    }
}