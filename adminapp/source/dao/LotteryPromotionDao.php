<?php
class LotteryPromotionDao extends BaseDao
{
    public function __construct() {

		$this->_table         = 'lottery_promotion';
		$this->_pk            = 'id';
		$this->_allowmem      = false;

        parent::__construct();
    }

	public function get_promotion_list($start, $limit)
	{
		$sql = 'SELECT a.name,lottery_id,p.id,p.is_current FROM %t p LEFT JOIN %t a ON p.lottery_id=a.id ORDER BY p.id DESC '.$this->_db->limit($start, $limit);
		return $this->_db->fetch_all($sql, array($this->_table, 'lottery_activity'));
	}

	public function set_current($id)
	{
		$this->_db->query('update %t set is_current=0 ', array($this->_table));
		$this->update($id, array('is_current'=>1));

		$key = 'get_current_promotion';
    	$this->_memory->cmd('rm', $key);		

		return true;
	}


}