<?php

class CronCommand 
{
	public function run($args)
	{
		$id = is_array($args) && isset($args[0]) ? intval($args[0]) : 0;
		CronLogic::run($id);
	}
}
