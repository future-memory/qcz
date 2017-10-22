<?php
class ShopUserAddressDao extends BaseDao 
{
	public function __construct() 
	{
		$this->_table = 'shop_user_address';
		$this->_pk    = 'uid';

		$this->_cache_ttl = 60;

		parent::__construct();

		$this->_allowmem = true;
	}

	public function get_address($uid)
	{
		$uid = floatval($uid);
		if(!$uid){
			return false;
		}
		$sql    = 'SELECT * FROM %t WHERE uid=%d ';
		$result = $this->_db->fetch_first($sql, array($this->_table, $uid));

        return $result;		
	}	

	
	
}