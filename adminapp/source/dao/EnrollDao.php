<?php
class EnrollDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'enroll';
		$this->_pk    = 'id';
		parent::__construct();
	}

	/**
	 * 根据主键获取报名信息
	 * @param unknown $id
	 * @param string $cache
	 * @return Ambigous <NULL, string, boolean>|Ambigous <multitype:, unknown>
	 */
	public function fetch_by_id($id,$cache=true)
	{
		$mem_key = __CLASS__.'::'.__FUNCTION__.'_'.$id;
		if($cache && $this->_allowmem){
			$data = $this->_memory->cmd('get',$mem_key);
			if(!empty($data)){
				//return $data;
			}
		}
		$data = $this->_db->fetch_first('select * from '.$this->_db->table($this->_table). ' where id='.$id);
		if($this->_allowmem){
			$this->_memory->cmd('set',$mem_key,$data,60);
		}
		return $data;
	}

	/**
	 * 获取报名活动列表  只有后台会用到
	 * @param number $limit
	 * @param number $offset
	 * @param string $cache
	 * @return Ambigous <NULL, string, boolean>|multitype:unknown
	 */
	public function fetch_all_by_page($limit=20,$offset=0,$cache=true)
	{
		$limit = intval($limit);
		$offset = intval($offset);
		$limit  = $limit > 0 ? $limit : 20;
		$offset = $offset >= 0 ? $offset : 0; 
		$mem_key = __CLASS__.'_'.__FUNCTION__.'_'.$limit.'_'.$offset;
		if($this->_allowmem && $cache){
			$data = $this->_memory->cmd('get',$mem_key);
			if($data){
				return $data;
			}
		}
		$data = $this->_db->fetch_all('select * from '.$this->_db->table($this->_table). ' order by id desc limit '.$limit.' offset '.$offset);
		if($this->_allowmem && $cache && $data){
			$this->_memory->cmd('set',$mem_key,$data,60);
		}
		return $data;
		
	}

	public function fetch_last($cache=true)
	{
		$mem_key = __CLASS__.'::'.__FUNCTION__.'_';
		if($cache && $this->_allowmem){
			$data = $this->_memory->cmd('get',$mem_key);
			if(!empty($data)){
				return $data;
			}
		}
		$data = $this->_db->fetch_first('select * from '.$this->_db->table($this->_table). ' order by id desc limit  1 ');
		if($this->_allowmem){
			$this->_memory->cmd('set',$mem_key,$data,60);
		}
		return $data;
	}
}
