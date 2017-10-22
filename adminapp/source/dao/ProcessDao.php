<?php
class ProcessDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = 'common_process';
		$this->_pk    = 'processid';
		parent::__construct();
	}

	public function delete_process($name, $time) 
	{
		$name = addslashes($name);
		return $this->_db->delete('common_process', "processid='$name' OR expiry<".intval($time));
	}

	public function process_cmd_memory($cmd, $name, $ttl = 0) 
	{
		$ret = '';
		switch($cmd){
			case 'set' :
				if($ttl<=0) $ttl = 15;
				$ret = $this->_memory->cmd('set', 'process_lock_'.$name, time(), $ttl);
				break;
			case 'get' :
				$ret = $this->_memory->cmd('get', 'process_lock_'.$name);
				break;
			case 'rm' :
				$ret = $this->_memory->cmd('rm', 'process_lock_'.$name);
		}
		return $ret;
	}

	public function process_cmd_db($cmd, $name, $ttl = 0) 
	{
		$ret = '';
		switch($cmd){
			case 'set':
				$ret = $this->insert(array('processid' => $name, 'expiry' => time() + $ttl), FALSE, true);
				break;
			case 'get':
				$ret = $this->fetch($name);
				if(empty($ret) || $ret['expiry'] < time()) {
					$ret = false;
				} else {
					$ret = true;
				}
				break;
			case 'rm':
				$ret = $this->delete_process($name, time());
				break;
		}
		return $ret;
	}


}
