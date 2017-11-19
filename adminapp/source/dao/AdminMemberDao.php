<?php

class AdminMemberDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'admin_member';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	//
	public function get_member_list($domain, $start, $limit)
	{
		$where = $domain && $domain!='www' ? 'WHERE domain=%s' : '';
		$param = $domain && $domain!='www' ? array($this->_table, $domain, $start, $limit) : array($this->_table, $start, $limit);
		return $this->_db->fetch_all('SELECT * FROM %t '.$where.' ORDER BY uid DESC LIMIT %d,%d', $param);
	}

	public function update_by_role($role_id, $data) 
	{
		if(!is_array($data)) {
			return null;
		}
		return $this->_db->update($this->_table, $data, $this->_db->field('role_id', $role_id));
	}

	//
	public function get_member_by_username($username)
	{
		return $this->_db->fetch_first('SELECT * FROM %t WHERE username=%s', array($this->_table, $username));
	}

	//删除角色下的所有用户
	public function delete_perms_by_role($role_id)
	{
		if(!$role_id){
			return false;
		}

		return $this->_db->query('DELETE FROM %t WHERE role_id=%d', array($this->_table, $role_id));
	}

	
	//保存admin token
	public function set_admin_token($uid, $data, $ttl=1800)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'admin_token_'.$uid;
        $redis->hMset($key, $data);
        $redis->expire($key, $ttl);
        return true;	
	}

	public function get_all_admin_token() {
		$redis = $this->_memory->get_memory_obj();
		return $redis->keys('admin_token_*');
	}

	//
	public function get_admin_token($uid, $field='token')
	{
		$redis = $this->_memory->get_memory_obj();
		$key   =  'admin_token_'.$uid;
        return $redis->hGetAll($key);
	}	

	//刷新token
	public function refresh_admin_token($uid, $ttl=1800)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'admin_token_'.$uid;
        $redis->expire($key, $ttl);
        return true;	
	}
	

}

