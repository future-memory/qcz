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

	//domain is null 为所有站点都可以用的角色
	public function get_role_list($domain)
	{
		$where = $domain && $domain!='www' ? 'WHERE domain=%s OR domain IS NULL ' : '';
		$param = $domain && $domain!='www' ? array($this->_table, $domain) : array($this->_table);
		return $this->_db->fetch_all('SELECT * FROM %t '.$where.' ORDER BY id DESC', $param);
	}


}
