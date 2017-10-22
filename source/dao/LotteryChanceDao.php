<?php
class LotteryChanceDao extends BaseDao {
    private $_redis        = null;
    private $_allow_memory = true;
    private $_cache_time   = 600;
    public function __construct() {

        $this->_table         = 'lottery_chance';
        $this->_pk            = 'id';
        $this->_pre_cache_key = 'lottery_chance_';
        $this->_allowmem      = false;
        parent::__construct();
        $this->_redis         = $this->_memory->get_memory_obj();
    }

    public function get_user_chance($activityid, $uid)
    {
    	if($this->_allow_memory){
            $key       = 'lottery_chance_'.$uid.'_'.$activityid;
            $data      = $this->_redis->hGetAll($key);
			if(!empty($data)){
				return $data;
			}
    	}
        // explain type: const
    	$data = $this->_db->fetch_first('SELECT * FROM %t WHERE uid=%d AND activityid=%d', array($this->_table, $uid, $activityid));
    	if($this->_allow_memory && !empty($data)){
            $this->_redis->hMset($key, $data);
            $this->_redis->expire($key, $this->_cache_time);
    	}
    	return $data;
    }


    public function insert_or_update_chance($uid, $activityid, $init_chance, $dateline, $id=0)
    {
        $data = array(
            'uid'           => $uid, 
            'activityid'    => $activityid, 
            'chance'        => $init_chance,
            'used'          => 0,
            'today_win_cnt' => 0,
            'dateline'      => $dateline
        );

        if($this->_allow_memory){
            $key  = 'lottery_chance_'.$uid.'_'.$activityid;
            $this->_redis->hMset($key, $data);
            $this->_redis->expire($key, $this->_cache_time);
            unset($data['today_win_cnt']);
        }

        return $this->insert_or_update($data, true);

        // if($id){
        //     $ret = $this->update_db_cache($id, $data);
        // }else{
        //     $ret = $this->insert($data, true);
            
        //     if($this->_allow_memory){
        //         $key  = 'lottery_chance_'.$uid.'_'.$activityid;
        //         memory('rm', $key);
        //         $this->_redis->hMset($key, $data);
        //         $this->_redis->expire($key, $this->_cache_time);
        //     }
        // }
        // return $ret;      
    }

    public function increase($activityid, $uid, $field, $value=1)
    {
        $verifyed_fields = array('paid_used', 'paid_chance', 'chance', 'used');
        if(!in_array($field, $verifyed_fields)){
            return false;
        }
        $value    = intval($value);
        $dateline = intval(TIMESTAMP);
        if($this->_allow_memory){
            $key    = 'lottery_chance_'.$uid.'_'.$activityid;
            $val = $this->_redis->hGet($key, $field);
            if($val!==false){ //加这个判断是担心过期
                $new_val = $this->_redis->hIncrBy($key, $field, $value);
                $this->_redis->hSet($key, 'dateline', $dateline);
                if($new_val <= $val){
                    $this->_memory->cmd('rm', $key);
                }
            }
        }

        return $this->_db->query('UPDATE %t SET `'.$field.'`=`'.$field.'`+'.$value.', dateline='.$dateline.' WHERE uid=%d AND activityid=%d', array($this->_table, $uid, $activityid));          
    }

    public function update_db_cache($id, $data)
    {
        if($this->_allow_memory){
            $key  = 'lottery_chance_'.$data['uid'].'_'.$data['activityid'];
            $this->_redis->hMset($key, $data);
            $this->_redis->expire($key, $this->_cache_time);
        }
        
        return $this->update($id, $data);
    }

    public function get_cache($activityid, $uid, $field)
    {
        $key = 'lottery_chance_'.$uid.'_'.$activityid;
        return $this->_redis->hGet($key, $field);        
    }

    public function set_cache($activityid, $uid, $field, $value)
    {       
        $key      = 'lottery_chance_'.$uid.'_'.$activityid;
        $dateline = intval(TIMESTAMP);
       
        $this->_redis->hSet($key, 'dateline', $dateline);
        return $this->_redis->hSet($key, $field, $value);
    }

    public function sub_increase_cache($activityid, $uid, $field, $value=1)
    {
        $key      = 'lottery_chance_'.$uid.'_'.$activityid;
        $value    = intval($value);
        $dateline = intval(TIMESTAMP);
       
        $this->_redis->hSet($key, 'dateline', $dateline);

        $new_value = $this->_redis->hIncrBy($key, $field, $value);
        if($new_value <= $value){
            $this->_memory->cmd('rm', $key);
        }

        return true;
    }
}