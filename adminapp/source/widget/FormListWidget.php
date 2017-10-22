<?php

class FormListWidget extends BaseWidget
{

    public function run()
    {
		$forum_logic = ObjectCreater::create('ForumLogic');
		$all_forums  = $forum_logic->get_formated_forums();
        $this->_owner->render_file(BASE_ROOT.'/pc/template/common/forum_list.php', array('all_forums'=>$all_forums), false);
    }

}
