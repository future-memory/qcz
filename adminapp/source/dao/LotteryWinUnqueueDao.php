<?php
class LotteryWinUnqueueDao extends BaseDao {
    private $_noq_table = '';

    public function __construct() {

		$this->_table         = 'lottery_win_unqueue';
		$this->_pk            = 'id';
        parent::__construct();
    }	
}