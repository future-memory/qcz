<?php

class LotteryLogic extends Logic 
{
	private $_log_data = array();

	public function __construct() 
	{
		include(BASE_ROOT.'/source/language/lang_lottery.php');
		logic::$lang = lang_lottery::$ErrDescription;
		$this->_dao = ObjectCreater::create('LotteryWinDao');
	}


	public function get_user_count($id) 
	{
		$result = array();
		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		$lottery =  ObjectCreater::create('LotteryActivityDao')->fetch($id);
		$data    = $this->init_chance($lottery, $id);
		
		$chance  = $lottery['max_chance'] && $data['chance'] > $lottery['max_chance'] ? $lottery['max_chance'] : $data['chance'];
		
		$chance  = intval($data['chance']) - intval($data['used']);
		$chance  = $chance > 0 ? $chance : 0;

		return $chance;
	}

	public function get_address_by_uid($uid) 
	{
		if(!$uid){
			return false;
		}
		$data = ObjectCreater::create('ShopUserAddressDao')->fetch($uid);
		return $data;
	}

	public function get_win_list_by_activityid_uid($id) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		$list   = array();
		$list   = ObjectCreater::create('LotteryWinDao')->get_win_list_by_activityid_uid($id, $member['uid']);
		$awardids = array();
		foreach($list as $item){
			$awardids[] = $item['awardid'];
		}
		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);
		foreach ($list as $key => $value) {
			$award_name               = $awards[$value['awardid']]['name'];
			$award_pic  = HelperUtils::get_pic_url($awards[$value['awardid']]['pic'], 'app');
			$list[$key]['award_name'] = $award_name;
			$list[$key]['award_pic']  = $award_pic;
		}
		return $list;
	}

	public function get_lottery_count_by_activityid($id)
	{
		$memory = Nice::app()->getComponent('Memory');

		$count_key = 'lottery_count_' . $id;
		$cache_ttl = 86400; //1天
		$count     = $memory->cmd('get', $count_key);

	    if(!$count){ //隔天或者redis挂掉
	    	$count_db = ObjectCreater::create('CommonCacheDao')->fetch($count_key, $force_from_db = true);
	    	$count = intval($count_db['cachevalue']) + 1;
	    	$memory->cmd('set', $count_key, $count, $cache_ttl);
		}
		return $count;
	}

	public function update_address_by_winid($win_id, $name, $phone, $address, $page=1, $_my_limit = 40)
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));
		
		$win_info = ObjectCreater::create('LotteryWinDao')->fetch($win_id);
		$this->throw_exception($member['uid']!=$win_info['uid'], array('code'=>403, 'message'=>'您无权修改该中奖信息！'));

		ObjectCreater::create('LotteryWinDao')->update($win_id, array('address_info'=>$name.'|'.$phone.'|'.$address));

		//删除中奖记录列表缓存
		$memory = Nice::app()->getComponent('Memory');
		$memkey = 'lottery_win_user_list_'.$member['uid'].'_0';
		$memory->cmd('rm', $memkey);
	}

	public function send_code($win_id, $goods_list)
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		if($win_id){
			$win = ObjectCreater::create('LotteryWinDao')->fetch($win_id);
			$this->throw_exception($member['uid']!=$win['uid'], array('code'=>403, 'message'=>'非法请求'));

			$sync_log = ObjectCreater::create('SyncPhoneLogDao')->fetch($member['uid']);
			parent::throw_exception($sync_log['dateline']>$win['win_time'], array('code'=>407, 'message'=>'中奖后修改绑定手机不能领取奖品'));
		}else{
			ObjectCreater::create('ShopLogic')->check_buying($goods_list);
		}

		return ObjectCreater::create('VerifyCodeLogic')->send_code();
	}

	public function get_activity_info($id = -1) {
		return ObjectCreater::create('LotteryActivityDao')->fetch($id);
	}
	//抽奖
	public function draw($id, $directly=true)
	{
		self::throw_exception(!$id , lang_lottery::ERROR_PARAM);

		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		self::throw_exception(!$member['uid'], lang_lottery::ERROR_UNLOGIN);

		$lottery = ObjectCreater::create('LotteryActivityDao')->fetch($id);
		//判断是否存在
		self::throw_exception(empty($lottery), lang_lottery::ERROR_NOT_FOUND);

		//活动类抽奖 不能直接抽 
		self::throw_exception($directly && $lottery['enable']==2, array('code'=>403, 'message'=>'请求错误！'));		

		$lottery['end_time']   = intval($lottery['end_time']);
		$lottery['start_time'] = intval($lottery['start_time']);
		//判断是否开启
		self::throw_exception(!$lottery['enable'], lang_lottery::ERROR_ENABLE);
		//判断是否在期间内
		self::throw_exception(TIMESTAMP < $lottery['start_time'] , lang_lottery::ERROR_NOT_START);
		self::throw_exception(TIMESTAMP > $lottery['end_time'], lang_lottery::ERROR_END);

		//访问量过滤
		$mem = Nice::app()->getComponent('Memory');
		$key = 'lottery_pv_'. $id.'_'.date('i');
		$pv  = $mem->cmd('get', $key);
		$t   = intval($pv / 60);

		$pv ? $mem->cmd('inc', $key) : $mem->cmd('set', $key, 1, 120);

		//访问量大于每秒1000次时,概率过滤
		self::throw_exception($t>1000 && 100<rand(1, $t), lang_lottery::ERROR_SYS_BUSY);

		$this->_log_data['uid']        = $member['uid'];
		$this->_log_data['dateline']   = TIMESTAMP;
		$this->_log_data['activityid'] = $id;
		$this->_log_data['extra_log']  = '';

		//检查有没有抽奖机会
		$this->check_chance($lottery, $id);

		if(isset($lottery['check_cheat']) && $lottery['check_cheat']){
			//作弊的直接返回不中奖 需要减掉机会  
			$is_cheat = $this->check_cheat();
			self::throw_exception($is_cheat, lang_lottery::ERROR_CHEAT);
		}

		//限制了中奖次数的话  超出中奖次数则直接返回不中奖
		if($lottery['allow_win']){
			if($lottery['allow_win_type']==2){ //每天允许中奖
				$win_cnt = $this->_dao->get_today_count_by_uid_activityid($member['uid'], $id);
			}else{
				$win_cnt = $this->_dao->get_count_by_uid_activityid($member['uid'], $id);
			}
			self::throw_exception($win_cnt>=$lottery['allow_win'], lang_lottery::ERROR_WIN_LIMIT);
		}

		$award_list = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid($id);
		$has_noq = false;
		$awards = array();
		foreach ($award_list as $item) {
			$awards[$item['awardid']] = $item;
			if($item['type']==2){
				$has_noq = true;
			}
		}

		$awardid = 0;
		$queue   = $this->draw_queue_award($id, $lottery, $awards, !$has_noq);
		$win_id  = !empty($queue) ? $this->save_win($id, $queue, $has_noq, $awardid) : null;

		$win_id  = $has_noq && !$win_id ? $this->draw_noqueue_award($id, $lottery, $awards, $awardid) : $win_id;
		
		self::throw_exception(!$awardid, lang_lottery::ERROR_MISS);

		$award_info = ObjectCreater::create('LotteryAwardDao')->fetch($awardid);
		//直接发奖品
		//$this->send_award($lottery, $award_info, $win_id, $queue);
		//删除中奖列表的缓存
		$this->_dao->delete_win_cache($member['uid'], $id);
		//更新获得奖品数的缓存 
		$this->_dao->incr_user_win_count_cache($member['uid'], $id);

		$this->_log_data['queueid'] = isset($queue['id']) ? $queue['id'] : 0;

		// 返回奖品图片
		$need_award_img = (int)HelperUtils::get_param('award_img', '');
		if ($need_award_img === 1) {
			$award_data = ObjectCreater::create('LotteryAwardDao')->fetch($awardid);
			return array(
				'code'       => 200, 
				'win_id'     => $win_id, 
				'award_id'   => $awardid, 
				'award_type' => $award_info['type'], 
				'award_name' => $award_info['name'],
				'award_img_url' => (is_array($award_data) && isset($award_data['pic'])) ? HelperUtils::get_pic_url($award_data['pic'], 'app') : '',
				'message'    => '恭喜您，获得“'.$award_info['name'].'”'
			);
		}

		return array(
			'code'       => 200, 
			'win_id'     => $win_id, 
			'award_id'   => $awardid, 
			'award_type' => $award_info['type'], 
			'award_name' => $award_info['name'],
			'message'    => '恭喜您，获得“'.$award_info['name'].'”'
		);
	}

	private function send_award($lottery, $award_info, $win_id, $queue)
	{
		// $member = ObjectCreater::create('MemberLogic')->get_current_member();

		// if($award_info['type']==1){
		// 	ObjectCreater::create('LotteryChanceDao')->begin();

		// 	$message = '恭喜您在抽奖活动「'.$lottery['name'].'」中获得煤球奖励，煤球已发放到您论坛账号上，请查收。';
		// 	try {
		// 		$res     = $this->_send_mb($member['uid'], $award_info['val'], 'LOT');
		// 		$res2    = true;//send_message($member['uid'], $message);
		// 	} catch (Exception $e) {
		// 		$res2 = false;
		// 	}

		// 	$this->_dao->set_sended(array($win_id), TIMESTAMP);	

		// 	if($res && $res2){
		// 		ObjectCreater::create('LotteryChanceDao')->commit();
		// 	}else{
		// 		ObjectCreater::create('LotteryChanceDao')->rollback();
		// 	}
		// }else if($award_info['type']==2){   //虚拟奖品
		// 	if(isset($queue['stuff']) && $queue['stuff'] && $award_info['message']){
		// 		$message = strpos($award_info['message'], '{code}')!==false ? str_replace('{code}', $queue['stuff'], $award_info['message']) : $award_info['message'].$queue['stuff'];
		// 		ObjectCreater::create('AnnouncepmLogic')->send($member['uid'], $message);
		// 	}
		// }else if($award_info['type']==3){   //实物奖品 同步绑定的手机号码
		// 	ObjectCreater::create('MemberLogic')->sync_phone();
		// }
	}


	//判断是否作弊
	private function check_cheat() 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		$uid_set_key = 'lottery_draw_uid_set';
		$ip_set_key  = 'lottery_draw_ip_set';
		$ip_key      = 'lottery_draw_ip_lasttime_'.$member['clientip'];
		$uid_key     = 'lottery_draw_uid_lasttime_'.$member['uid'];
		$cache_ttl   = 10;
		$time_gap 	 = 5;

		$memory = Nice::app()->getComponent('Memory');
		$redis = $memory->get_memory_obj();

		if($redis->sIsMember($uid_set_key, $member['uid'])){
			return true;
		}
		if($redis->sIsMember($ip_set_key, $member['clientip'])){
			if(!$redis->sIsMember($uid_set_key, $member['uid'])){
				$redis->sAdd($ip_set_key, $member['uid']);
			}
			return true;
		}

		$ip_last_time  = $memory->cmd('get', $ip_key);
		$uid_last_time = $memory->cmd('get', $uid_key);
		if(($ip_last_time && (TIMESTAMP - $ip_last_time < $time_gap)) || ($uid_last_time && (TIMESTAMP - $uid_last_time < $time_gap))){
			$redis->sAdd($ip_set_key, $member['clientip']);
			$redis->sAdd($uid_set_key, $member['uid']);
			return true;
		}
		$memory->cmd('set', $ip_key, TIMESTAMP, $cache_ttl);

		return false;
	}

	//判断有没有抽奖机会
	private function check_chance($lottery, $activityid) 
	{
		$lottery['max_chance']      = intval($lottery['max_chance']);

		if(!$lottery['max_chance']){
			//无限制直接返回
			return true;
		}
		
		//获取、初始化
		$chance = $this->init_chance($lottery, $activityid);

		$this->_log_data['chance']      = $chance['chance'];
		$this->_log_data['used']        = $chance['used'];


		$chance['chance'] = $lottery['max_chance'] && $chance['chance'] > $lottery['max_chance'] ? $lottery['max_chance'] : $chance['chance'];

		self::throw_exception($chance['chance']<=$chance['used'], lang_lottery::ERROR_NO_CHANCE);

		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//减抽奖次数
		ObjectCreater::create('LotteryChanceDao')->increase($activityid, $member['uid'], $field='used');
	}


	//从队列中抽奖
	private function draw_queue_award($id, $lottery, $awards, $throw_err=true) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//获取最近的一个奖品
		$queues = ObjectCreater::create('LotteryActivityAwardQueueDao')->get_nearest_award($id);
		self::throw_exception($throw_err && empty($queues), lang_lottery::ERROR_Q_EMPTY);

		//概率
		$queue       = array();
		$del_q_cache = false;
		foreach($queues as $q){
			if($q['send_time']<=TIMESTAMP){
				$probabilitys = isset($awards[$q['awardid']]) ? $awards[$q['awardid']] : null;
				if(!$probabilitys){
					continue;
				}
				//概率 
				$probability  = $probabilitys['probability'];
				$probability  = $probability<1 ? 1 : $probability;
				$random       = rand(1, $probability);

				$this->_log_data['extra_log'] .= 'prb:'.$probability.'rd:'.$random;

				//有被废弃的奖品
				if(isset($probabilitys['status']) && ($probabilitys['status']===0 || $probabilitys['status']==='0')){
					$this->_log_data['extra_log'] .=  'q:'.$q['id'].' status:0';
					$ret = ObjectCreater::create('LotteryActivityAwardQueueDao')->update($q['id'], array('flag'=>1));
					$del_q_cache = true;
					continue;
				}

				if($random===1 || $probability<=1){
					$queue = $q;
					break;
				}
			}
		}

		$del_q_cache && ObjectCreater::create('LotteryActivityAwardQueueDao')->del_nearest_award_cache($id);	

		self::throw_exception($throw_err && empty($queue), lang_lottery::ERROR_CANNOT_SAVE);

		//删除queue nearest缓存
		$del_q_cache  || ObjectCreater::create('LotteryActivityAwardQueueDao')->del_nearest_award_cache($id);		

		//置为已抽取  置不成功则直接返回不中奖
		$ret = false;
		if (isset($queue['id'])) {
			$ret = ObjectCreater::create('LotteryActivityAwardQueueDao')->update($queue['id'], array('flag'=>1));
		}
		
		self::throw_exception($throw_err && !$ret, lang_lottery::ERROR_CANNOT_SAVE);

		return $ret ? $queue : array();		
	}

	private function save_win($id, $queue, $has_noq, &$awardid) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		//保存获奖信息 保存不成功也返回不中奖
		$win_data = array(
			'uid'        => $member['uid'],
			'qid'        => $queue['id'],
			'ip'		 => $member['clientip'],
			'username'   => $member['username'],
			'awardid'    => $queue['awardid'], 
			'activityid' => $id,
			'stuff'      => isset($queue['stuff']) ? $queue['stuff'] : '',
			'win_time'   => TIMESTAMP
		);
		
		try {
			$win_id  = $this->_dao->insert($win_data, $return_insert_id=true);
			$awardid = $queue['awardid'];			
		} catch (Exception $e) {
			$win_id = false;
		}

		if(!$win_id){ //恢复
			ObjectCreater::create('LotteryActivityAwardQueueDao')->update($queue['id'], array('flag'=>0));
			self::throw_exception(!$has_noq, lang_lottery::ERROR_CANNOT_SAVE);
		}

		return $win_id;
	}


	//从非队列奖品中抽奖
	private function draw_noqueue_award($id, $lottery, $awards, &$awardid) 
	{
		$awardid = 0;
		shuffle($awards);

		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		foreach($awards as $award){
			if($award['left']<1 || $award['type']!=2){
				continue;
			}
			$probability  = $award['probability'];
			$probability  = $probability<1 ? 1 : $probability;

			$random = rand(1, $probability);
			$this->_log_data['extra_log'] .= ' noq,prb:'.$probability.'rd:'.$random;

			//有被废弃的奖品
			if(isset($award['status']) && ($award['status']===0 || $award['status']==='0')){
				$this->_log_data['extra_log'] .= ' status:0';
				continue;
			}

			if($random===1 || $probability<=1){
			
				$this->_log_data['extra_log'] .= ' mr:'.$award['minute_rate'];

				if(isset($award['minute_rate']) && $award['minute_rate']){
					$minute       = date('i');
					$minute_wined = $this->_dao->get_minute_wined($id, $award['awardid'], $minute);
					$this->_log_data['extra_log'] .= ' mw:'.var_export($minute_wined, true);

					if($minute_wined>=$award['minute_rate']){
						continue;
					}

					$awardid = $award['awardid'];
					//减库存
					$res = ObjectCreater::create('LotteryActivityAwardDao')->des_award_by_activity($id, $awardid);
					$this->_log_data['extra_log'] .= ' sc:'.var_export($res, true);

					if(!$res){
						$awardid = 0;
						continue;
					}

					$awardid && $this->_dao->inc_minute_wined($id, $awardid, $minute);
				}
				
				break;
			}
		}
		$win_id  = $awardid ? $this->save_win_noq($id, $awardid) : 0;
		$awardid = $win_id ? $awardid : null;
		
		return $win_id;
	}

	private function save_win_noq($id, $awardid) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		//保存获奖信息 保存不成功也返回不中奖
		$win_data = array(
			'uid'        => $member['uid'],
			'ip'		 => $member['clientip'],
			'username'   => $member['username'],
			'awardid'    => $awardid, 
			'activityid' => $id, 
			'win_time'   => TIMESTAMP
		);
		
		return $this->_dao->insert($win_data, $return_insert_id=true);
	}


	//初始化抽奖次数
	public function init_chance($lottery, $activityid) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$chance = ObjectCreater::create('LotteryChanceDao')->get_user_chance($activityid, $member['uid']);

		if(empty($chance)){ //初始化
			$init_chance = $lottery['init_chance'] ? intval($lottery['init_chance']) : 0;

			$id = ObjectCreater::create('LotteryChanceDao')->insert_or_update_chance($member['uid'], $activityid, $init_chance, TIMESTAMP);

			$chance = array(
				'id'           => $id,
				'chance'       => $init_chance, 
				'used'         => 0, 
				'dateline'     => TIMESTAMP
			);
		}else{
			if(date('Ymd', $chance['dateline'])!=date('Ymd') && ($lottery['max_chance_type']==2 || $lottery['max_paid_chance_type']==2)){//隔天
				$left_chance = $chance['chance'] - $chance['used'];
				$left_chance = $left_chance>0 ? $left_chance : 0;
				$init_chance = $lottery['init_chance'] ? intval($lottery['init_chance']) : 0;
				$init_chance = $lottery['init_chance_type']==2 ? $init_chance : $left_chance; //&& $init_chance>$left_chance
				
				$left_paid_chance = $chance['paid_chance'] - $chance['paid_used'];
				$left_paid_chance = $left_paid_chance>0 ? $left_paid_chance : 0;				
				$paid_chance      = $lottery['max_paid_chance'] ? intval($lottery['max_paid_chance']) : 0;
				$paid_chance      = $lottery['max_paid_chance_type']==2 ? $paid_chance : $left_paid_chance;

				ObjectCreater::create('LotteryChanceDao')->insert_or_update_chance($member['uid'], $activityid, $init_chance, $paid_chance, TIMESTAMP, $chance['id']);
				
				$chance['chance']       = $init_chance;
				$chance['paid_chance']  = $paid_chance;
				$chance['used']         = 0;
				$chance['paid_used']    = 0;
				$chance['extra_chance'] = 0;
			}
		}

		$extra_chance = $this->get_extra_chance($lottery, $chance, $activityid);
		if($extra_chance>0){
			ObjectCreater::create('LotteryChanceDao')->increase($activityid, $member['uid'], 'chance', $extra_chance);
			$chance['chance'] = $chance['chance'] + $extra_chance;
		}

		return $chance;		
	}

	private function custom_check($id, $uid) {

	}

	public function increase_chance($lottery_id, $lottery=null, $value=1) 
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$lottery = !empty($lottery) ? $lottery : ObjectCreater::create('LotteryActivityDao')->fetch($lottery_id);

		$lottery['end_time']   = intval($lottery['end_time']);
		$lottery['start_time'] = intval($lottery['start_time']);
		//判断是否存在
		self::throw_exception(empty($lottery), array('code'=>404, 'message'=>'请求错误！'));
		//判断是否开启
		self::throw_exception(!$lottery['enable'], array('code'=>301, 'message'=>'抽奖未开启！'));
		//判断是否在期间内
		self::throw_exception(TIMESTAMP < $lottery['start_time'] , array('code'=>302, 'message'=>'请耐心等待，抽奖活动未开始！'));
		self::throw_exception(TIMESTAMP > $lottery['end_time'], array('code'=>303, 'message'=>'来晚了一步，抽奖活动已经结束！'));
		//判断是否超出最多机会
		$chance = ObjectCreater::create('LotteryChanceDao')->get_user_chance($lottery_id, $member['uid']);

		self::throw_exception($lottery['max_chance'] && $chance['chance']>($lottery['max_chance']-1), array('code'=>304, 'message'=>'超出最多抽奖机会次数，不能再加！'));

		//未初始化时
		if(empty($chance)){
			//初始化
			$init_chance = $lottery['init_chance'] ? intval($lottery['init_chance'])+1 : 1;
			$paid_chance = $lottery['max_paid_chance'] ? intval($lottery['max_paid_chance']) : 0;
			return ObjectCreater::create('LotteryChanceDao')->insert_or_update_chance($member['uid'], $lottery_id, $init_chance, $paid_chance, TIMESTAMP);
		}
		//增加一次机会
		return ObjectCreater::create('LotteryChanceDao')->increase($lottery_id, $member['uid'], $field='chance', $value);
	}


	public function get_win_list($id, $start=0, $limit=20, $type = 0) {
		if (!is_array($type)) {
			if ((int)$type === 1) {
				// just煤球
				$list = ObjectCreater::create('LotteryWinDao')->get_win_meiqiu_list_by_activityid($id, $start, $limit);
			} else if((int)$type === 0) {
				$list = ObjectCreater::create('LotteryWinDao')->get_win_list_by_activityid($id, $start, $limit);
			} else {
				// 实物 或 虚拟
				$list = ObjectCreater::create('LotteryWinDao')->get_win_real_list_by_activityid($id, $start, $limit);				
			}		
		} else {
			//有煤球
			if(in_array(1, $type)){
				$list = ObjectCreater::create('LotteryWinDao')->get_win_list_by_activityid($id, $start, $limit);
			}else{
				$list = ObjectCreater::create('LotteryWinDao')->get_win_real_list_by_activityid($id, $start, $limit);	
			}
			
		}		
		$awardids = $data = $tmp = array();
		foreach($list as $item){
			$awardids[] = $item['awardid'];
			if(isset($item['qid'])){
				$tmp[] = array(
					'id'       => $item['id'],
					'username' => $item['username'],
					'avatar'   => HelperUtils::avatar($item['uid'], 'middle', true),
					'awardid'  => $item['awardid'],
				);
			}else{
				$data[] = array(
					'username' => $item['username'],
					'avatar'   => HelperUtils::avatar($item['uid'], 'middle', true),
					'awardid'  => $item['awardid'],
				);
			}
		}

		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);
		$list   = array();

		if(empty($data) && !empty($tmp)){
			foreach ($tmp as $value) {
				if ($awards[$value['awardid']]) {
					$award_name          = $awards[$value['awardid']]['name'];
					$value['award_name'] = $award_name;
					$list[] = $value;
				}
			}
		}else{
			$s = 3;
			$i = $j = 0;
			foreach ($data as $value) {
				if ($awards[$value['awardid']]) {
					$award_name          = $awards[$value['awardid']]['name'];
					$value['award_name'] = $award_name;
					$list[] = $value;					
				}				
				if(isset($tmp[0]) && $i===($tmp[0]['id']%$s+$j*$s)){
					if($awards[$tmp[0]['awardid']]) {
						$sidx                 = null;
						$award_name           = $awards[$tmp[0]['awardid']]['name'];
						$tmp[0]['award_name'] = $award_name;
						$list[]               = $tmp[0];
					}
					array_shift($tmp);
					$j++;
				}
				$i++;
			}
		}
		
		return $list;		
	}

	public function get_award_list($id) 
	{
		$list     = ObjectCreater::create('LotteryActivityAwardDao')->get_awards_by_activityid($id);
		$awardids = $tmp = array();
		foreach($list as $item){
			$awardids[]            = $item['awardid'];
			$tmp[$item['awardid']] = $item;
		}

		$awards = ObjectCreater::create('LotteryAwardDao')->fetch_all($awardids);
		$data   = array();
		foreach($awards as $award){
			$data[] = array(
				'id'         => $award['id'],
				'name'       => $award['name'],
				'type'       => $award['type'],
				'is_default' => isset($tmp[$award['id']]['is_default']) ? intval($tmp[$award['id']]['is_default']) : 0,
				'pic'        => $award['pic'] ? HelperUtils::get_pic_url($award['pic'], 'app') : ''
			);
		}
		
		return $data;
	}

	public function check_activity($lottery)
	{
		//判断是否存在
		$this->throw_exception(empty($lottery), array('code'=>404, 'message'=>'请求错误！'));
		$lottery['end_time']   = isset($lottery['end_time']) ? intval($lottery['end_time']) : 0;
		$lottery['start_time'] = isset($lottery['start_time']) ? intval($lottery['start_time']) : 0;		
		//判断是否开启
		$this->throw_exception(!isset($lottery['enable']) || !$lottery['enable'], array('code'=>301, 'message'=>'活动未开启！'));
		//判断是否在期间内
		$this->throw_exception(TIMESTAMP < $lottery['start_time'] , array('code'=>302, 'message'=>'请耐心等待，活动未开始！'));
		$this->throw_exception(TIMESTAMP > $lottery['end_time'], array('code'=>303, 'message'=>'来晚了一步，活动已经结束！'));
	}

	public function save_address($win_id, $name, $phone, $address)
	{
		$this->throw_exception(!$win_id || !$name || !$phone || !$address, array('code'=>400, 'message'=>'参数错误！'));
		$this->throw_exception(mb_strlen($name)>60, array('code'=>405, 'message'=>'您输入的姓名超出长度限制！'));
		$this->throw_exception(mb_strlen($address)>490, array('code'=>406, 'message'=>'您输入的地址超出长度限制！'));

		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		self::throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$win_info = ObjectCreater::create('LotteryWinDao')->fetch($win_id);

		$this->throw_exception($member['uid']!=$win_info['uid'], array('code'=>407, 'message'=>'您无权修改该中奖信息！'));

		$data = array(
			'uid'     => $member['uid'],   
			'name'    => $name,    
			'phone'   => $phone,  
			'address' => $address 
		);

		ObjectCreater::create('ShopUserAddressDao')->insert_or_update($data);

		ObjectCreater::create('LotteryWinDao')->update($win_id, array('address_info'=>$name.'|'.$phone.'|'.$address));
	}


	public function save_log($data) {

		if(!empty($this->_log_data)){
			if(!isset($data['code']) || $data['code']!=401){
				$this->_log_data['result'] = isset($data['code']) ? $data['code'] : (!isset($this->_log_data['result']) ? 200 : $this->_log_data['result']);
				ObjectCreater::create('LotteryLogDao')->insert($this->_log_data);			
			}
			
		}		
	}

}
