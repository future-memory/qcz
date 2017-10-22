<?php
class LotteryActivityAwardQueueDao extends BaseDao {
    private $_redis = null;

    public function __construct() {

        $this->_table         = 'lottery_activity_award_queue';
        $this->_pk            = 'id';
        $this->_pre_cache_key = 'lottery_activity_award_queue_';
        $this->_allowmem      = true;
        parent::__construct();
        // $this->_redis         = C::memory()->get_memory_obj();
        $this->_redis         = $this->_memory->get_memory_obj();
    }

    public function get_award_queue_by_activityid($activityid, $awardid=0, $start=0, $limit=10, $sort = 'desc')
    {
        $sort      = strtoupper($sort)=='ASC' ? 'ASC' : 'DESC';
        $condition = $awardid ? ' AND awardid='.intval($awardid) : '';
        $data      = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d '.$condition.' ORDER BY send_time '.$sort.' limit %d, %d', array($this->_table, $activityid, $start, $limit));
        return $data;       
    }

    public function get_award_queue_count_by_activityid($activityid, $awardid=0)
    {
        $condition = $awardid ? ' AND awardid='.intval($awardid) : '';
        $data = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t WHERE activityid=%d '.$condition, array($this->_table, $activityid));
        return $data['cnt'];       
    }    

    public function get_nearest_award($activityid)
    {
        if($this->_allowmem){
            $key       = 'lottery_activity_award_queue_nearest_'.$activityid;            
            $data      = $this->_memory->cmd('get', $key);
            if(!empty($data)){
                return $data;
            }
        }
    	$data = $this->_db->fetch_all('SELECT * FROM %t WHERE activityid=%d AND flag=0 ORDER BY send_time asc limit 3', array($this->_table, $activityid));
        if($this->_allowmem){
            $cache_ttl = 300; 
            $this->_memory->cmd('set', $key, $data, $cache_ttl);
        }
        return $data;
    }

    public function get_nearest_award_plus($activityid, $awardids=null)
    {
        if($this->_allowmem){
            $key = 'lottery_activity_award_queue_nearest_virtual_'.$activityid.($awardids ? '_'.implode('_', $awardids) : '');            
            $ids = $this->_redis->zRange($key, 0, -1);
            if(!empty($ids)){
                return $this->fetch_all($ids);
            }
        }
        $data = $this->_db->fetch_all('SELECT * FROM %t force index(idx_lottery_queue_activityid_awardid) WHERE activityid=%d AND flag=0 '.($awardids ? 'AND awardid IN('.implode(',', $awardids).')' : '').' ORDER BY send_time asc limit 50', array($this->_table, $activityid));
        if($this->_allowmem && $data){
            foreach($data as $item){
                $this->_redis->zAdd($key, intval($item['send_time']), intval($item['id']));
            }
        }
        return $data;
    }

    public function del_nearest_award_id_set_cache($activityid, $awardids, $qid)
    {
        $key = 'lottery_activity_award_queue_nearest_virtual_'.$activityid.($awardids ? '_'.implode('_', $awardids) : '');
        if(in_array(19,$awardids)){
            $awardids = count($awardids)>1 ? array(19) : array(9,20,21,19);
            $this->_redis->zDelete($key, $qid);
            $key = 'lottery_activity_award_queue_nearest_virtual_'.$activityid.($awardids ? '_'.implode('_', $awardids) : '');
        }
        return $this->_redis->zDelete($key, $qid);
    }

    public function get_activity_award_counts($activityid, $awardids)
    {
        if(empty($awardids)){
            return array();
        }
        return $this->_db->fetch_all('SELECT awardid, count(*) as cnt,sum(IF(flag=0,1,0)) as `qleft` FROM %t WHERE activityid=%d AND awardid IN('.implode(',', $awardids).') GROUP BY awardid', array($this->_table, $activityid), 'awardid');
    }    

    public function del_nearest_award_cache($activityid)
    {
        $key = 'lottery_activity_award_queue_nearest_'.$activityid;            
        return $this->_memory->cmd('rm', $key);
    }


    public function clear_queue_by_activityid($activityid)
    {
        return $this->_db->query('DELETE FROM %t WHERE activityid=%d', array($this->_table, $activityid));
    }

    public function clear_queue_by_activityid_awardid($activityid, $awardid)
    {
        return $this->_db->query('DELETE FROM %t WHERE activityid=%d AND awardid=%d', array($this->_table, $activityid, $awardid));
    }

    //清理已经被抽的
    public function delete_exceed($exceedtime) 
    {
        $this->_db->query("DELETE FROM %t WHERE flag=1 AND send_time<%d", array($this->_table, TIMESTAMP - intval($exceedtime)), false, true);
    } 	
}