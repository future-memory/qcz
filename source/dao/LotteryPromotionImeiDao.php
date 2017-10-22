<?php
class LotteryPromotionImeiDao extends BaseDao
{
    public function __construct() 
    {
		$this->_table         = 'lottery_promotion_imei';
		$this->_pk            = 'imei';
        parent::__construct();
    }

    public function get_item_by_imei_pid($imei, $pid)
    {
        if($this->_allowmem){
            $key  = 'promotion_imei_'.$imei.'_'.$pid;
            $data = $this->_memory->cmd('get', $key);
            if($data!==false){
                return $data;
            }
        }
        // explain type: const
        $data = $this->_db->fetch_first('SELECT imei,uid FROM %t WHERE imei=%s AND pid=%d', array($this->_table, $imei, $pid));

        if($this->_allowmem){
            $this->_memory->cmd('set', $key, $data, 60);
        }
        return $data;
    }


    public function get_used_by_uid($uid, $id)
    {
    	if($this->_allowmem){
            $key  = 'promotion_imei_u_'.$uid.'_'.$id;
            $data = $this->_memory->cmd('get', $key);
			if($data!==false){
				return $data;
			}
    	}
        // explain type: const
    	$data = $this->_db->fetch_first('SELECT imei FROM %t WHERE uid=%d AND pid=%d LIMIT 1', array($this->_table, $uid, $id));

    	if($this->_allowmem){
            $this->_memory->cmd('set', $key, $data, 60);
    	}
    	return $data;
    }

    public function del_user_cache($uid, $id)
    {
		$key  = 'promotion_imei_u_'.$uid.'_'.$id;
        $this->_memory->cmd('rm', $key);

    }


}