<?php


class EnrollApplyDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'enroll_apply';
		$this->_pk    = 'id';
		parent::__construct();
	}

	/**
	 * 根据主键获取报名用户信息
	 * @param int $uid
	 * @param boolean $cache
	 * @return Ambigous <NULL, string, boolean>|Ambigous <multitype:, unknown>
	 */
	public function fetch_by_cid_uid($enroll_id,$uid,$cache=true)
	{
		$data = array();
		if(preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id) && preg_match('/^[1-9][0-9]{0,9}$/iu', $uid)){
			$mem_key = 'enroll_apply::'.__FUNCTION__.'_'.$enroll_id.'_'.$uid;
			if($cache && $this->_allowmem){
				$data = $this->_memory->cmd('get',$mem_key);
				if(!empty($data)){
					return $data;
				}
			}
			$data = $this->_db->fetch_first('select * from '.$this->_db->table($this->_table). '  where uid='.$uid .' and enroll_id='.$enroll_id);
			if($this->_allowmem){
				$this->_memory->cmd('set',$mem_key,$data,10);
			}
		}
		return $data;
	}

	/**
	 * 获取报名用户列表  只有后台会用到
	 * @param number $limit
	 * @param number $offset
	 * @param string $cache
	 * @return Ambigous <NULL, string, boolean>|multitype:unknown
	 */
	public function fetch_all_by_page($enroll_id,$limit=20,$offset=0,$cache=true)
	{
		if(!preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id)){
			return array();
		}
		$limit = intval($limit);
		$offset = intval($offset);
		$limit  = $limit > 0 ? $limit : 20;
		$offset = $offset >= 0 ? $offset : 0; 
		$mem_key = 'enroll_apply_'.__FUNCTION__.'_'.$enroll_id.'_'.$limit.'_'.$offset;
		if($this->_allowmem && $cache){
			$data = $this->_memory->cmd('get',$mem_key);
			if($data){
				return $data;
			}
		}
		$data = $this->_db->fetch_all('select * from '.$this->_db->table($this->_table). ' where enroll_id='.$enroll_id.' order by uid desc limit '.$limit.' offset '.$offset);
		if($this->_allowmem &&cache && $data){
			$this->_memory->cmd('set',$mem_key,$data,60);
		}
		return $data;
		
	}

	/**
	 * 获取各状态的报名总数
	 * @param unknown $enroll_id
	 * @param unknown $status
	 * @return number
	 */
	public function fetch_status_count($enroll_id,$status)
	{
		if(!is_numeric($status) ||  !in_array($status, array(0,-1,1,2,3,4))){
			$status = null;
		}
		$mem_key =  "enroll_apply_count_".$enroll_id.'_'.$status;
		$count = 0;
		if(preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id)){
			if($this->_allowmem){
				$count = $this->_memory->cmd('get',$mem_key);
				if($count !== false ){
					return $count;
				}
			}
			$status_condition = '';
			if(is_numeric($status)){
				$status_condition = ' and status='.$status;
			}
			$count_arr = $this->_db->fetch_first('select count(1) as count from '.$this->_db->table($this->_table).' where enroll_id='.$enroll_id.$status_condition);
			$count = isset($count_arr['count']) ? $count_arr['count'] : 0;
			$this->_memory->cmd('set',$mem_key,$count,5);
		}
		return $count;
	}

	/**
	 * 获取报名用户列表  只有后台会用到
	 * @param number $limit
	 * @param number $offset
	 * @param string $cache
	 * @return Ambigous <NULL, string, boolean>|multitype:unknown
	 */
	public function fetch_all_by_status($enroll_id,$status,$limit=20,$offset=0,$cache=true)
	{
		if(!preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id)){
			return array();
		}
		$limit = intval($limit);
		$offset = intval($offset);
		$limit  = $limit > 0 ? $limit : 20;
		$offset = $offset >= 0 ? $offset : 0;

		$status_condition = '';
		if(is_numeric($status) && in_array($status, array(0,-1,1,2,3,4))){
			$status_condition = ' and status='.$status;
		}
		$data = $this->_db->fetch_all('select *,m.threads,m.posts from '.$this->_db->table($this->_table). ' a left join pre_common_member_count m on m.uid=a.uid where enroll_id='.$enroll_id.$status_condition.' order by id desc limit '.$limit.' offset '.$offset);

		return $data;
	}

	/**
	 *根据id 取报名数据
	 */
	public function fetch_by_id($id, $cache=false)
	{
		$data = array();
		if(preg_match('/^[1-9][0-9]{0,9}$/iu', $id)){
			$mem_key = 'enroll_apply::'.__FUNCTION__.'_'.$id;
			if($cache && $this->_allowmem){
				$data = $this->_memory->cmd('get',$mem_key);
				if(!empty($data)){
					return $data;
				}
			}
			$data = $this->_db->fetch_first('select * from '.$this->_db->table($this->_table). '  where id='.$id );
			if($this->_allowmem){
				$this->_memory->cmd('set',$mem_key,$data,60);
			}
		}
		return $data;
	}

	/**
	 * 批量设置不通过
	 * @param unknown $enroll_id
	 */
	public function multi_unthrough($enroll_id)
	{
		if(preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id)){
			$sql = 'update '.$this->_db->table($this->_table) .' set status=-1 where status=0 and enroll_id='.$enroll_id;
			$this->_db->query($sql);
			return true;
		}
		return false;
	}

	/**
	 * 批量审核通过
	 * @param unknown $uid
	 * @param unknown $enroll_id
	 */
	public function multi_through($uids,$enroll_id)
	{
		if(preg_match('/^[1-9][0-9]{0,9}$/iu', $enroll_id)){
			$count = 0;
			foreach ((array)$uids as $uid){
				if(preg_match('/^[1-9][0-9]{0,9}$/iu', $uid)){
					if($count>0 && $count%300 == 0){
						sleep(1);
					}
					$sql = 'update '.$this->_db->table($this->_table) .' set status=1 where status in (0,-1) and uid='.$uid.' and enroll_id='.$enroll_id.'  limit 1';
					$this->_db->query($sql);
					$count++;
				}
			}
		}
	}

	//删除个人报名缓存
	public function del_apply_cache($id, $uid)
	{
		if($this->_allowmem){
			$mem_key = 'enroll_apply::fetch_by_cid_uid_'.$id.'_'. $uid;
			memory('rm',$mem_key);
		}	
	}

}
