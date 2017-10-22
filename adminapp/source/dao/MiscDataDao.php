<?php
class MiscDataDao extends BaseDao 
{
	public function __construct() {
		$this->_table = 'misc_data';
		$this->_pk    = 'id';
		parent::__construct();		
	}

	public function fetch_list_by_key($key, $start, $limit) 
	{
		$sql_key = '';
		if($key){
			$sql_key = 'WHERE `key`='.$this->_db->quote($key);
		}
        $data = $this->_db->fetch_all('SELECT * FROM %t '.$sql_key.' ORDER BY `order` DESC, id DESC LIMIT %d, %d', array($this->_table, $start, $limit));

        return $data;		
	}

	public function del_data_list_cache($key)
	{
		$mem_key = array('misc_data_'.$key, 'misc_data_'.$key.'_0', 'misc_subject_'.$key);
		return $this->_memory->cmd('rm', $mem_key);
	}


}