<?php
class ShopGoodsDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'shop_goods';
		$this->_pk    = 'id';

		parent::__construct();
		$this->_cache_ttl = 600;
	}

	public function get_goods_list($domain, $start=0, $limit=15)
	{
		$bool  = $domain && $domain!='www';
		$where = $bool ? 'WHERE domain=%s' : '';
		$param = $bool ? array($this->_table, $domain, $start, $limit) : array($this->_table, $start, $limit);
		$data  = $this->_db->fetch_all('SELECT * FROM %t '.$where.' ORDER BY id DESC LIMIT %d, %d', $param);

        return $data;		
	}

	public function get_goods_count($domain)
	{
		$bool  = $domain && $domain!='www';
		$where = $bool ? 'WHERE domain=%s' : '';
		$param = $bool ? array($this->_table, $domain) : array($this->_table);	
		$data  = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t '.$where, $param);

        return $data['cnt'];		
	}


	public function increase_goods_count($goods_id, $count)
	{
		$this->clear_cache($goods_id);
		$sql = $count>0 ? 'UPDATE %t SET count=count+%d WHERE id=%d' : 'UPDATE %t SET count=if(count>%d,count+%d,0) WHERE id=%d';
		return $count>0 ? $this->_db->query($sql, array($this->_table, $count, $goods_id)) : $this->_db->query($sql, array($this->_table, abs($count), $count, $goods_id));
	}

	private function get_condition($type, $price_start, $price_end, $belong=null, $online=1)
	{
		$type = intval($type);
		$price_start = intval($price_start);
		$price_end = intval($price_end);
		$conditions = array();

		if($online){
			$conditions[] = '`is_online`=1';
		}
		if($type){
			$conditions[] = '`type`='.$type;
		}
		if($price_start){
			$conditions[] = '`price`>='.$price_start;
		}
		if($price_end){
			$conditions[] = '`price`<='.$price_end;
		}

		return empty($conditions) ? '' : ' WHERE '.implode(' AND ', $conditions);
	}


}
