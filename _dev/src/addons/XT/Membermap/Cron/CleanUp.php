<?php

namespace XT\Membermap\Cron;

class CleanUp
{
	/**
	 * Clean up tasks that should be done daily. This task cannot be relied on
	 * to run daily, consistently.
	 */
	public static function runDailyCleanUp()
	{
		$app = \XF::app();

		/** @var \XT\Membermap\Repository\Log $logRepo */
		$logRepo = $app->repository('XT\Membermap:Log');
		$logRepo->pruneLogs();
	}
}