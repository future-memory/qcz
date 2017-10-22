<?php
/**
 * @property AdminLogic $logic
 *
 */
class AdminController extends BaseController 
{
    public function __construct()   
    {
    	$this->logic = ObjectCreater::create('AdminLogic');

    	$action = $this->get_param('action');
    	$skip_actions = HelperConfig::get_config('global::skip_actions');
		if(in_array($action, $skip_actions)){
			return true;
		}
        $this->logic->check_admin_login();
        $this->logic->check_access();
        $this->logic->writelog();
    }
    

}





