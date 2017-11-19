<?php

class ShopGoodsTypeDao extends BaseDao
{

	public $cache_ttl;
	public $allowmem;

	public function __construct() 
	{
		$this->_table = 'shop_goods_type';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function get_list($start, $limit)
	{
        $data = $this->_db->fetch_all('SELECT * FROM %t limit %d, %d', array($this->_table, $start, $limit));
        return $data;	
	}

	public function get_goods_types($domain)
	{
		$bool  = $domain && $domain!='www';
		$where = $bool ? 'WHERE domain=%s' : '';
		$param = $bool ? array($this->_table, $domain) : array($this->_table);
        $data  = $this->_db->fetch_all('SELECT * FROM %t '.$where, $param, $this->_pk);

        return $data;
	}


}