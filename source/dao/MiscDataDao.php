<?php

class MiscDataDao extends BaseDao
{

    public function __construct() 
    {
		$this->_table     = 'misc_data';
		$this->_pk        = 'id';
		$this->_cache_ttl = 600;
		parent::__construct();
    }

	public function get_list($key, $start, $limit,$envirnment=0)
	{
		if($this->_allowmem && $envirnment!=1){//灰度验证不用缓存
			$mem_key = $this->_pre_cache_key.$key.'_'.intval($envirnment);
			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data;
			}
		}

		$sql_add  = '';
		if($key){
			$sql_add = '  and `key`='.$this->_db->quote($key);
		}
		if($envirnment != 1){
			$sql_add .= ' and `envirnment`=0';
		}
        $data = $this->_db->fetch_all('SELECT * FROM %t WHERE 1=1 '.$sql_add.' ORDER BY `order` DESC, id DESC LIMIT %d, %d', array($this->_table, $start, $limit));

		if($this->_allowmem &&  $envirnment!=1){
			$this->_memory->cmd('set', $mem_key, $data, $this->_cache_ttl);
		}

        return $data;		
	}	

	public function get_count($type)
	{
		$sql_key = '';
		if($key){
			$sql_key = 'WHERE `key`='.$this->_db->quote($key);
		}
        $data = $this->_db->fetch_all('SELECT count(*) as cnt FROM %t '.$sql_key, array($this->_table));
        return $data['cnt'];		
	}


	public function admin_get_list($key, $start, $limit)
	{
		$sql_key = '';
		if($key){
			$sql_key = 'WHERE `key`='.$this->_db->quote($key);
		}
        $data = $this->_db->fetch_all('SELECT * FROM %t '.$sql_key.' ORDER BY `order` DESC, id DESC LIMIT %d, %d', array($this->_table, $start, $limit));

        return $data;		
	}	


	public function get_list_by_keys($keys)
	{
		if(empty($keys)){
			return array();
		}
		if($this->_allowmem){
			$mem_key = $this->_pre_cache_key.implode('_', $keys);
			$data = $this->_memory->cmd('get',$mem_key);	
			if($data!==false){		
				return $data;
			}
		}

		$sql_key = '';
		foreach($keys as $key){
			$sql_key .= $this->_db->quote($key).',';
		}
		$sql_key = trim($sql_key, ',');

        $data = $this->_db->fetch_all('SELECT * FROM %t WHERE `key` IN('.$sql_key.') ORDER BY id DESC LIMIT 10', array($this->_table));

		if($this->_allowmem){
			$this->_memory->cmd('set', $mem_key, $data, 60); //$this->_cache_ttl
		}

        return $data;		
	}
	

}
