<?php

namespace XT\Membermap\Admin\Controller;

use XF\Mvc\Entity\Finder;
use XF\Mvc\ParameterBag;
use XF\Admin\Controller\AbstractController;

class Log extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		$this->assertAdminPermission('viewLogs');
		
        if ($params->log_id)
		{
			$entry = $this->assertLogExists($params->log_id, null, 'requested_log_entry_not_found');

			$viewParams = [
				'entry' => $entry
			];
			return $this->view('XT\Membermap:View', 'xt_mm_log_view', $viewParams);
		}
        else
        {
            $page = $this->filterPage();
            $perPage = 20;

            $logRepo = $this->getApiLogRepo();

            /** @var \XT\Membermap\Repository\Log $logFinder */
            $logFinder = $logRepo->findApiLogForList()
                ->limitByPage($page, $perPage);

			$linkFilters = [];
			if ($userId = $this->filter('user_id', 'uint'))
			{
				$linkFilters['user_id'] = $userId;
				$logFinder->where('user_id', $userId);
			}

			$total = $logFinder->total();
			
			$this->assertValidPage($page, $perPage, $total, 'log');

			if ($this->isPost())
			{
				// redirect to give a linkable page
				return $this->redirect($this->buildLink('xt-api-logs', null, $linkFilters));
			}

            $viewParams = [
                'logs' => $logFinder->fetch(),
				'logUsers' => $logRepo->getUsersInLog(),

				'userId' => $userId,

                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
				'linkFilters' => $linkFilters
            ];

            return $this->view('XT\Membermap:List', 'xt_mm_log_list', $viewParams);
        }
	}

	public function actionDelete(ParameterBag $params)
	{
        $apiLog = $this->assertLogExists($params->log_id);

		if ($this->isPost())
		{
            $apiLog->delete();

			return $this->redirect($this->buildLink('xt-api-logs'));
		}
		else
		{
			$viewParams = [
				'apilog' => $apiLog
			];

			return $this->view('XT\Membermap:Delete', 'xt_mm_log_delete', $viewParams);
		}
	}

    public function actionMassDelete()
    {
        $this->assertPostOnly();

        $deletes = $this->filter('delete', 'array-str');

        $apiLogs = $this->em()->findByIds('XT\Membermap:Log', $deletes);
        foreach ($apiLogs AS $apiLog)
        {
            $apiLog->delete();
        }

        return $this->redirect($this->buildLink('xt-api-logs'));
    }

	protected function assertLogExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('XT\Membermap:Log', $id, $with, 'requested_log_entry_not_found');
	}

	/**
	 * @return \XT\Membermap\Repository\Log
	 */
	protected function getApiLogRepo()
	{
		return $this->repository('XT\Membermap:Log');
	}
}