<?php

class ShopGoodsDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table         = 'shop_goods';
		$this->_pk            = 'id';

		parent::__construct();
	}

	public function get_list($type=0, $price_start=0, $price_end=0, $start=0, $limit=15)
	{
		if($this->_allowmem){
			$mem_key = 'shop_goods_list_'.$type.'_'.$price_start.'_'.$price_end.'_'.$start.'_'.$limit;
			$mem_key = defined('API_ROOT') ? $mem_key : $mem_key.'_0';

			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data;
			}
		}

		$condition = $this->get_condition($type, $price_start, $price_end);
		$data      = $this->_db->fetch_all('SELECT * FROM %t '.$condition.' ORDER BY sort_order DESC, id DESC LIMIT %d, %d', array($this->_table, $start, $limit));
		if($this->_allowmem){
			$this->_memory->cmd('set', $mem_key, $data, 120);
		}
        return $data;		
	}

	public function get_count($type=0, $price_start=0, $price_end=0)
	{
		if($this->_allowmem){
			$mem_key = 'shop_goods_count_'.$type.'_'.$price_start.'_'.$price_end;
			$mem_key = defined('API_ROOT') ? $mem_key : $mem_key.'_0';

			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data;
			}
		}

		$condition = $this->get_condition($type, $price_start, $price_end);
		$data      = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t '.$condition, array($this->_table));
		
		if($this->_allowmem){
			$this->_memory->cmd('set', $mem_key, $data['cnt'], 120);
		}

        return $data['cnt'];		
	}

	public function increase_goods_count($goods_id, $count)
	{
		$this->clear_cache($goods_id);
		$sql = $count>0 ? 'UPDATE %t SET count=count+%d WHERE id=%d' : 'UPDATE %t SET count=if(count>%d,count+%d,0) WHERE id=%d';
		return $count>0 ? $this->_db->query($sql, array($this->_table, $count, $goods_id)) : $this->_db->query($sql, array($this->_table, abs($count), $count, $goods_id));
	}

	private function get_condition($type, $price_start, $price_end, $online=1)
	{
		$type        = intval($type);
		$price_start = intval($price_start);
		$price_end   = intval($price_end);
		$conditions  = array();


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
