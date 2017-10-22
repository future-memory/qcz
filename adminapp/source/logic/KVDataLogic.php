<?php

class KVDataLogic extends Logic
{
	public function __construct()
	{
		$this->_dao = ObjectCreater::create('KVDataDao');
	}

	public function get_val($key, $decode=false)
	{
		$data = $this->_dao->fetch($key);
		if(!empty($data) && $data['expire']>0 && $data['expire']<TIMESTAMP){
			$this->_dao->delete($key);
			return null;
		}
		return isset($data['val']) ? ($decode ? json_decode($data['val'], true) : $data['val']) : null;
	}

	public function get_data_by_keys($keys)
	{
		$data = $this->_dao->fetch_all($keys);
		$dkey = array();
		foreach ($data as $key=>$item) {
			if($item['expire']>0 && $item['expire']<TIMESTAMP){
				$dkey[] = $item['key'];
				unset($data[$key]);
			}
		}
		!empty($dkey) && $this->_dao->delete($dkey);
		return $data;		
	}

	public function set_data($key, $val, $ttl=0)
	{
		$this->_dao->insert_or_update(array(
			'key' => $key,
			'val' => is_array($val) || is_object($val) ? json_encode($val) : $val,
			'expire' => $ttl ? (TIMESTAMP + $ttl) : 0
		));
	}


}
