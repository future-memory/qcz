<?php

class ShopOrderDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table         = 'shop_order';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'shop_order_';

		parent::__construct();

		$this->_allowmem = true;
		$this->_cache_ttl = 120;
	}

	public function get_count($status=0, $goods_id=0, $start_time=0, $end_time=0)
	{
		$where = $this->get_where($status, $goods_id, $start_time, $end_time);
		$data  = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t '.$where, array($this->_table));
        return $data ? $data['cnt'] : 0;		
	}

	public function get_list($status=0, $goods_id=0, $start_time=0, $end_time=0, $start=0, $limit=15)
	{
		$where = $this->get_where($status, $goods_id, $start_time, $end_time);	
        $data = $this->_db->fetch_all('SELECT * FROM %t '.$where.' ORDER BY id DESC limit %d, %d', array($this->_table, $start, $limit));
        return $data;		
	}

	public function get_count_by_uid($uid)
	{
		if($this->_allowmem){
			$mem_key = 'shop_order_user_count_'.$uid;
			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data ? $data['cnt'] : 0;
			}
		}		
		$data  = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t WHERE uid=%d', array($this->_table, $uid));

 		if($this->_allowmem){
			$this->_memory->cmd('set', $mem_key, $data, $this->_cache_ttl);
		}
        return $data ? $data['cnt'] : 0;		
	}

	public function get_list_by_uid($uid, $start, $limit)
	{
		if($this->_allowmem){
			$mem_key = 'shop_order_user_list_'.$uid.'_'.$start;
			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data;
			}
		}

        $data = $this->_db->fetch_all('SELECT * FROM %t  WHERE uid=%d ORDER BY id DESC LIMIT %d, %d', array($this->_table, $uid, $start, $limit));

		if($this->_allowmem){
			$this->_memory->cmd('set', $mem_key, $data, $this->_cache_ttl);
		}

        return $data;		
	}	

	public function get_where($status, $goods_id, $start_time, $end_time)
	{
		$where = array();
		if($status){
			$where[] = 'status='.intval($status); 
		}
		if($start_time){
			$where[] = 'dateline>='.intval($start_time); 
		}
		if($start_time){
			$where[] = 'dateline<='.intval($end_time); 
		}
		if($goods_id){
			$where[] = 'id IN(SELECT order_id FROM pre_shop_order_goods WHERE goods_id='.intval($goods_id).')'; 
		}		
		if(!empty($where)){
			return ' WHERE '. implode(' AND ', $where);
		}
		return '';
	}

	public function del_user_order_cache($uid, $start=0)
	{
		//删除缓存
		$mem_key = 'shop_order_user_list_'.$uid.'_'.$start;
		$this->_memory->cmd('rm',$mem_key);
	}
	/**
	 * 判断用户否存在某种状态的单子
	 * @param unknown $uid
	 * @param unknown $status
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_uid_status($uid,$status)
	{
		return $this->_db->fetch_first('select * from '.$this->_db->table($this->_table). ' where uid='.intval($uid).' and status='.intval($status).' limit 1');
			
	}
	/**
	 * 用户每天指定状态订单量
	 * @param unknown $uid
	 * @param unknown $status
	 * @return Ambigous <multitype:, unknown>
	 */
	public function count_by_uid_status_dateline($uid,$status,$dateline)
	{
		$uid = intval($uid);
		$status = HelperUtils::dintval($status,true);
		$dateline = intval($dateline);
		if($this->_allowmem){
			$key = 'shop_order::'.__FUNCTION__.'_'.md5($uid.'_'.serialize($status).'_'.$dateline);
			$count = $this->_memory->cmd('get',$key);
			if($count !== false){
				return intval($count);		
			}
		}
		$status_con = is_array($status) ? ' and status in('.implode(',', $status).')' : ' and status='.$status;
		$count = (int)$this->_db->result_first('select count(1) from '.$this->_db->table($this->_table). ' where uid='.$uid.' and dateline>='.$dateline.$status_con);
		if($this->_allowmem){
			$this->_memory->cmd('set',$key,$count,30);
		}
		return $count;
	}



}
