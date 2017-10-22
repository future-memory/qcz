<?php
class MiscSubjectDao extends BaseDao 
{
	public function __construct() {
		$this->_table = 'misc_subject';
		$this->_pk    = 'key';
		parent::__construct();
	}
}