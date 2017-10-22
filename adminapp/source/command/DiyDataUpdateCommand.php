<?php

// cli command:  php /pathto/cli/command.php diydataupate

class DiyDataUpdateCommand 
{
	public function run($args)
	{
		ObjectCreater::create('DiyLogic')->cron_update_data();
	}
}
