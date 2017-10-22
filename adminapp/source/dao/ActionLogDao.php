<?php
class ActionLogDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'common_member_action_log';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete_by_dateline($timestamp) 
	{
		$this->_db->delete($this->_table, 'dateline < '.HelperUtils::dintval($timestamp));
	}

	public function count_day_hours($action, $uid) 
	{
		return $this->_db->result_first('SELECT COUNT(*) FROM %t WHERE dateline>%d AND action=%d AND uid=%d', array($this->_table, TIMESTAMP - 86400, $action, $uid));
	}

	public function count_per_hour($uid, $type) 
	{
		return $this->_db->result_first('SELECT COUNT(*) FROM %t WHERE dateline>%d AND `action`=%d AND uid=%d', array($this->_table, TIMESTAMP - 3600, HelperLog::get_user_action($type), $uid));
	}

	public function delete_by_uid($uids) 
	{
		$this->_db->delete($this->_table, 'uid IN ('.HelperUtils::dimplode($uids).')');
	}

}
