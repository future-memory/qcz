<?php
class LotteryController extends AdminController 
{
	private  $chance_types = array(
		0 => '请选择',
		1 => '整个活动',
		2 => '每天'
	);
	private  $award_types  = array(
		0 => '请选择',
		1 => '煤球',
		2 => '虚拟奖品',
		3 => '实物'
	);

	public function __construct() 
	{
		parent::__construct();
		$this->logic = ObjectCreater::create('LotteryLogic');
	}

	//活动列表页
	public function index() 
	{
		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 15);
		
		$start = $page > 1 ? ($page-1) * $limit : 0;
		$list  = ObjectCreater::create('LotteryActivityDao')->range($start, $limit, $sort = 'desc');
		
		$count = ObjectCreater::create('LotteryActivityDao')->count();
		
		$url   = 'index.php?mod=lottery&action=index';
		$pager = HelperPager::paging($count, $limit, $page, $url);

		include(BASE_ROOT . '/template/lottery/index.php');				
	}

	//添加或编辑活动页
	public function edit_activity()
	{
		$id     = (int)$this->get_param('id', 0);
		$info   = ObjectCreater::create('LotteryActivityDao')->fetch($id, true);

		$chance_types = $this->chance_types;

		include(BASE_ROOT . '/template/lottery/edit.php');
	}

	//活动保存
	public function update_activity()
	{
		$id               = (int)$this->get_param('id', 0);
		$enable           = (int)$this->get_param('enable', 0);
		$allow_win        = (int)$this->get_param('allow_win', 0);
		$max_chance       = (int)$this->get_param('max_chance', 0);
		$init_chance      = (int)$this->get_param('init_chance', 0);
		$init_chance_type = (int)$this->get_param('init_chance_type', 0);
		$allow_win_type   = (int)$this->get_param('allow_win_type', 0);
		$max_chance_type  = (int)$this->get_param('max_chance_type', 0);
		$allow_incr       = (int)$this->get_param('allow_incr', 0);
		$check_cheat      = (int)$this->get_param('check_cheat', 1);
		$start_time       = $this->get_param('start_time', 0);
		$end_time         = $this->get_param('end_time', 0);
		$name             = $this->get_param('name');

		$start_time  = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time    = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);

		$bool = !$name || !$start_time || !$end_time || !$allow_win_type || !$max_chance_type || !$init_chance_type || ($start_time>=$end_time);
		$this->throw_error($bool, array('code'=>400, 'message'=>'参数错误！'));

		$data         = array(
			'name'             => $name,
			'enable'           => $enable,
			'allow_win'        => $allow_win,
			'max_chance'       => $max_chance,
			'init_chance'      => $init_chance,
			'init_chance_type' => $init_chance_type,
			'start_time'       => $start_time,
			'end_time'         => $end_time,
			'allow_win_type'   => $allow_win_type,
			'max_chance_type'  => $max_chance_type,
			'allow_incr'       => $allow_incr,
			'check_cheat'      => $check_cheat,
		);

		$id && $data['id'] = $id;

		ObjectCreater::create('LotteryActivityDao')->insert_or_update($data);
		
		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//奖品列表页
	public function award_list()
	{
		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 10);
		
		$start = $page > 1 ? ($page-1) * $limit : 0;
		$list  = ObjectCreater::create('LotteryAwardDao')->range($start, $limit, $sort = 'desc');
		$count = ObjectCreater::create('LotteryAwardDao')->count();

		$url   = 'index.php?mod=lottery&action=award_list';
		$pager = HelperPager::paging($count, $limit, $page, $url);
		
		include(BASE_ROOT . '/template/lottery/award_list.php');
	}

	//添加或编辑奖品
	public function edit_award()
	{
		$id          = (int)$this->get_param('id', 0);
		$info        = ObjectCreater::create('LotteryAwardDao')->fetch($id, true);

		$award_types = $this->award_types;

		include(BASE_ROOT . '/template/lottery/edit_award.php');
	}

	//奖品保存
	public function update_award()
	{
		$id      = (int)$this->get_param('id');
		$type    = (int)$this->get_param('type');
		$val     = (int)$this->get_param('val');
		$name    = $this->get_param('name');
		//$picurl  = $this->get_param('picurl');
		$message = $this->get_param('message');		

		$this->throw_error(!$name, array('code'=>400, 'message'=>'参数错误！'));

		$data = array(
			'name'    => $name,
			//'pic'     => $picurl,
			'type'    => $type,
			'val'     => $val,
			'message' => $message, 
		);

		if($_FILES['pic']['name']){
			$pic = ObjectCreater::create('HelperUpyun')->save($_FILES['pic'], 'app', $allow_size=512000);

			$this->throw_error(!is_array($pic), array('code'=>400, 'message'=>$pic));

			if(is_array($pic) && isset($pic['attachment']) && $pic['attachment']){
				$data['pic'] = '0|'.$pic['attachment'];
			}
		}

		$id && $data['id'] = $id;

		ObjectCreater::create('LotteryAwardDao')->insert_or_update($data);

		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//活动奖品列表页
	public function activity_award_list()
	{
		$id       = (int)$this->get_param('id', 0);
		$list     = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid_admin($id, true);

		$awardids = array();
		foreach($list as $item){
			$awardids[] = $item['awardid'];
		}

		//统计
		$counts = ObjectCreater::create('LotteryActivityAwardQueueDao')->get_activity_award_counts($id, $awardids);
		//奖品名
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);
		foreach ($list as $key => $value) {
			$award_name               = $awards[$value['awardid']]['name'];
			$list[$key]['cnt']        = isset($counts[$value['awardid']]) ? $counts[$value['awardid']]['cnt'] : 0;
			$list[$key]['qleft']      = isset($counts[$value['awardid']]) ? $counts[$value['awardid']]['qleft'] : 0;
			$list[$key]['award_name'] = $award_name;
		}
		$list = array_values($list);

		$info = ObjectCreater::create('LotteryActivityDao')->fetch($id);

		include(BASE_ROOT . '/template/lottery/activity_award_list.php');
	}

	//将奖品移出活动
	public function delete_activity_award()
	{
		$awardid    = (int)$this->get_param('awardid');
		$activityid = (int)$this->get_param('activityid');

		$this->throw_error(!$awardid || !$activityid, array('code'=>400, 'message'=>'参数错误！'));

		$qcount = ObjectCreater::create('LotteryActivityAwardQueueDao')->get_award_queue_count_by_activityid($activityid, $awardid, 0);

		ObjectCreater::create('LotteryActivityAwardDao')->delete_by_activityid_awardid($activityid, $awardid, $qcount);

		ObjectCreater::create('LotteryActivityAwardQueueDao')->clear_queue_by_activityid_awardid($activityid, $awardid);

		$this->render_json(self::$WEB_SUCCESS_RT);		
	}

	//设置为默认奖品
	public function set_activity_award_default()
	{
		$awardid    = (int)$this->get_param('awardid');
		$activityid = (int)$this->get_param('activityid');
		$val        = (int)$this->get_param('val');
		
		$this->throw_error(!$awardid || !$activityid, array('code'=>400, 'message'=>'参数错误！'));

		$res = ObjectCreater::create('LotteryActivityAwardDao')->set_activity_award_default($activityid, $awardid, $val);


		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//恢复奖品
	public function recovery_activity_award()
	{
		$awardid    = (int)$this->get_param('awardid');
		$activityid = (int)$this->get_param('activityid');

		$this->throw_error(!$awardid || !$activityid, array('code'=>400, 'message'=>'参数错误！'));

		ObjectCreater::create('LotteryActivityAwardDao')->recovery_by_activityid_awardid($activityid, $awardid);

		$this->render_json(self::$WEB_SUCCESS_RT);	
	}

	//编辑奖品概率页
	public function edit_award_probability()
	{
		$id     = (int)$this->get_param('id', 0);
		$actid  = (int)$this->get_param('activityid', 0);
		$info   = ObjectCreater::create('LotteryActivityAwardDao')->fetch($id, $force_db=true);

		$award_info    = ObjectCreater::create('LotteryAwardDao')->fetch($info['awardid']);
		$activity_info = ObjectCreater::create('LotteryActivityDao')->fetch($info['activityid']);

		include(BASE_ROOT . '/template/lottery/edit_award_probability.php');
	}

	//更新概率
	public function update_award_probability()
	{
		$id               = (int)$this->get_param('id', 0);
		$probability      = (int)$this->get_param('probability', 0);
		$paid_probability = (int)$this->get_param('paid_probability', 0);
		$minute_rate      = (int)$this->get_param('minute_rate', 0);

		$this->throw_error(!$id || !$probability, array('code'=>400, 'message'=>'参数错误！'));

		$data = array(
			'probability'      => $probability, 
			'paid_probability' => $paid_probability, 
			'minute_rate'      => $minute_rate
		);

		$info = ObjectCreater::create('LotteryActivityAwardDao')->fetch($id);

		ObjectCreater::create('LotteryActivityAwardDao')->update($id, $data);
		
		//删除缓存
		ObjectCreater::create('LotteryActivityAwardDao')->del_activity_award_cache($info['activityid'], $info['awardid']);

        $this->render_json(self::$WEB_SUCCESS_RT);
	}

	//奖品分配页
	public function award_distribute()
	{
		$id         = (int)$this->get_param('id', 0);

		$info       = ObjectCreater::create('LotteryActivityDao')->fetch($id,true);
		$award_list = ObjectCreater::create('LotteryAwardDao')->range(0, 1000, $sort = 'desc');

		include(BASE_ROOT . '/template/lottery/award_distribute.php');
	}

	//奖品分配
	public function distribute()
	{
		$id               = (int)$this->get_param('id', 0);
		$awardid          = (int)$this->get_param('awardid', 0);
		$count            = (int)$this->get_param('count', 0);
		$probability      = (int)$this->get_param('probability', 0);
		$paid_probability = (int)$this->get_param('paid_probability', 0);
		$start_time       = $this->get_param('start_time', 0);
		$end_time         = $this->get_param('end_time', 0);
		$type             = (int)$this->get_param('type', 0);
		$minute_rate      = (int)$this->get_param('minute_rate', 0);
		
		$start_time  = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time    = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);

		$this->throw_error(!$id || !$awardid || !$count || !$start_time || !$end_time || ($start_time >= $end_time), array('code'=>400, 'message'=>'参数错误！'));		
		
		//判断是不是已经有分发
		$item = ObjectCreater::create('LotteryActivityAwardDao')->get_item_by_activityid_awardid($id, $awardid);
		$append = !empty($item);
		$award_info = ObjectCreater::create('LotteryAwardDao')->fetch($awardid, true);

		if($type===2){
			//$this->throw_error(intval($award_info['type'])!==1, array('code'=>400, 'message'=>'非煤球奖品请使用队列方式！'));
			$res = ObjectCreater::create('LotteryActivityAwardDao')->distribute_unq_award($id, $awardid, $type, $minute_rate, $probability, $paid_probability, $count, $count);

			$this->throw_error(!$res, array('code'=>500, 'message'=>'分配失败，请重试！'));

			$this->render_json(self::$WEB_SUCCESS_RT);
		}

		//获取抽奖配置
		$lottery        = ObjectCreater::create('LotteryActivityDao')->fetch($id,true);
		$act_end_time   = intval($lottery['end_time']);
		$act_start_time = intval($lottery['start_time']);

		$this->throw_error(!$act_end_time || !$act_start_time, array('code'=>400, 'message'=>'抽奖开始时间和结束时间未设置'));
		$this->throw_error($end_time>$act_end_time, array('code'=>400, 'message'=>'奖品发出时间必须在活动期间'));

		$start_time = $start_time ? $start_time : $act_start_time;
		$end_time   = $end_time ? $end_time : $act_end_time;

		$start = $append ? TIMESTAMP : 0;
		$this->logic->distribute($id, $awardid, $count, $start_time, $end_time, $append, $probability, $paid_probability, $type, $award_info);

		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//获奖列表页
	public function win_list()
	{
		$id         = (int)$this->get_param('id');
		$awardid    = (int)$this->get_param('awardid', 0);
		
		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);
		
		$page       = (int)$this->get_param('page', 1);
		$limit      = (int)$this->get_param('limit', 10);
		$start      = $page > 1 ? ($page-1) * $limit : 0;
		
		$list       = ObjectCreater::create('LotteryWinDao')->get_win_list_by_activityid_awardid($id, $awardid, $start_time, $end_time, $start, $limit, $sort = 'desc');
		$count      = ObjectCreater::create('LotteryWinDao')->get_win_count_by_activityid_awardid($id, $awardid, $start_time, $end_time);
		
		$awards     = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid_type_admin($id);
		$awardids   = array();
		foreach($awards as $item){
			$awardids[] = $item['awardid'];
		}
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);

		foreach ($list as $key => $value) {
			$award_name               = $awards[$value['awardid']]['name'];
			$list[$key]['award_name'] = $award_name;
		}

		$info  = ObjectCreater::create('LotteryActivityDao')->fetch($id);


		$url   = "index.php?mod=lottery&action=win_list&id=".$id.'&awardid='.$awardid.'&start_time='.$start_time.'&end_time='.$end_time;
		$pager = HelperPager::paging($count, $limit, $page, $url);

		include(BASE_ROOT . '/template/lottery/win_list.php');
	}

	//煤球获奖页
	public function win_noq_list()
	{
		$id         = (int)$this->get_param('id');
		$awardid    = (int)$this->get_param('awardid', 0);
		
		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);
		
		$page       = (int)$this->get_param('page', 1);
		$limit      = (int)$this->get_param('limit', 10);
		$start      = $page > 1 ? ($page-1) * $limit : 0;
		
		$list       = ObjectCreater::create('lottery_win')->get_win_noq_list_by_activityid_awardid($id, $awardid, $start_time, $end_time, $start, $limit, $sort = 'desc');
		$list       = array_slice($list, 0 , $limit); 
		$count      = ObjectCreater::create('lottery_win')->get_win_noq_count_by_activityid_awardid($id, $awardid, $start_time, $end_time);
		
		$awards     = ObjectCreater::create('lottery_activity_award')->get_awards_by_activityid_type_admin($id, false);
		$awardids   = array();
		foreach($awards as $item){
			$awardids[] = $item['awardid'];
		}
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);

		foreach ($list as $key => $value) {
			$award_name               = $awards[$value['awardid']]['name'];
			$list[$key]['award_name'] = $award_name;
		}

		$info   = ObjectCreater::create('LotteryActivityDao')->fetch($id);

		$multi = multi($count, $limit, $page, ADMINSCRIPT."?action=lottery&op=win_noq_list&id=".$id.'&awardid='.$awardid.'&start_time='.$start_time.'&end_time='.$end_time);

		include(BASE_ROOT . '/template/lottery/win_noq_list.php');
		exit();		
	}	

	//获奖名单导出
	public function win_export()
	{
        ob_end_clean();

		$limit      = 1000;
		$id         = (int)$this->get_param('id');
		$awardid    = (int)$this->get_param('awardid', 0);
		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);
		$noq        = (int)$this->get_param('noq', 0);
		
		$awards   = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid_admin($id, true);

		$awardids = array();
		foreach($awards as $item){
			$awardids[] = $item['awardid'];
		}
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);

		$info   = ObjectCreater::create('LotteryActivityDao')->fetch($id);

        $title_arr  = array(
            'A' => 'UID',
            'B' => '用户名',
            'C' => 'IP',
            'D' => '奖品',
            'E' => '中奖时间',
            'F' => '姓名',
            'G' => '电话',
            'H' => '地址',
        );
        
        $name_plus = '';
        if($start_time && $end_time){
        	$name_plus = date('Y-m-d_H_i_s', $start_time).'至'.date('Y-m-d_H_i_s', $end_time);
        }elseif($start_time){
        	$name_plus = date('Y-m-d_H_i_s', $start_time).'起';
        }elseif($end_time){
        	$name_plus = date('Y-m-d_H_i_s', $end_time).'止';
        }
        $name_plus .= ($noq ? '_库存方式' : '_队列方式');  

		HelperUtils::export_csv_start(iconv('utf-8', 'gbk//ignore', $info['name'].'-中奖列表'.$name_plus.'.csv'), $title_arr);

		$index = $loop_index = 0;
		$total = $noq ? ObjectCreater::create('LotteryWinDao')->get_win_noq_count_by_activityid_awardid($id, $awardid, $start_time, $end_time) : ObjectCreater::create('LotteryWinDao')->get_win_count_by_activityid_awardid($id, $awardid, $start_time, $end_time);

        do{
			$list    = $noq ? ObjectCreater::create('LotteryWinDao')->get_win_noq_list_by_activityid_awardid($id, $awardid, $start_time, $end_time, $loop_index, $limit, $sort = 'desc') : ObjectCreater::create('LotteryWinDao')->get_win_list_by_activityid_awardid($id, $awardid, $start_time, $end_time, $loop_index, $limit, $sort = 'desc');
			$list    = array_slice($list, 0, $limit);
			$csv_row = '';
            foreach ($list as $key => $item) {
            	$address_info = trim($item['address_info']) ? explode('|', $item['address_info']) : array();
				$csv_row  = $item['uid'] . ',"' . $item['username'] . '",' . $item['ip'] . ',' . $awards[$item['awardid']]['name'] . ',' ;
                $csv_row .= ($item['win_time'] ? date('Y-m-d H:i:s', $item['win_time']) : '') .',';
                $csv_row .= (!empty($address_info) ? (isset($address_info[0]) ? '"'.$address_info[0]. '",' : '-').(isset($address_info[1]) ? '"'.$address_info[1]. '",' : '-').(isset($address_info[2]) ? '"'.$address_info[2]. '",' : '-') : '-,-,-'). "\n";
                echo iconv('utf-8', 'gbk//ignore', $csv_row);
                $index++;
                if($index%1000 ===0){
                    @flush();
                    @ob_flush();
                }
            }
            $loop_index += $limit;
        }while ($loop_index < $total);

        HelperUtils::export_csv_end();	
	}

	//奖品队列页
	public function award_queue()
	{
		$id       = (int)$this->get_param('id');
		$awardid  = (int)$this->get_param('awardid', 0);
		$page     = (int)$this->get_param('page', 1);
		$limit    = (int)$this->get_param('limit', 10);
		$sort     = $this->get_param('sort', 'asc');
		
		$start    = $page > 1 ? ($page-1) * $limit : 0;
		$list     = ObjectCreater::create('LotteryActivityAwardQueueDao')->get_award_queue_by_activityid($id, $awardid, $start, $limit, $sort);
		$count    = ObjectCreater::create('LotteryActivityAwardQueueDao')->get_award_queue_count_by_activityid($id, $awardid);

		$awards   = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid_admin($id);
		$awardids = array();
		foreach($awards as $item){
			$awardids[] = $item['awardid'];
		}
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);

		foreach ($list as $key => $value) {
			$award_name               = $awards[$value['awardid']]['name'];
			$list[$key]['award_name'] = $award_name;
		}

		$info   = ObjectCreater::create('LotteryActivityDao')->fetch($id);

		$url   = 'index.php?mod=lottery&action=award_queue&id='.$id.'&awardid='.$awardid;
		$pager = HelperPager::paging($count, $limit, $page, $url);

		include(BASE_ROOT . '/template/lottery/award_queue.php');
	}

	public function change_flag()
	{
		$id   = (int)$this->get_param('id', 0);
		$data = array('flag'=>1);

		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));

		$this->logic->update_queue($id, $data);

		$this->render_json(self::$WEB_SUCCESS_RT);
	}


	//编辑队列
	public function edit_queue()
	{
		$id            = (int)$this->get_param('id', 0);

		$info          = ObjectCreater::create('LotteryActivityAwardQueueDao')->fetch($id);
		$award_info    = ObjectCreater::create('LotteryAwardDao')->fetch($info['awardid']);
		$activity_info = ObjectCreater::create('LotteryActivityDao')->fetch($info['activityid']);

		include(BASE_ROOT . '/template/lottery/edit_queue.php');
	}

	//修改队列
	public function change_send_time()
	{
		$id         = (int)$this->get_param('id', 0);
		$val        = $this->get_param('send_time', 0);
		$activityid = (int)$this->get_param('activityid', 0);

		$val = is_numeric($val) && $val>1420041600 ? intval($val) : strtotime($val);

		$this->throw_error(!$id || !$val || $val<1420041600, array('code'=>400, 'message'=>'参数错误！'));

		$data = array('send_time'=>$val);

		$this->logic->update_queue($id, $data);

		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	public function get_win_count()
	{
		$id         = (int)$this->get_param('id', 0);
		$awardid    = (int)$this->get_param('awardid', 0);

		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);

		$this->throw_error(!$id || !$awardid, array('code'=>400, 'message'=>'参数错误！'));

		$cnt = ObjectCreater::create('lottery_win')->get_win_count_by_activityid_awardid_for_send($id, $awardid, $start_time, $end_time);

		$this->render_json(array('code'=>200, 'data'=>array('count'=>$cnt)));
	}

	public function get_award_list()
	{
		$id         = (int)$this->get_param('id', 0);

		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));

		$list     = ObjectCreater::create('lottery_activity_award')->get_awards_by_activityid_admin($id);
		$awardids = array();
		foreach($list as $item){
			$awardids[] = $item['awardid'];
		}
		$award_list = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>array_values($award_list))));
	}

	public function get_win_list()
	{
		$id      = (int)$this->get_param('id', 0);
		$awardid = (int)$this->get_param('awardid', 0);
		$start   = (int)$this->get_param('start', 0);
		$limit   = (int)$this->get_param('limit', 500);

		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);

		$this->throw_error(!$id || !$awardid, array('code'=>400, 'message'=>'参数错误！'));

		$cnt = ObjectCreater::create('lottery_win')->get_win_count_by_activityid_awardid_for_send($id, $awardid, $start_time, $end_time);
		$list = ObjectCreater::create('lottery_win')->get_win_list_by_activityid_awardid_for_send($id, $awardid, $start_time, $end_time, $start, $limit);

		$this->render_json(array('code'=>200, 'data'=>array('count'=>$cnt, 'list'=>$list)));
	}


	//清空队列
	public function delete_award_queue()
	{
		$id         = (int)$this->get_param('id');
		$awardid    = (int)$this->get_param('awardid');
		$force      = (int)$this->get_param('force');

		$info = ObjectCreater::create('LotteryActivityDao')->fetch($id);

		$this->throw_error(TIMESTAMP>=intval($info['start_time']) && TIMESTAMP<=intval($info['end_time']) && $force!==2, array('code'=>400, 'message'=>'活动期间，不能清空！'));


		if($awardid){
			ObjectCreater::create('LotteryActivityAwardQueueDao')->clear_queue_by_activityid_awardid($id, $awardid);
		}else{
			ObjectCreater::create('LotteryActivityAwardQueueDao')->clear_queue_by_activityid($id);
		}

		$this->render_json(self::$WEB_SUCCESS_RT);
	}


}




