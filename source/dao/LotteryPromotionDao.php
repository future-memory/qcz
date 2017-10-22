<?php
class LotteryPromotionDao extends BaseDao
{
    public function __construct() 
    {
		$this->_table         = 'lottery_promotion';
		$this->_pk            = 'id';
        parent::__construct();
    }

	public function get_current_promotion()
	{
		if($this->_allowmem){
			$key = 'get_current_promotion';
    		$data = $this->_memory->cmd('get', $key);
    		if($data!=false){
    			return $data;
    		}
    	}

		$sql = 'SELECT * FROM %t where is_current=1 ';
		$data = $this->_db->fetch_first($sql, array($this->_table, 'lottery_activity'));

		if($this->_allowmem){
			$this->_memory->cmd('set', $key, $data, 60);
		}

		return $data;
	}


}