<?php
class LotteryActivityAwardDao extends BaseDao {

    public function __construct() {

		$this->_table         = 'lottery_activity_award';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'lottery_activity_award_';
		$this->_allowmem      = true;

        parent::__construct();
    }

    public function get_awards_by_activityid($activityid)
    {
        if($this->_allowmem){
            $key  = 'award_list_'.$activityid;            
            $data = $this->_memory->cmd('get', $key);
            if(!empty($data)){
                return $data;
            }
        }        
    	$data = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d AND `status`=1', array($this->_table, $activityid));
        if($this->_allowmem){
            $cache_ttl = 1800; 
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }

    public function get_awards_by_activityid_admin($activityid, $show_all=false)
    {
        $status_sql = $show_all ? '' : 'AND `status`=1';
        $data       = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d '.$status_sql, array($this->_table, $activityid));
        return $data;
    }

    public function get_awards_by_activityid_type_admin($activityid, $queue=false)
    {
        $status_sql = $queue ? 'AND `type`!=2' : 'AND `type`=2';
        $data       = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d '.$status_sql, array($this->_table, $activityid));
        return $data;
    }


    public function get_item_by_activityid_awardid($activityid, $awardid)
    {
        $data = $this->_db->fetch_first('SELECT * FROM %t WHERE activityid=%d and awardid=%d ORDER BY id DESC limit 1', array($this->_table, $activityid, $awardid));
        return $data;
    }

    public function get_probability_by_activity_award($activityid, $awardid)
    {
        if($this->_allowmem){
            $key       = 'lottery_award_probability_'.$activityid.'_'.$awardid;            
            $data      = $this->_memory->cmd('get', $key);
            if(!empty($data)){
                return $data;
            }
        }

        $data = $this->_db->fetch_first('SELECT probability,paid_probability,`status` FROM %t WHERE activityid=%d and awardid=%d', array($this->_table, $activityid, $awardid));

        if($this->_allowmem){
            $cache_ttl = 1800; 
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }

        return $data;
    }

    public function delete_by_activityid_awardid($activityid, $awardid)
    {
        //
        $key = 'award_list_'.$activityid;  
        $this->_memory->cmd('rm', $key);

                //删除缓存
        $key = 'lottery_award_probability_'.$activityid.'_'.$awardid;            
        $this->_memory->cmd('rm', $key);

        return $this->_db->query('UPDATE %t SET `status`=0 WHERE activityid=%d and awardid=%d ', array($this->_table, $activityid, $awardid));
    }

    public function set_activity_award_default($activityid, $awardid, $val)
    {
        //删缓存
        $key = 'award_list_'.$activityid;  
        $this->_memory->cmd('rm', $key);

        return $this->_db->query('UPDATE %t SET `is_default`=%d WHERE activityid=%d and awardid=%d ', array($this->_table, $val, $activityid, $awardid));
    }

    public function recovery_by_activityid_awardid($activityid, $awardid)
    {
        $key = 'award_list_'.$activityid;  
        $this->_memory->cmd('rm', $key);
                //删除缓存
        $key = 'lottery_award_probability_'.$activityid.'_'.$awardid;            
        $this->_memory->cmd('rm', $key);
        
        return $this->_db->query('UPDATE %t SET `status`=1 WHERE activityid=%d and awardid=%d ', array($this->_table, $activityid, $awardid));
    }

    public function des_award_by_activity($activityid, $awardid)
    {
        $key = 'award_list_'.$activityid;  
        $this->_memory->cmd('rm', $key);
                //删除缓存
        $key = 'lottery_award_probability_'.$activityid.'_'.$awardid;            
        $this->_memory->cmd('rm', $key);
        
        return $this->_db->query('UPDATE %t SET `left`=`left`-1 WHERE activityid=%d and awardid=%d ', array($this->_table, $activityid, $awardid));        
    }

    public function distribute_unq_award($activityid, $awardid, $type, $minute_rate, $probability, $paid_probability, $count, $left)
    {
        $key = 'award_list_'.$activityid;  
        $this->_memory->cmd('rm', $key);
                //删除缓存
        $key = 'lottery_award_probability_'.$activityid.'_'.$awardid;            
        $this->_memory->cmd('rm', $key);

        return $this->_db->query("INSERT INTO %t (activityid, awardid, probability, paid_probability, `type`, `minute_rate`, `count`, `left`) values (%d, %d, %d, %d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE probability=%d, paid_probability=%d, type=%d, minute_rate=%d, `count`=`count`+%d, `left`=`left`+%d", array($this->_table, $activityid, $awardid, $probability, $paid_probability, $type, $minute_rate, $count, $left, $probability, $paid_probability, $type, $minute_rate, $count, $left));        
    }	
}