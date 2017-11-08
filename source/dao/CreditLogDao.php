<?php
class CreditLogDao extends BaseDao
{
	const SPLIT_TABLE = 10;

    public function __construct() 
    {
        $this->_table = 'credit_log';
        $this->_pk    = 'uid';
        parent::__construct();
    }

	//管理使用
	public function fetch_by_operation_relatedid($operation, $relatedid) 
	{
		if(self::SPLIT_TABLE>1){	//开启分表
			return $this->fetch_by_operation_relatedid_merge($operation, $relatedid);
		}

		$relatedid = HelperUtils::dintval($relatedid, true);
		$parameter = array($this->_table, $operation, $relatedid);
		$wherearr = array();
		$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
		return $this->_db->fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr), $parameter);
	}

	//管理使用
	public function fetch_by_operation_relatedid_merge($operation, $relatedid)
	{
		// 获得表前缀
		$wherearr   = array();
		$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
		$relatedid  = HelperUtils::dintval($relatedid, true);
		$parameter  = array($operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid, $operation, $relatedid);

		$where_str = 'WHERE '.implode(' AND ', $wherearr);

		$sql = "SELECT * FROM pre_credit_log_0 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_1 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_2 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_3 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_4 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_5 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_6 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_7 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_8 {$where_str} UNION ALL 
				SELECT * FROM pre_credit_log_9 {$where_str} ";

		return $this->_db->fetch_all($sql, $parameter);
	}

	//管理后台使用
	public function fetch_all_by_operation($operation, $start = 0, $limit = 0) 
	{
		if(self::SPLIT_TABLE>1){	//开启分表
			return $this->fetch_all_by_operation_merge($operation, $start, $limit);
		}

		return $this->_db->fetch_all('SELECT * FROM %t WHERE operation=%s ORDER BY dateline DESC '.$this->_db->limit($start, $limit), array($this->_table, $operation));
	}

	//管理后台使用
	public function fetch_all_by_operation_merge($operation, $start=0, $limit=0)
	{
		// 获得表前缀
		$where_str = 'WHERE '.implode(' AND ', $wherearr);

		$sql = "SELECT * FROM pre_credit_log_0 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_1 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_2 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_3 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_4 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_5 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_6 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_7 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_8 WHERE operation=%s UNION ALL 
				SELECT * FROM pre_credit_log_9 WHERE operation=%s 
			ORDER BY dateline DESC ".$this->_db->limit($start, $limit);

		return $this->_db->fetch_all($sql, array($operation,$operation,$operation,$operation,$operation,$operation,$operation,$operation,$operation,$operation));
	}

	public function fetch_all_by_uid_operation_relatedid($uid, $operation, $relatedid) 
	{
		if(self::SPLIT_TABLE>1 && !$uid){	//开启分表 并且uid为空
			return $this->fetch_by_operation_relatedid_merge($operation, $relatedid);
		}

		$mem_key = 'credit_log::fetch_all_by_uid_operation_relatedid_'.$operation.'_'.$relatedid;
		if($this->_allowmem){
			$data = $this->_memory->cmd('get',$mem_key);
			if($data!==false){
				return $data;
			}
		}

		$parameter = array($this->get_table_name($uid));
		$wherearr  = array();
		if($uid) {
			$uid = HelperUtils::dintval($uid, true);
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		$relatedid   = HelperUtils::dintval($relatedid, true);
		$wherearr[]  = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[]  = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
		$parameter[] = $operation;
		$parameter[] = $relatedid;

		$data = $this->_db->fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr).' ORDER BY dateline', $parameter);

		if($this->_allowmem) {
			$this->_memory->cmd('set', $mem_key , $data , 10);
		}

		return $data;

	}

	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) 
	{
		$mem_key = 'credit_log::fetch_all_by_uid_'.$uid.'_'.$start.'_'.$limit;
		if($this->_allowmem){
			$data = $this->_memory->cmd('get', $mem_key);
			if($data!==false){
				return $data;
			}
		}		
		$data = $this->_db->fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.$this->_db->limit($start, $limit), array($this->get_table_name($uid), $uid));
		if($this->_allowmem) {
			$this->_memory->cmd('set', $mem_key , $data , 10);
		}

		return $data;
	}


	public function delete_by_operation_relatedid($operation, $relatedid) 
	{
		if(self::SPLIT_TABLE>1){	//开启分表
			return $this->delete_by_operation_relatedid_merge($operation, $relatedid);
		}

		$relatedid = HelperUtils::dintval($relatedid, true);
		if($operation && $relatedid) {
			return $this->_db->delete($this->_table, $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
		}
		return 0;
	}

	public function delete_by_operation_relatedid_merge($operation, $relatedid) 
	{
		$relatedid = HelperUtils::dintval($relatedid, true);
		if($operation && $relatedid) {
			$this->_db->delete($this->_table.'_0', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_1', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_2', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_3', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_4', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_5', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_6', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_7', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_8', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			$this->_db->delete($this->_table.'_9', $this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
			return true;
		}
		return 0;
	}


	public function delete_by_uid_operation_relatedid($uid, $operation, $relatedid) 
	{
		$relatedid = HelperUtils::dintval($relatedid, true);
		$uid = HelperUtils::dintval($uid, true);
		if($relatedid && $uid && $operation) {
			return $this->_db->delete($this->get_table_name($uid), $this->_db->field('uid', $uid).' AND '.$this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
		}
		return 0;
	}

	public function update_by_uid_operation_relatedid($uid, $operation, $relatedid, $data) 
	{
		$relatedid = HelperUtils::dintval($relatedid, true);
		$uid = HelperUtils::dintval($uid, true);
		if(!empty($data) && is_array($data) && $relatedid && $uid && $operation) {
			return $this->_db->update($this->get_table_name($uid), $data, $this->_db->field('uid', $uid).' AND '.$this->_db->field('operation', $operation).' AND '.$this->_db->field('relatedid', $relatedid));
		}
		return 0;
	}

	public function count_by_uid_operation_relatedid($uid, $operation, $relatedid) 
	{
		$relatedid = HelperUtils::dintval($relatedid, true);
		$uid = HelperUtils::dintval($uid, true);
		if($relatedid && $uid && $operation) {
			$wherearr = array();
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			return $this->_db->result_first('SELECT COUNT(*) FROM %t WHERE '.implode(' AND ', $wherearr), array($this->get_table_name($uid), $uid, $operation, $relatedid));
		}
		return 0;
	}

	public function count_by_uid($uid) 
	{
		$mem_key = 'credit_log::count_by_uid_'.$uid;
		if($this->_allowmem){
			$data = $this->_memory->cmd('get',$mem_key);
			if($data!==false){
				return $data;
			}
		}

		$data = $this->_db->result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->get_table_name($uid), $uid));
		
		if($this->_allowmem) {
			$this->_memory->cmd('set', $mem_key , $data , 10);
		}

		return $data;
	}


	//用于在操作中判断 不需要缓存
	public function count_credit_by_uid_operation_relatedid($uid, $operation, $relatedid, $creditid) 
	{
		$creditid = intval($creditid);
		if($creditid) {
			$relatedid = HelperUtils::dintval($relatedid, true);
			$uid = HelperUtils::dintval($uid, true);
			$wherearr = array();
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			return $this->_db->result_first('SELECT SUM(extcredits%d) AS credit FROM %t WHERE '.implode(' AND ', $wherearr), array($creditid, $this->get_table_name($uid), $uid, $operation, $relatedid));
		}
		return 0;
	}

	public function count_by_search($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) 
	{
		$condition = $this->make_query_condition($uid, $optype, $begintime, $endtime, $exttype, $income, $extcredits, $relatedid);
		
		$mem_key = 'credit_log::count_by_search_'.md5($condition[0].'_'.implode('_', $condition[1]));
		if($this->_allowmem){
			$data = $this->_memory->cmd('get', $mem_key);
			if($data!==false){
				return $data;
			}
		}

		$data = $this->_db->result_first('SELECT COUNT(*) FROM %t '.$condition[0], $condition[1]);

		return $data;
	}

	private function make_query_condition($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) 
	{
		$wherearr = array();

		$parameter = array($this->get_table_name($uid));
		if($uid) {
			$uid = HelperUtils::dintval($uid, true);
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		if($optype) {
			$wherearr[] = is_array($optype) && $optype ? 'operation IN(%n)' : 'operation=%s';
			$parameter[] = $optype;
		}
		if($relatedid) {
			$relatedid = HelperUtils::dintval($relatedid, true);
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			$parameter[] = $relatedid;
		}
		if($begintime) {
			$wherearr[] = 'dateline>%d';
			$parameter[] = $begintime;
		}
		if($endtime) {
			$wherearr[] = 'dateline<%d';
			$parameter[] = $endtime;
		}
		if($exttype && $extcredits[$exttype]) {
			$wherearr[] = "extcredits{$exttype}!=0";
		}
		if($income && $extcredits) {
			$incomestr = $income < 0 ? '<' : '>';
			$incomearr = array();
			foreach(array_keys($extcredits) as $id) {
				$incomearr[] = 'extcredits'.$id.$incomestr.'0';
			}
			if($incomearr) {
				$wherearr[] = '('.implode(' OR ', $incomearr).')';
			}
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}

	//分表名
	public function get_table_name($uid)
	{
		$tbl_id	= $uid % self::SPLIT_TABLE;
		return self::SPLIT_TABLE > 1 ? $this->_table.'_'.$tbl_id : $this->_table;
	}

	// 转成可以支持  InnoDB 类型的数据库
	public function insert_log($data, $return_insert_id = false) 
	{
		if(!isset($data['uid'])){
			return false;
		}

		if(!isset($data['current'])){
			$user = ObjectCreater::create('MemberDao')->fetch($data['uid'], true);
			$data['current'] = $user['credit'];
		}

		$mem_key = array('credit_log_uid_'.$data['uid'].'_0');
		$this->_memory->cmd('rm', $mem_key);		

		return $this->_db->insert($this->get_table_name($data['uid']), $data, $return_insert_id);
	}


}