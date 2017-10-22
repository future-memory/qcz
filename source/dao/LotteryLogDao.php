<?php
class LotteryLogDao extends BaseDao
{
    public function __construct() {

		$this->_table         = 'lottery_log';
		$this->_pk            = 'id';
		$this->_allowmem      = false;

        parent::__construct();
    }

	public function delete_exceed($exceedtime) 
	{
		$this->_db->query("DELETE FROM %t WHERE dateline<%d", array($this->_table, TIMESTAMP - intval($exceedtime)), false, true);
	}

	public function get_statistic($activityid, $start_time, $end_time)
	{
		$sql = 'SELECT SUM(IF(pay>0,1,0)) AS paid, SUM(1) AS total, SUM(IF(pay>0 and result=200,1,0)) AS paid_win, SUM(IF(result=200,1,0)) AS win FROM '.$this->_db->table($this->_table).' WHERE dateline>='.intval($start_time).' AND dateline<='.intval($end_time).($activityid ? ' AND activityid='.intval($activityid) : '');
		return $this->_db->fetch_first($sql);
	}
	/**
	 * 查询该用户是否抽过奖
	 * @param int $uid
	 * @param int $date
	 * @return array
	 */
	public function fetch_by_uid($uid)
	{
		$mem_key = "table_lottery_log::fetch_by_uid_".$uid;
		$_allowmem = $this->_memory->cmd('check');
		if($_allowmem) {
			$data = $this->_memory->cmd('get',$mem_key);
			if($data !== false) {
				return $data;
			}
		}			
		$sql = 'select * from '.$this->_db->table($this->_table). '  where  uid='.intval($uid).' limit 1';
		$result = $this->_db->fetch_first($sql);
		$_allowmem && $this->_memory->cmd('set', $mem_key, $result, 5);
		return $result;
	}
	/**
	 * 查询该用户抽奖次数
	 * @param int $uid
	 * @param int $date
	 * @return array
	 */
	public function fetch_count_uid_dateline($uid,$dateline){
		$uid = intval($uid);
		$dateline =  intval($dateline);
		$sql = 'select count(1) from '.$this->_db->table($this->_table). '  where  uid='.intval($uid) .' and dateline>='.$dateline;
		$count =  (int)$this->_db->result_first($sql);
		return $count;
	}

}