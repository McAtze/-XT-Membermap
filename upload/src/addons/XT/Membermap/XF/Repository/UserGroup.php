<?php 

namespace XT\Membermap\XF\Repository;

/**
 * Class UserGroup
 * @package XT\Membermap\XF\Repository
 */
class UserGroup extends XFCP_UserGroup
{
	/**
	 * @deprecated
	 */
	public function getGroupMarker(\XF\Entity\User $user)
    {
        $groups = $user->secondary_group_ids;
		$groups[] = $user->user_group_id;

		$groups = array_unique($groups);
		sort($groups, SORT_NUMERIC);

        $result = $this->db()->fetchOne("
			SELECT xt_mm_markerPin
			FROM xf_user_group
			WHERE user_group_id IN (" . $this->db()->quote($groups) . ")
			LIMIT 1
		");
		if (!$result)
		{
			$result = end($groups);
		}

        return $result;
    }

	public function findUserGroupsWithMapMarker()
	{
		return $this->findUserGroupsForList()->where('xt_mm_markerPin','<>','');
	}
}