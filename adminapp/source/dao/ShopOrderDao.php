<?php
class ShopOrderDao extends BaseDao
{

	public $cache_ttl;
	public $allowmem;

	public function __construct() 
	{
		$this->_table         = 'shop_order';
		$this->_pk            = 'id';
		$this->_pre_cache_key = 'shop_order_';

		parent::__construct();
		$this->_cache_ttl = 120;
	}

	public function get_count($status=0, $goods_id=0, $start_time=0, $end_time=0, $belong=null)
	{
		$where = $this->get_where($status, $goods_id, $start_time, $end_time, $belong);
		$data  = $this->_db->fetch_first('SELECT count(*) as cnt FROM %t '.$where, array($this->_table));
        return $data ? $data['cnt'] : 0;		
	}

	public function get_list($status=0, $goods_id=0, $start_time=0, $end_time=0, $start=0, $limit=15, $belong=null)
	{
		$where = $this->get_where($status, $goods_id, $start_time, $end_time, $belong);	
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

	public function get_where($status, $goods_id, $start_time, $end_time, $belong=null)
	{
		$where = array();
		//belong 是否提供给魅玩帮的，0社区，2lifekit
		if($belong!==null){
			$where[] = '`belong`='.intval($belong) ;
		}
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


}
