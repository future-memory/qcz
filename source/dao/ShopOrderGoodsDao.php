<?php

class ShopOrderGoodsDao extends BaseDao
{

	public function __construct() 
	{
		$this->_table         = 'shop_order_goods';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'shop_order_goods_';

		parent::__construct();

		$this->_allowmem = true;
	}

	public function get_list_by_orderid($orderid)
	{
		$orderid = intval($orderid);
		if(!$orderid){
			return array();
		}
		$sql      = 'SELECT * FROM %t WHERE order_id=%d ';
		$result   = $this->_db->fetch_all($sql, array($this->_table, $orderid));

        return $result;
	}

	public function get_list_by_orderids($orderids)
	{
		if(!is_array($orderids) || empty($orderids)){
			return array();
		}
		$orderids = array_map('intval', $orderids);
		$sql      = 'SELECT name,order_id,goods_id,cover_pic,og.price as price,og.count as count FROM '.$this->_db->table('shop_order_goods').' og LEFT JOIN '.$this->_db->table('shop_goods').' g ON g.id=og.goods_id  WHERE og.order_id in('.implode(',', $orderids).')';
		$result   = $this->_db->fetch_all($sql);

        return $result;
	}

}
