<?php

class SigninLogic extends Logic
{
	private $_log_data         = array();
	private	$small_circle_days = 5;
	private	$big_circle_days   = 30;
	public $recircle_days      = 5;
	public $cur_member         = array();

	public function __construct()
	{
		$this->_dao       = ObjectCreater::create('SigninDao');
		
		$member_logic     = ObjectCreater::create('MemberLogic');
		$this->cur_member = $member_logic->get_current_member();
	}

	public function check_signed($uid)
	{
		$is_singed = false;
		//今天是否签到
		$data = $this->_dao->fetch($uid);
		if(!empty($data)){
		    if(date('Ymd') == date('Ymd', $data['lasttime'])){
		        $is_singed = true;
		    }
		}
		return $is_singed;
	}

	public function get_tips($uid)
	{
		$data = $this->_dao->fetch($uid);

		$signed    = !empty($data) && date('Ymd') == date('Ymd', $data['lasttime']);
		$last_days = $signed ? $data['last_days'] : $data['last_days'] + 1;

		$result = array(
			'last_days' => $data['last_days'],
			'reward'    => '',
			'last_days' => 0
		);

		//有漏签 取漏签天数 
		$days = intval(date('d'));
		if(($signed && $data['last_days']<$days) || (!$signed && $data['last_days']+1<$days)){
			$signed_days = 0;
			$start_time  = $this->_get_circle_start_time();
			$list = $this->_dao->get_signed_list($uid, $start_time);
			foreach($list as $item){
				if($item['code']==200){
					$signed_days += 1;
				}
			}

			$result['last_days'] = $days - $signed_days;
			$result['last_days'] = $result['last_days']>0 ? $result['last_days'] : 0;	
		}

		//今日奖励
		$circles = $this->_get_circle_encouragement($last_days);
		$ecrs    = $this->_get_encouragement_credit($circles, $last_days);
		if(!empty($ecrs)){
	    	$reward = 0;
	        foreach ($ecrs as $ecr){
	        	$reward += $ecr['reward'];
	        }
	        $result['reward'] .= '积分+'.$reward;
    	}


		return $result;
	}


	public function sign(&$last_days=null)
	{
		self::throw_exception(!$this->cur_member['uid'], array('code'=>402, 'message'=>'请先登录！'));
	
		$today_0     = strtotime(date('Y-m-d 00:00:00'));
		$yesterday_0 = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
		//设置
		
		//个人签到信息
		$user_data                  = $this->_dao->fetch($this->cur_member['uid']);
		$this->_log_data['uid']     = $this->cur_member['uid'];
		$this->_log_data['groupid'] = $this->cur_member['groupid'];
		$user_data['lasttime']      = isset($user_data['lasttime']) ? intval($user_data['lasttime']) : 0;
		self::throw_exception($user_data['lasttime']>=$today_0, array('code'=>201, 'message'=>'您已签到完毕，今日已无需再次签到！'));

		//连续天数，昨天有签到的时候 + 1，否则重新计算
		$last_days = isset($user_data['lasttime']) && $user_data['lasttime']>=$yesterday_0 ? intval($user_data['last_days'])+1 : 1; 
		//累积天数
		$days      = isset($user_data['days']) ? intval($user_data['days']) + 1 : 1;
		//排名
		$rank_log  = '';
		$rank      = $this->_get_sign_rank($rank_log);
		
		$sign_data = array(
			'uid'       => intval($this->cur_member['uid']),
			'username'  => htmlspecialchars($this->cur_member['username']),
			'days'      => $days,
			'last_days' => $last_days,
			'lasttime'  => TIMESTAMP,
			'rank'      => $rank
        );

		//获取奖励配置
		$circles = $this->_get_circle_encouragement($sign_data['last_days']);
		$ecrs    = $this->_get_encouragement_credit($circles, $last_days);

		//更新签到数据
        $res = $this->_dao->insert_or_update($sign_data);
        self::throw_exception(!$res, array('code'=>501, 'message'=>'抱歉，服务器错误，请重试！'));

        //清除缓存
        $start_time  = $this->_get_circle_start_time();
        $this->_dao->clear_cache($this->cur_member['uid']);
        $this->_dao->del_signed_list_cache($this->cur_member['uid'], $start_time-86400);

		$this->_log_data['last_days'] = $last_days;
		$this->_log_data['days']      = $days;
		$this->_log_data['rank_log']  = $rank_log;

        //奖励
        $ecr_msg = $special_msg = '';
		$this->_do_encouragement($ecrs, $ecr_msg, $special_msg);
       
        $message = '连续签到'.$last_days.'天';
        $message = $message.$ecr_msg.$special_msg;

        return $message;
	}

	public function resign($uid, $resign_date, &$return_last_day=null)
	{
		$this->_log_data['uid']     = $this->cur_member['uid'];
		$this->_log_data['groupid'] = $this->cur_member['groupid'];
		$this->_log_data['flag']    = 2;

		$resign_time = strtotime($resign_date);
		if(!$resign_date || !$resign_time){
			return false;
		}

		$today       = date('Y-m-d');
		$today_0     = strtotime(date('Y-m-d 00:00:00'));
		$signed_data = $this->_dao->fetch($uid);

		$start_time  = $this->_get_circle_start_time($resign_time);
		
		$setting = $this->get_setting();
		self::throw_exception(empty($setting), array('code'=>500, 'message'=>'抱歉，服务器错误，请联系管理员或版主！'));
		self::throw_exception($resign_time>=$today_0, array('code'=>408, 'message'=>'抱歉，该日期不能补签！'));
		
		//签到历史数据 多查一天 以免第一天就需要补签
		$data             = $this->_dao->get_signed_list($uid, $start_time-86400);
		$tmp              = array();
		$resigned         = 0;  //已补签天数
		$last_signed_time = 0; //最后签到日期  用于判断是否需要更新主表last_days

		foreach($data as $item){
			if($item['code']!=200){
				continue;
			}
			$tmp_date         = date('Y-m-d', $item['dateline']);
			$tmp[$tmp_date]   = $item;
			$resigned         = $item['flag']==2 ? $resigned + 1 : $resigned;

			$last_signed_time = $item['dateline']>$last_signed_time ? $item['dateline'] : $last_signed_time;
		}
		//已经签到
		self::throw_exception(isset($tmp[$resign_date]), array('code'=>408, 'message'=>'该日期已经签到，无需补签！'));
		//限制补签到次数
		self::throw_exception(isset($setting['resign_limit']) && $setting['resign_limit'] && $resigned>=$setting['resign_limit'], array('code'=>407, 'message'=>'超出补签限制次数，无法补签！'));

		$formula_check = $this->checkformulsyntax(
	        $setting['resign_formula'],
	        array('+', '-', '*', '/'),
	        array('days')
	    );

	    self::throw_exception(!$formula_check, array('code'=>500, 'message'=>'抱歉，服务器错误，请联系管理员或版主！'));

		$need        = 5;
		$break       = null; //终端统计连续签到天数
		$last_days   = 0; //到补签当天 连续签到天数
		$unsign_day  = 0; //包括已补签
		$unsign_day2 = 0; //不包括已补签
		$the_date    = null; //应该补签的那天

		$check_need_fix = null;
		$need_fix_ids   = array();
		$fix_last_sign  = false;

		for($time=$start_time;$time<=$today_0;$time=$time+86400){
			$date        = date('Y-m-d', $time);
			$signed      = isset($tmp[$date]) ? 1 : 0;
			$unsign_day  = $unsign_day + ($signed ? ($tmp[$date]['flag']!=1 ? 1 : 0) : 1);
			$unsign_day2 = $time>=$signed_data['lasttime'] ? $unsign_day2 : ($unsign_day2 + ($signed ? 0 : 1));

			if($the_date===null && $signed===0 && $date==$resign_date){
				$formula = str_replace("days", $unsign_day, $setting['resign_formula']);
	        	@eval("\$need = $formula;");
	        	$the_date = $date;
	        	$this->_log_data['dateline'] = $time;

	        	$check_need_fix = true;
	    	}

	    	//检查下一天是否需要修复数据
	    	if($check_need_fix){
				$the_date_after  = $time + 86400;
				$the_date_after  = date('Y-m-d', $the_date_after);
				//下一天签到了的话则需要继续检查
				$check_need_fix = isset($tmp[$the_date_after]) ? true : false;
	    		if($check_need_fix){
	    			$fix_last_sign  = $tmp[$the_date_after]['dateline']>=$last_signed_time ? true : $fix_last_sign;
	    			$need_fix_ids[] = $tmp[$the_date_after]['id'];
	    		}
	    	}

			$break     = $signed===0 ? ($the_date===$date ? null : 1) : $break;
			$last_days = $break!==null ? $last_days : ($last_days + 1);
		}

		//不按顺序补签
		//self::throw_exception($the_date!=$resign_date, array('code'=>408, 'message'=>'请按顺序补签！'));
		self::throw_exception($need>$this->cur_member['credit'], array('code'=>403, 'message'=>'积分不足，不能补签！'));

		$sign_data = array(
			'uid'   => intval($this->cur_member['uid']),
			'days' => $signed_data['days'] + 1,
        );

		$the_date_before = strtotime($the_date) - 86400;
		$the_date_before = date('Y-m-d', $the_date_before);

		//
		$this->_log_data['last_days'] = (isset($tmp[$the_date_before]) && isset($tmp[$the_date_before]['last_days']) ? intval($tmp[$the_date_before]['last_days']) : 0) + 1;
		$this->_log_data['days'] = $sign_data['days'];

		//只有一天没补签
		if($unsign_day2===1 && $signed_data['lasttime']>$this->_log_data['dateline']){
			$sign_data['last_days'] = $signed_data['last_days'] + $this->_log_data['last_days'];
		}elseif($signed_data['lasttime']<$this->_log_data['dateline']){
		//缺签后没有签到的情况
			$sign_data['last_days']       = $signed_data['last_days'] + 1;
			$sign_data['lasttime']        = $this->_log_data['dateline'];
			$this->_log_data['last_days'] = $sign_data['last_days'];
		}elseif($fix_last_sign){ 
		//更新了最后一次签到（或补签）的数据 所以主表的连续签到天数也要更新
			$sign_data['last_days']       = $signed_data['last_days'] + $this->_log_data['last_days'];
		}//其他情况不需要保存连续天数

		$return_last_day = isset($sign_data['last_days']) ? $sign_data['last_days'] : null;

        $res = $this->_dao->insert_or_update($sign_data);
        self::throw_exception(!$res, array('code'=>501, 'message'=>'抱歉，服务器错误，请重试！'));

        //修正历史签到数据
		if(!empty($need_fix_ids)){
			$this->_dao->fix_log_data($need_fix_ids, $this->_log_data['last_days']);
		}

        $this->_dao->clear_cache($this->cur_member['uid']);
        $this->_dao->del_signed_list_cache($this->cur_member['uid'], $start_time-86400);

		$need = -1 * abs($need);
		//扣积分
		$member_logic->update_credit($uid, $need, 'SGD');

		//获取奖励配置
		$circles = $this->_get_circle_encouragement($last_days, $this->_log_data['dateline']);
		$ecrs    = $this->_get_encouragement_credit($circles, $last_days);

        //奖励
        $ecr_msg = $special_msg = '';

        $this->_do_encouragement($ecrs, $ecr_msg, $special_msg);

		$message = '补签成功，扣除积分'.$need.$ecr_msg.$special_msg;

        $this->_log_data['rank_log'] = '，扣除积分'.$need;

        return $message;
	}

	public function get_signed_list($uid, $from_date=0)
	{
		$today       = date('Y-m-d');
		$signed_data = $this->_dao->fetch($uid);
		$start_time  = $this->_get_circle_start_time($from_date);
		
		$from_date   = $from_date ? (is_numeric($from_date) ? $from_date : strtotime($from_date)) : null;
		$end_time    = $from_date ? strtotime(date('Y-m-'.date('t', $from_date).' 00:00:00', $from_date)) : strtotime(date('Y-m-d 00:00:00'));

		$list        = array();

		//无签
		if($start_time == $end_time && $signed_data['lasttime']<$end_time){
			return $list;
		}

		//签到记录
		$data = $this->_dao->get_signed_list($uid, $start_time-86400);
		$tmp  = array();
		foreach($data as $item){
			if($item['code']!=200){
				continue;
			}			
			$tmp[date('Y-m-d', $item['dateline'])] = $item;
		}

		//获取 设置
		$setting       = $this->get_setting();
		$formula_check = $this->checkformulsyntax(
	        $setting['resign_formula'],
	        array('+', '-', '*', '/'),
	        array('days')
	    );

		$sec_title  = '积分';
		$need       = 5;
		$unsign_day = 0;
		for($time=$start_time;$time<=$end_time;$time=$time+86400){
			$date       = date('Y-m-d', $time);
			$signed     = isset($tmp[$date]) ? 1 : 0;
			//flag==2 补签的 需要计算上
			$unsign_day = $unsign_day + ($signed ? ($tmp[$date]['flag']==2 ? 1 : 0) : 1);

			if(!$signed && $formula_check){
				$formula = str_replace("days", $unsign_day, $setting['resign_formula']);
	        	@eval("\$need = $formula;");
	    	}

			$list[] = array(
				'date'   => $date, 
				'signed' => isset($tmp[$date]) ? 1 : 0, 
				'need'   => $date!=$today && !$signed ? $need.$sec_title : null
			);
		}

		return $list;
	}

	private function _get_circle_start_time($date=null)
	{
		$date = is_numeric($date) ? $date : strtotime($date);
		$Ym    = !$date ? 'Y-m' : date('Y-m', $date);
		return strtotime(date($Ym.'-01 00:00:00'));
	}

	//根据配置获取应该奖励的积分
	private function _get_encouragement_credit($circle_ecrs, $last_days)
	{
	    $list = $this->_dao->get_all_signin_config();
		$ecrs = array();

	    foreach($list as $item){
	    	//circle==2 为绝对循环  circle==0为不循环  circle==1为普通循环
			$bool = $item['circle']==2 ? isset($circle_ecrs[$item['days']]) : ($item['circle']==0 ? $last_days===intval($item['days']) : ($last_days && ($last_days%$item['days'])===0));
			$bool = $bool && (!$item['groupid'] || $item['groupid']==$this->cur_member['groupid']);
			if($bool){
				$ecrs[] = $item;
			}
 	    }

		return $ecrs;
	}

	//绝对循环（周期循环）
	private function _get_circle_encouragement($last_days, $timestamp=null)
	{
		$ecrs       = array();
		$cur_date   = $timestamp ? intval(date('d', $timestamp)) : intval(date('d'));
		$total_date = $timestamp ? intval(date('t', $timestamp)) : intval(date('t'));
		
		//			
		if($last_days>=$this->small_circle_days && $cur_date%$this->small_circle_days===0){
			$ecrs[$this->small_circle_days] = $this->small_circle_days;
		}

		//每月满签
		if($last_days>=$total_date && $cur_date===$total_date){
			$ecrs[$this->big_circle_days] = $this->big_circle_days;
			if(isset($ecrs[$this->small_circle_days])){
				unset($ecrs[$this->small_circle_days]);
			}
		}

		return $ecrs;
	}

	private function _do_encouragement($ecrs, &$ecr_msg, &$special_msg)
	{
		$setting_logic = ObjectCreater::create('SettingLogic');
		$extcredits    = $setting_logic->get('extcredits', $unserialize=true);

        if(!empty($ecrs)){
        	$reward = 0;
            foreach ($ecrs as $ecr){
                $reward += $ecr['reward'];
                if($ecr['type']==1){
					$special_msg .= '积分+'.$ecr['reward'].' ';
				}else{
					$ecr_msg     .= '积分+'.$ecr['reward'].' ';
            	}
            }

            ObjectCreater::create('MemberLogic')->update_credit($this->cur_member['uid'], $reward, 'SGE');

			$this->_log_data['ecr']         = $ecr_msg;
			$this->_log_data['special_msg'] = 't:'.$special_msg;

			$ecr_msg     = '，获得奖励 '.$ecr_msg;
			$special_msg = $special_msg ? '，获得特殊奖励 '.$special_msg : '';
        }
	}

	public function checkformulsyntax($formula, $operators, $tokens) 
	{
	    $var = implode('|', $tokens);
	    $operator = implode('', $operators);

	    $operator = str_replace(
	        array('+', '-', '*', '/', '(', ')', '{', '}', '\''),
	        array('\+', '\-', '\*', '\/', '\(', '\)', '\{', '\}', '\\\''),
	        $operator
	    );

	    $$var = null;
	    if(!empty($formula)) {
	        if(!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(eval(preg_replace("/($var)/", "\$\\1", $formula).';'))){
	            return false;
	        }
	    }
	    return true;
	}

	//获取签到排名
	private function _get_sign_rank(&$rank_log)
	{
	    $rank_key    = 'sign_rank_key';
	    $date_key    = 'sign_date_key';
	    
	    $cache_ttl   = 2592000; //30天
	    
	    $signed_date = memory('get', $date_key);
	    $today       = date('Y-m-d', TIMESTAMP);
	    
	    
	    $rank_log   .= 'signed_date:'.var_export($signed_date, true).'today:'.$today;

	    $cache_logic = ObjectCreater::create('CacheLogic');

	    if($signed_date != $today){ //隔天或者redis挂掉
	        $signed_date_db = $cache_logic->get($date_key, $force_from_db = true);

	        if($signed_date_db != $today){
	            $rank_log    .= ' signed_date_db:'.var_export($signed_date_db, true);
	            //隔天
	            $cache_logic->set($date_key, $today);
	            $rank = 1; 
	        }else{
	            //redis挂掉
	            $signed_rank_db = $cache_logic->get($rank_key, $force_from_db = true);
	            $rank           = intval($signed_rank_db) + 1;
	        }
	        memory('set', $date_key, $today, $cache_ttl);
	        memory('set', $rank_key, $rank, $cache_ttl); 
	    }else{
	        $rank_get = memory('get', $rank_key);
	        if($rank_get){  //缓存里有
	            $rank      = memory('inc', $rank_key); 
	            $rank_log .= ' rank:'.$rank.' rank_get:'.$rank_get;
	            if($rank < $rank_get){
	                //incr时过期
	                $rank      = $rank_get + 1;
	                memory('set', $rank_key, $rank, $cache_ttl);                 
	            }
	        }else{
	            //redis挂掉或者缓存过期
	            $signed_rank_db = $cache_logic->get($rank_key, $force_from_db = true);
	            $rank           = intval($signed_rank_db) + 1;

	            memory('set', $rank_key, $rank, $cache_ttl); 
	        }
	    }

	    $rank_log .= ' rank: '. $rank;

	    //更新数据库中的排名
	     $cache_logic->set($rank_key, $rank);
	    return $rank;
	}

	//获取签到设置
	public function get_setting()
	{
		$key         = 'signin_setting_key';
		$cache_logic = ObjectCreater::create('CacheLogic');
		$setting     = $cache_logic->get($key);

		return HelperUtils::dunserialize($setting);
	}

	//获取单天签到数据
	public function get_signed_item($uid, $date)
	{
		$time       = is_numeric($date) ? $date : strtotime($date);
		$item       =  $this->_dao->get_signed_item($uid, $time);
		$real_date  = !empty($item) ? date('Y-m-d', $item['dateline']) : null;
		$date       = is_numeric($date) ? date('Y-m-d', $date) : $date;

		return $date==$real_date ? $item : array();
	}

	//保存日志
	public function save_log($data=array())
	{
		if(!empty($this->_log_data)){
			$data['code'] = isset($data['code']) ? $data['code'] : 200;

			if($data['code']!=402){
				$this->_log_data['flag']      = isset($this->_log_data['flag']) ? $this->_log_data['flag'] : ($data['code']==200 ? 1 : 0);
				$this->_log_data['code']      = $data['code'];
				$this->_log_data['dateline']  = isset($this->_log_data['dateline']) ? $this->_log_data['dateline'] : TIMESTAMP;

				$this->_log_data['extra_log'] = 'gid:'.$this->_log_data['groupid'];
				if(isset($this->_log_data['last_days'])){
					$this->_log_data['extra_log'] .= ','.','.$this->_log_data['ecr'].','.$this->_log_data['special_msg'].','.$this->_log_data['rank_log'];
					unset($this->_log_data['rank_log'], $this->_log_data['special_msg'], $this->_log_data['ecr']);
				}
				unset($this->_log_data['groupid']);

		        //切换数据表
		        $this->setTable('signin_log');
		        //保存数据
		        $this->_dao->insert($this->_log_data);
		        //数据表恢复
		        $this->revertTable();
			}
		}
	}


}
