<?php
class LogsController extends AdminController {

	public function index() 
	{
		$month = trim($this->get_param('month'));
		$page  = (int)$this->get_param('page', 1);
		$limit = 20;
		$start = ($page - 1) * $limit;

		$data  = ObjectCreater::create('AdminLogLogic')->get_log_by_month($month, $start, $limit);

		$logs     = $data['logs'];
		$count    = $data['count'];
		$selected = $data['selected'];
		$months   = $data['months'];

        $founders = HelperConfig::get_config('global::founders');
        $founders = explode(',', $founders);		
		
		$url   = '/index.php?mod=logs'.($month ? '&month='.$month : '');
		$pager = HelperPager::paging($count, $limit, $page, $url, true);

		$roles = array();
		$range = ObjectCreater::create('AdminRoleDao')->range();
		foreach($range as $role) {
			$roles[$role['id']] = $role['name'];
		}

		include(APP_ROOT . '/template/logs/list.php');
	}


}
