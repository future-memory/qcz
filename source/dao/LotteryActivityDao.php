<?php
//抽奖活动表
class LotteryActivityDao extends BaseDao 
{
    public function __construct() 
    {

		$this->_table         = 'lottery_activity';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'lottery_activity_';
		$this->_allowmem      = true;
		$this->_cache_ttl 	  = 600;

        parent::__construct();
    }

    /**
     * 获取所有可用活动
     */
   	public function fetch_all_enble()
   	{
   		$sql = 'SELECT * FROM %t WHERE enable IN(1,2) AND end_time>%d ORDER BY ID DESC LIMIT 50';
   		return $this->_db->fetch_all($sql, array($this->_table, TIMESTAMP));
   	}


}