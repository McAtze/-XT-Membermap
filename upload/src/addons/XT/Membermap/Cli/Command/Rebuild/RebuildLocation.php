<?php

namespace XT\Membermap\Cli\Command\Rebuild;

use XF\Cli\Command\Rebuild\AbstractRebuildCommand;

class RebuildLocation extends AbstractRebuildCommand
{
	protected function getRebuildName()
	{
		return 'xt-membermap-location';
	}

	protected function getRebuildDescription()
	{
		return 'Rebuilds the user locatoin data for the membermap.';
	}

	protected function getRebuildClass()
	{
		return 'XT\Membermap:UserMapData';
	}
}