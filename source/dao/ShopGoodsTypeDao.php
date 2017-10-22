<?php

class ShopGoodsTypeDao extends BaseDao
{

	public function __construct() 
	{
		$this->_table = 'shop_goods_type';
		$this->_pk    = 'id';

		parent::__construct();

		$this->_allowmem = true;
	}

	public function get_list($start, $limit)
	{
        $data = $this->_db->fetch_all('SELECT * FROM %t limit %d, %d', array($this->_table, $start, $limit));
        return $data;		
	}


}