<?php

namespace XT\Membermap\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null log_id
 * @property int user_id
 * @property int request_date
 * @property string request_url_
 * @property string request_status
 * @property array request_data
 * @property string ip_address
 *
 * GETTERS
 * @property string request_url
 * @property string request_url_short
 *
 * RELATIONS
 * @property \XF\Entity\User User
 */

class Log extends Entity
{
    /**
	 * @return string
	 */
	public function getRequestUrl()
	{
		$url = rawurldecode($this->request_url_);
		if (!preg_match('/./su', $url))
		{
			$url = $this->request_url_;
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function getRequestUrlShort()
	{
		$url = $this->request_url;

		$length = utf8_strlen($url);
		if ($length > 80)
		{
			$zwsp = chr(0xE2) . chr(0x80) . chr(0x8B);
			$url = utf8_substr_replace($url, '...' . $zwsp, 25, $length - 25 - 35);
		}

		return $url;
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xt_mm_log';
		$structure->shortName = 'XT:MMLog';
		$structure->primaryKey = 'log_id';

		$structure->columns = [
			'log_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'request_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'request_url' => ['type' => self::STR, 'required' => true],
            'request_status' => ['type' => self::STR, 'required' => true],
			'request_data' => ['type' => self::JSON_ARRAY, 'default' => []]
		];
		$structure->getters = [
			'request_url' => true,
			'request_url_short' => true
		];
		$structure->relations = [
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
		];

		return $structure;
	}
}