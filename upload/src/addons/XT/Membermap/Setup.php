<?php

namespace XT\Membermap;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1()
	{
		$this->schemaManager()->createTable('xf_xt_mm_log', function(Create $table)
		{
			$table->addColumn('log_id', 'int')->autoIncrement();
			$table->addColumn('user_id', 'int');
			$table->addColumn('request_date', 'int');
			$table->addColumn('request_url', 'text');
			$table->addColumn('request_status', 'text');
			$table->addColumn('request_data', 'mediumblob');
			$table->addKey('request_date');
			$table->addKey(['user_id', 'request_date']);
		});
	}

	public function installStep2()
	{
		$this->schemaManager()->alterTable('xf_user_profile', function(Alter $table)
		{
			$table->addColumn('xt_mm_location_lat', 'double(40,30)')->setDefault(0)->after('location');
			$table->addColumn('xt_mm_location_long', 'double(40,30)')->setDefault(0)->after('xt_mm_location_lat');
			$table->addColumn('xt_mm_show_on_map', 'tinyint', 3)->setDefault(0)->after('xt_mm_location_long');
		});
	}

	public function installStep3()
	{

		$this->schemaManager()->alterTable('xf_user_group', function (Alter $table) 
		{
			$table->addColumn('xt_mm_markerPin', 'varchar', 255);
		});
	}

	public function installStep4()
	{
		foreach ($this->getDefaultWidgetSetup() AS $widgetKey => $widgetFn)
		{
			$widgetFn($widgetKey);
		}
	}

	public function postInstall(array &$stateChanges)
	{
		if ($this->applyDefaultPermissions())
		{
			$this->app->jobManager()->enqueueUnique(
				'permissionRebuild',
				'XF:PermissionRebuild',
				[],
				false
			);
		}
	}

	// ################################ UPGRADE TO 1.0.1 B3 ##################
	public function upgrade1000133Step1()
	{
		$this->query("
			ALTER TABLE `xf_user_profile` 
			MODIFY `xt_mm_location_lat` DOUBLE(40,30) DEFAULT 0,
			MODIFY `xt_mm_location_long` DOUBLE(40,30) DEFAULT 0;
		");
	}

	// ################################ UPGRADE TO 1.0.1 B5 ##################
	public function upgrade1000135Step1()
	{
		$this->query("
			ALTER TABLE `xf_user_group` 
			MODIFY `xt_mm_markerPin` VARCHAR(255);
		");
	}

	// ################################ UPGRADE TO 1.0.1 B8 ##################
	public function upgrade1000138Step1()
	{
		$this->query("
		CREATE TABLE xf_xt_mm_log (
			log_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id INT UNSIGNED NOT NULL,
			request_date INT UNSIGNED NOT NULL,
			request_url TEXT NOT NULL,
			request_status TEXT NOT NULL,
			request_data MEDIUMBLOB NOT NULL,
			PRIMARY KEY (log_id),
			KEY request_date (request_date),
			KEY user_id_request_date (user_id, request_date)
		) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
	}

	// ############################################ UNINSTALL #########################
	public function uninstallStep1()
	{
		$this->schemaManager()->dropTable('xf_xt_mm_log');
	}

	public function uninstallStep2()
	{
		$this->schemaManager()->alterTable('xf_user_profile', function(Alter $table)
		{
			$table->dropColumns(['xt_mm_location_lat']);
			$table->dropColumns(['xt_mm_location_long']);
			$table->dropColumns(['xt_mm_show_on_map']);
		});
	}

	public function uninstallStep3()
	{
        $this->schemaManager()->alterTable('xf_user_group', function(Alter $table)
		{
			$table->dropColumns(['xt_mm_markerPin']);
		});
	}

	public function uninstallStep4()
    {
        \XF\Util\File::deleteAbstractedDirectory('data://xtminimap/');
    }

	// ############################################ Data Definitions #########################
	protected function getDefaultWidgetSetup()
	{
		return [
			'xt_mm_minimap' => function($key, array $options = [])
			{
				$options = array_replace([], $options);
			
				$this->createWidget(
					$key,
					'xt_mm_minimap',
					[
						'positions' => ['xt_mm_membermap_sidebar' => 100],
						'options' => $options
					]
				);
			},
			'xt_mm_members_on_map' => function($key, array $options = [])
			{
				$options = array_replace([], $options);
		
				$this->createWidget(
					$key,
					'xt_mm_members_on_map',
					[
						'positions' => ['xt_mm_membermap_sidebar' => 200],
						'options' => $options
					]
				);
			},
			'xt_mm_map_legend' => function($key, array $options = [])
			{
				$options = array_replace([], $options);
					
				$this->createWidget(
					$key,
					'xt_mm_map_legend',
					[
						'positions' => ['xt_mm_membermap_sidebar' => 300],
						'options' => $options
					]
				);
			},
		];
	}
	
	protected function applyDefaultPermissions($previousVersion = null)
	{
		$applied = false;
	
		if (!$previousVersion)
		{
			$this->applyGlobalPermission('xt_membermap', 'view', 'general', 'viewProfile');
	
			$applied = true;
		}

		return $applied;
	}
}