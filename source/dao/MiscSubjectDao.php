<?php

class MiscSubjectDao extends BaseDao
{
    public function __construct() 
    {
		$this->_table     = 'app_misc_subject';
		$this->_pk        = 'key';
		$this->_cache_ttl = 600;
		parent::__construct();
    }
}
