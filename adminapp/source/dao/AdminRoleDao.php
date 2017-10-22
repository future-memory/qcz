<?php

class AdminRoleDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'admin_role';
		$this->_pk    = 'id';
		parent::__construct();
	}

	public function fetch_by_name($name) 
	{
		$sql = 'SELECT * FROM %t WHERE name=%s';
		return $name ? $this->_db->fetch_first($sql, array($this->_table, $name)) : null;
	}	

}
