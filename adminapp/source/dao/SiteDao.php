<?php
class SiteDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'site';
		$this->_pk    = 'domain';
		parent::__construct();
	}

}
