<?php

class AdminRolePermDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'admin_role_perm';
		$this->_pk    = 'id';
		parent::__construct();
	}

	public function fetch_all_by_role($role_id) 
	{
		if(!$role_id){
			return array();
		}
		return $this->_db->fetch_all('SELECT * FROM %t WHERE role_id=%d', array($this->_table, $role_id));
	}

	public function delete_perms_by_role($role_id)
	{
		if(!$role_id){
			return false;
		}

		return $this->_db->query('DELETE FROM %t WHERE role_id=%d', array($this->_table, $role_id));
	}


}
