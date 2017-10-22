<?php

class MemberDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'member';
		$this->_pk    = 'id';
		
		parent::__construct();
	}

	public function get_member_by_uid($uid, $source) 
	{
		$key  = 'member_uid_'.$uid.'_'.$source;
		$data = $this->_memory->cmd('get', $key);
		if($data){
			return $data;
		}		
		$data = $this->_db->fetch_first('SELECT id,uid,username,avatar FROM %t WHERE uid=%s AND `source`=%d', array($this->_table, $uid, $source));

		if($data){
			$this->_memory->cmd('set', $key, $data, 600);
		}

		return $data;
	}

	public function delete_member_cache($uid, $source)
	{
		$key = 'member_uid_'.$uid.'_'.$source;
		return $this->_memory->cmd('rm', $key);
	}

	//获取微信 js接口 token cache
	public function get_js_token_cache($appid)
	{
		$key = 'wx_jx_token_'.$appid;
		return $this->_memory->cmd('get', $key);		
	}

	//保存 微信 js接口 token cache
	public function set_js_token_cache($appid, $access_token, $expires_in)
	{
		$key = 'wx_jx_token_'.$appid;
		$ttl = $expires_in - 30;
		return $this->_memory->cmd('set', $key, $access_token, $ttl);	
	}

	//获取微信 js接口 token cache
	public function get_js_ticket_cache($appid)
	{
		$key = 'wx_jx_ticket_'.$appid;
		return $this->_memory->cmd('get', $key);		
	}

	//保存 微信 js接口 token cache
	public function set_js_ticket_cache($appid, $ticket, $expires_in)
	{
		$key = 'wx_jx_ticket_'.$appid;
		$ttl = $expires_in - 30;
		return $this->_memory->cmd('set', $key, $ticket, $ttl);	
	}

}
