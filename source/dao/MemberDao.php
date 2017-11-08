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

	//更新积分
	public function update_credit($id, $credit)
	{
		$credit = abs($credit);
		$setsql = $credit<0 ? "credit=if(credit>$credit,credit+'$credit',0)" : "credit=credit+{$credit}";

		$sql	= "UPDATE %t SET $setsql WHERE id=%d limit 1 ";
		$res	= $this->_db->query($sql);
		
		$this->clear_cache($id);
		
		return $res;
	}

	//删除用户缓存
	public function delete_member_cache($uid, $source)
	{
		$key = 'member_uid_'.$uid.'_'.$source;
		return $this->_memory->cmd('rm', $key);
	}

	public function set_member_token_info($uid, $data, $ttl=86400)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'token_'.$uid;
        $redis->hMset($key, $data);
        $redis->expire($key, $ttl);
        return true;	
	}
	
	public function get_member_token_info($uid, $field='token')
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'token_'.$uid;
        return $redis->hGetAll($key);
        //return $this->_redis->hGet($key, $field);   
	}

	//刷新token
	public function refresh_member_token_info($uid, $ttl=86400)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'token_'.$uid;
        $redis->expire($key, $ttl);
        return true;	
	}

	public function set_weapp_session_cache($code, $data, $ttl=86400)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'wa_session_'.md5($code);
        $redis->hMset($key, $data);
        $redis->expire($key, $ttl);
        return true;	
	}
	
	public function get_session_token_cache($code)
	{
		$redis = $this->_memory->get_memory_obj();
		$key   = 'wa_session_'.md5($code);
        return $redis->hGetAll($key);
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
