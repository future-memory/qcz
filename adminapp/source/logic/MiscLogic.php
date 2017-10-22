<?php
class MiscLogic extends Logic 
{
	private $_subject_dao;
	private $_data_dao;
	
	public function __construct() 
	{
		$this->_subject_dao = ObjectCreater::create('MiscSubjectDao');
		$this->_data_dao = ObjectCreater::create('MiscDataDao');
	}

	public function get_subject_list($start, $limit, $sort = 'desc') 
	{
		$list = $this->_subject_dao->range($start, $limit, 'desc');
		return $list;
	}

	public function get_data_list_by_key($key, $start, $limit) 
	{
		return $this->_data_dao->fetch_list_by_key($key, $start, $limit);
	}

	public function get_subject_count() 
	{
		$count = $this->_subject_dao->count();
		return $count;
	}

	public function get_subject_by_key($key, $force_db = false) 
	{
		return $this->_subject_dao->fetch($key, $force_db);
	}

	public function get_data_by_id($id, $force_db = false) 
	{
		return $this->_data_dao->fetch($id, $force_db);
	}

	public function insert_or_update_subject($data) 
	{
		$this->_subject_dao->insert_or_update($data);
		$this->_data_dao->del_data_list_cache($data['key']);
	}

	public function post_data($data) 
	{
		$this->_data_dao->insert($data);
		$this->_data_dao->del_data_list_cache($data['key']);
	}

	public function update_data($id, $data) 
	{
		$this->_data_dao->update($id, $data);
		$this->_data_dao->del_data_list_cache($data['key']);
	}

	public function delete_data_by_id($id, $key) 
	{
		$this->_data_dao->delete($id);
		$this->_data_dao->del_data_list_cache($key);
	}
}