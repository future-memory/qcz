<?php

class DiyWidget extends BaseWidget
{
    public function run()
    {
		$diy    = $this->_owner->get_param('diy');
		$is_diy = $diy=='yes' ? ObjectCreater::create('DiyLogic')->check_diy_perm() : false;

		$is_diy = $is_diy ? $is_diy : $this->_owner->get_param('diy_dev');

        $is_diy && $this->_owner->render_file(BASE_ROOT.'/pc/template/common/diy_include.php');
    }

}
