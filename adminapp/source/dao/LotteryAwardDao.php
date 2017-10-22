<?php
class LotteryAwardDao extends BaseDao 
{
    public function __construct() 
    {

		$this->_table         = 'lottery_award';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'lottery_award_';
		$this->_allowmem      = true;

        parent::__construct();
    }

}
