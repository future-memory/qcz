<?php

class KVDataDao extends BaseDao
{

    public function __construct() 
    {
		$this->_table = 'kvdata';
		$this->_pk    = 'key';
		$this->_cache_ttl = 3600;
		parent::__construct();
    }


}