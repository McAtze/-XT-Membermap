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
		$sm = $this->schemaManager();
	
		$sm->alterTable('xf_user_profile', function(Alter $table)
		{
			$table->addColumn('xt_mm_location_lat', 'double(40,30)')->setDefault(0)->after('location');
			$table->addColumn('xt_mm_location_long', 'double(40,30)')->setDefault(0)->after('xt_mm_location_lat');
			$table->addColumn('xt_mm_show_on_map', 'tinyint', 3)->setDefault(0)->after('xt_mm_location_long');
		});

		$sm->alterTable('xf_user_group', function (Alter $table) 
		{
			$table->addColumn('xt_mm_markerPin', 'varchar', 250)->setDefault('');
		});
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

	public function uninstallStep1()
	{
		$sm = $this->schemaManager();
	
		$sm->alterTable('xf_user_profile', function(Alter $table)
		{
			$table->dropColumns('xt_mm_location_lat');
			$table->dropColumns('xt_mm_location_long');
			$table->dropColumns('xt_mm_show_on_map');
		});

		$sm->alterTable('xf_user_group', function (Alter $table) 
		{
			$table->dropColumns('xt_mm_markerPin');
		});
	}
}