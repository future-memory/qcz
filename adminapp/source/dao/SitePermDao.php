<?php

class SitePermDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'site_perm';
		$this->_pk    = 'id';
		parent::__construct();
	}

	public function fetch_all_by_domain($domain) 
	{
		if(!$domain){
			return array();
		}
		return $this->_db->fetch_all('SELECT * FROM %t WHERE domain=%s', array($this->_table, $domain));
	}

	public function delete_perms_by_domain($domain)
	{
		if(!$domain){
			return false;
		}

		return $this->_db->query('DELETE FROM %t WHERE domain=%s', array($this->_table, $domain));
	}


}
