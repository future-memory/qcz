<?php
class LotteryWinDao extends BaseDao {
    private $_noq_table = 'lottery_win_unqueue';

    public function __construct() {

		$this->_table         = 'lottery_win';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'lottery_win_';

        parent::__construct();
    }

    public function get_count_by_uid_activityid($uid, $activityid)
    {
    	if($this->_allowmem){
			$cache_ttl = 86400;
			$key       = 'lottery_win_cnt_'.$uid.'_'.$activityid;
			$cnt       = $this->_memory->cmd('get', $key);
			if($cnt!==false){
				return $cnt;
			}
    	}

    	$data = $this->_db->fetch_first('SELECT SUM(cnt) AS total FROM (SELECT count(*) as cnt FROM %t WHERE uid=%d AND activityid=%d UNION ALL SELECT count(*) as cnt FROM %t WHERE uid=%d AND activityid=%d) unc', array($this->_table, $uid, $activityid, $this->_noq_table, $uid, $activityid));
    	if($this->_allowmem && !empty($data)){
			$this->_memory->cmd('set', $key, intval($data['total']), $cache_ttl);
    	}

        return $data['total'];
    }

    public function get_today_count_by_uid_activityid($uid, $activityid)
    {
        $field = 'today_win_cnt';
        //因为这个hash是加在chance上的，所以统一在那边处理
        // $cache_cnt = C::t('lottery_chance')->get_cache($activityid, $uid, $field);
        $cache_cnt = ObjectCreater::create('LotteryChanceDao')->get_cache($activityid, $uid, $field);
        if($cache_cnt!==false){
            return intval($cache_cnt);
        }

        $start_time = strtotime(date('Y-m-d 00:00:00'));

        $data = $this->_db->fetch_first('SELECT SUM(cnt) AS total FROM (SELECT count(*) as cnt FROM %t WHERE uid=%d AND activityid=%d AND win_time>=%d UNION ALL SELECT count(*) as cnt FROM %t WHERE uid=%d AND activityid=%d AND win_time>=%d) unc', array($this->_table, $uid, $activityid, $start_time, $this->_noq_table, $uid, $activityid, $start_time));
        if(!empty($data)){
            ObjectCreater::create('LotteryChanceDao')->set_cache($activityid, $uid, $field, intval($data['total']));
        }

        return $data['total'];        
    }

    public function incr_user_win_count_cache($uid, $activityid)
    {
        //当天
        $field = 'today_win_cnt';
        // C::t('lottery_chance')->increase_cache($activityid, $uid, $field);
        ObjectCreater::create('LotteryChanceDao')->sub_increase_cache($activityid, $uid, $field);
        //全部
    	$key = 'lottery_win_cnt_'.$uid.'_'.$activityid;
    	$cnt = $this->_memory->cmd('get', $key);
    	if($cnt){
    		$newcnt = $this->_memory->cmd('inc', $key);
    		if($newcnt>$cnt){
    			return true;
    		}
    	}
    	return $this->_memory->cmd('rm', $key);
    }

    public function get_win_list_by_activityid($activityid, $start, $limit)
    {
        if($this->_allowmem){
            $cache_ttl = 60; 
            $key       = 'lottery_activity_win_list_'.$activityid.'_'.$start.'_'.$limit;
            $data      = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }
        $limit_actual = intval($limit / 3);
        $limit_actual = $limit_actual > 0 ? $limit_actual : 1;

        $data = $this->_db->fetch_all('SELECT * FROM ((SELECT `id` ,`uid` ,`username`,`ip` ,`awardid`,`activityid` ,`win_time`,`sended` ,`send_time` ,`address_info`, `qid` FROM %t WHERE activityid=%d ORDER BY id DESC limit %d, %d) UNION ALL (SELECT `id` ,`uid` ,`username`,`ip` ,`awardid`,`activityid` ,`win_time`,`sended` ,`send_time` ,`address_info`, 0 as `qid` FROM %t WHERE activityid=%d ORDER BY id DESC limit %d, %d) ) unl  ORDER BY win_time DESC limit %d', array($this->_table, $activityid, $start, $limit_actual, $this->_noq_table, $activityid, $start, $limit, $limit+$limit_actual));

        if($this->_allowmem && !empty($data)){
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }

    public function get_win_meiqiu_list_by_activityid($activityid, $start, $limit)
    {
        if($this->_allowmem){
            $cache_ttl = 60; 
            $key       = 'lottery_activity_win_meiqiu_list_'.$activityid.'_'.$start.'_'.$limit;
            $data      = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }
        $data = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d ORDER BY id DESC limit %d, %d', array($this->_noq_table, $activityid, $start, $limit));

        if($this->_allowmem && !empty($data)){
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }

    public function get_actual_win_by_activityid($activityid, $start, $limit)
    {
        if($this->_allowmem){
            $cache_ttl = 180; 
            $key       = 'lottery_activity_actual_win_'.$activityid.'_'.$start.'_'.$limit;
            $data      = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }        
        $data = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d AND awardid IN(SELECT id FROM pre_lottery_award WHERE type=3) ORDER BY id DESC limit %d, %d', array($this->_table, $activityid, $start, $limit*2));

        if($this->_allowmem && !empty($data)){
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;        
    }

    public function get_win_real_list_by_activityid($activityid, $start, $limit)
    {
        if($this->_allowmem){
            $cache_ttl = 60; 
            $key       = 'lottery_activity_win_real_list_'.$activityid.'_'.$start.'_'.$limit;
            $data      = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }
        $data = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d ORDER BY id DESC limit %d, %d', array($this->_table, $activityid, $start, $limit));

        if($this->_allowmem && !empty($data)){
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }

    public function get_win_list_by_activityid_uid($activityid, $uid)
    {
        if($this->_allowmem){
            $cache_ttl = 86400; 
            $key       = 'lottery_win_list_'.$activityid.'_'.$uid;
            $data      = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }        

        $data = $this->_db->fetch_all('SELECT `id` ,`uid` ,`username`,`ip` ,`awardid`,`activityid` ,`win_time`,`sended` ,`send_time` ,`address_info`, `qid` FROM %t WHERE uid=%d AND activityid=%d ORDER BY `win_time` DESC', array($this->_table, $uid, $activityid, $this->_noq_table, $uid, $activityid));

        if($this->_allowmem && !empty($data)){
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }


    public function get_list_by_uid($uid, $start, $limit)
    {
        if($this->_allowmem){
            $cache_ttl = 300; 
            $mem_key   = 'lottery_win_user_list_'.$uid.'_'.$start;
            $data      = $this->_memory->cmd('get',$mem_key); 
            if($data!==false){      
                return $data;
            }
        }

        $data = $this->_db->fetch_all('SELECT id,awardid,win_time,activityid,address_info,stuff FROM %t WHERE uid=%d ORDER BY win_time DESC LIMIT %d, %d', array($this->_table, $uid, $start, $limit));

        if($this->_allowmem){
            $this->_memory->cmd('set', $mem_key, $data, $cache_ttl);
        }

        return $data;       
    }

    public function delete_win_cache($uid, $activityid)
    {
        $key = 'lottery_win_user_list_'.$uid.'_0';
        $this->_memory->cmd('rm',$key); 
        $key = 'lottery_win_list_'.$activityid.'_'.$uid;
        $this->_memory->cmd('rm',$key);
        //$key = 'lottery_activity_win_list_'.$activityid.'_0'; 
        //$this->_memory->cmd('rm',$key);
        return true;
    }


    public function get_win_list_by_activityid_awardid($activityid, $awardid, $start_time, $end_time, $start, $limit, $sort = 'desc')
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data = $this->_db->fetch_all('SELECT `id` ,`uid` ,`username`,`ip` ,`awardid`,`activityid` ,`win_time`,`sended` ,`send_time` ,`address_info`, `qid` FROM %t WHERE activityid=%d '.$con.' ORDER BY id DESC limit %d, %d', array($this->_table, $activityid, $start, $limit));

        return $data;
    }

    public function get_win_count_by_activityid_awardid($activityid, $awardid, $start_time, $end_time)
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t WHERE activityid=%d '.$con, array($this->_table, $activityid));
        return $data['cnt'];
    }

    public function get_win_noq_list_by_activityid_awardid($activityid, $awardid, $start_time, $end_time, $start, $limit, $sort = 'desc')
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data = $this->_db->fetch_all('SELECT `id` ,`uid` ,`username`,`ip` ,`awardid`,`activityid` ,`win_time`,`sended` ,`send_time` ,`address_info` FROM %t WHERE activityid=%d '.$con.' ORDER BY id DESC limit %d, %d', array($this->_noq_table, $activityid, $start, $limit));

        return $data;
    }

    public function get_win_noq_count_by_activityid_awardid($activityid, $awardid, $start_time, $end_time)
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t WHERE activityid=%d '.$con, array($this->_noq_table, $activityid));
        return $data['cnt'];
    }


    public function get_win_list_by_activityid_awardid_for_send($activityid, $awardid, $start_time, $end_time, $start, $limit, $sort = 'desc')
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data   = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d AND awardid=%d AND sended=0 '.$con.' ORDER BY id DESC limit %d, %d', array($this->_table, $activityid, $awardid, $start, $limit));
        return $data;
    }

    public function get_win_count_by_activityid_awardid_for_send($activityid, $awardid, $start_time, $end_time)
    {
        $con  = $this->get_condition($awardid, $start_time, $end_time);
        $data = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t WHERE activityid=%d AND sended=0'.$con, array($this->_table, $activityid));
        return $data['cnt'];
    }

    public function get_minute_wined($activityid, $awardid, $minute)
    {
        $key = 'minute_wined_'.$activityid.'_'.$awardid.'_'.$minute;
        return $this->_memory->cmd('get', $key);
    }

    public function inc_minute_wined($activityid, $awardid, $minute)
    {
        $key = 'minute_wined_'.$activityid.'_'.$awardid.'_'.$minute;
        $res = $this->_memory->cmd('inc', $key);

        if($res<2){
            // $redis = C::memory()->get_memory_obj();
            $redis = $this->_memory->get_memory_obj();
            $redis->expire('gxJDlH_'.$key, 100);
        }
        return true;
    }


    public function set_sended($ids, $time)
    {
        if(empty($ids)){
            return false;
        }

        $this->_db->query('UPDATE %t SET sended=1,send_time='.intval($time).' WHERE id IN('.implode(',', $ids).')', array($this->_noq_table));
        return $this->_db->query('UPDATE %t SET sended=1,send_time='.intval($time).' WHERE id IN('.implode(',', $ids).');', array($this->_table));
    }

    public function get_condition($awardid, $start_time, $end_time)
    {
        $conditions = array();
        if($awardid){
            $conditions[] = 'awardid='.intval($awardid);
        }
        if($start_time){
            $conditions[] = 'win_time>='.intval($start_time);
        }
        if($end_time){
            $conditions[] = 'win_time<='.intval($end_time);
        }

        return !empty($conditions) ? ' AND '.implode(' AND ', $conditions) : '';
    }

}