<?php
class DbtoolDao extends BaseDao
{
	public function __construct() 
	{
		$this->_table = '';
		$this->_pk    = '';
		$this->_pre_cache_key = '';
		parent::__construct();
	}

	public function dbversion() {
		return $this->_db->result_first("SELECT VERSION()");
	}

	public function dbsize() {
		$dbsize = 0;
		$query = $this->_db->query("SHOW TABLE STATUS LIKE 'pre_%'", 'SILENT');
		while($table = $this->_db->fetch($query)) {
			$dbsize += $table['Data_length'] + $table['Index_length'];
		}
		return $dbsize;
	}

	public function gettablestatus($tablename, $formatsize = true) {
		$status = $this->_db->fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");

		if($formatsize) {
			$status['Data_length'] = sizecount($status['Data_length']);
			$status['Index_length'] = sizecount($status['Index_length']);
		}

		return $status;
	}

}
