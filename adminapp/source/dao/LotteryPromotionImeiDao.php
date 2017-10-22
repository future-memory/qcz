<?php
class LotteryPromotionImeiDao extends BaseDao
{
    public function __construct() 
    {
		$this->_table         = 'lottery_promotion_imei';
		$this->_pk            = 'imei';
        parent::__construct();
    }

	public function get_imei_list_by_pid($pid, $start, $limit)
	{
		$sql = 'SELECT * FROM %t where pid=%d '.$this->_db->limit($start, $limit);
		return $this->_db->fetch_all($sql, array($this->_table, $pid));
	}

	public function get_imei_count_by_pid($pid)
	{
		$sql = 'SELECT count(*) FROM %t where pid=%d ';
		return $this->_db->result_first($sql, array($this->_table, $pid));
	}

	public function del_by_imei_pid($imei, $pid)
	{
		$this->clear_cache($imei);
		return $this->_db->query('delete from %t where imei=%s and pid=%d', array($this->_table, $imei, $pid));
	}

	public function get_used_imei_list_by_pid($pid, $start, $limit)
	{
		$sql = 'SELECT i.imei,w.win_time FROM %t i LEFT JOIN %t w ON i.`uid` = w.`uid` WHERE i.pid=%d AND i.uid>0 ORDER BY w.win_time ASC '.$this->_db->limit($start, $limit);
		return $this->_db->fetch_all($sql, array($this->_table, 'lottery_win', $pid));
	}

	public function get_used_imei_count_by_pid($pid)
	{
		$sql = 'SELECT count(*) FROM %t WHERE pid=%d AND uid>0';
		return $this->_db->result_first($sql, array($this->_table, $pid));
	}


}