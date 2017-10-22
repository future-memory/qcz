<?php

// cli command:  php /pathto/cli/command.php hotthread

class HotThreadCommand 
{
	public function run($args)
	{
		$forums = ObjectCreater::create('ForumLogic')->get_app_forum_list();
		foreach ($forums as $forum) {
			if($forum['fid']){
				ObjectCreater::create('HotThreadLogic')->generate_hot_thead($forum['fid']);
			}
		}
		echo 'ok.';
	}
}
