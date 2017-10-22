<?php
/**
 * 资源独占锁
 * @property Memory $_memory
 * @property Redis  $redis
 */
class LockLogic extends Logic {
	
	private $_memory;
	
	private $redis;
	
	public function __construct(){
		$this->_memory =  Nice::app()->getComponent('Memory');
		$this->set_redis();
	}
	/**
	 * 加锁
	 */
	public function lock($lock_key,$lock_ex=2){
		$lock_ex = intval($lock_ex);
		if(empty($lock_key) || $lock_ex <= 0){
			return false;
		}
		$lock = $this->redis->setNX($lock_key , time());//设当前时间
		if($lock){
			$this->redis->expire($lock_key, $lock_ex); //如果没执行完 2s锁失效
		}
		if(!$lock){//如果获取锁失败 检查时间
			$time = $this->redis->get($lock_key);
			if(time() - $time  >= $lock_ex){//添加时间戳判断为了避免expire执行失败导致死锁
				$this->redis->rm($lock_key);
			}
			$lock =  $this->redis->setNX($lock_key , time());
			if($lock){
				$this->redis->expire($lock_key, $lock_ex); //如果没执行完 2s锁失效
			}
		}
		return $lock;
	}
	/**
	 * 解锁
	 */
	public function unlock($lock_key){
		$this->redis->rm($lock_key);
	}
	/**
	 *  获取redis实例
	 */
	private  function set_redis(){
		$this->redis = $this->_memory->get_memory_obj();
	}
}