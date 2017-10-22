<?php
class EnrollLogic extends Logic 
{
	public $status = array(-1=>'不通过',0=>'未审核',1=>'通过',2=>'确认参与',3=>'确认不参与',4=>'取消');

	public function __construct() {
		$this->_dao = ObjectCreater::create('EnrollDao');
	}

	public function get_current_enroll() 
	{
		$current_id = (int)ObjectCreater::create('KVDataLogic')->get('enroll');
		$current_enroll = array();
		if($current_id){
			$current_enroll = $this->_dao->fetch_by_id($current_id);
		}
		if(empty($current_enroll)){
			$current_enroll = $this->_dao->fetch_last();
		}
		return $current_enroll;
	}

	public function get_list_by_page($limit = 20, $offset = 0, $cache = true) {
		return $this->_dao->fetch_all_by_page($limit, $offset, $cache);
	}

	public function get_count()
	{
		return $this->_dao->count();
	}

	public function get_enroll_by_id($ids, $cache = false) {
		return $this->_dao->fetch_by_id($ids, $cache);
	}

	public function delete_enroll_by_id($ids) {
		return $this->_dao->delete($ids);
	}

	public function post_enroll_data($data) {
		return $this->_dao->insert($data);
	}

	public function update_enroll_data($id, $data) {
		return $this->_dao->update($id, $data);
	}

	public function fetch_apply_list_by_status($id, $status, $perpage, $start , $cache=false)
	{
		return ObjectCreater::create('EnrollApplyDao')->fetch_all_by_status($id, $status, $perpage, $start, $cache);
	}

	public function fetch_apply($id, $cache)
	{
		return ObjectCreater::create('EnrollApplyDao')->fetch_by_id($id, false);
	}

	public function update_apply($id, $data)
	{
		return ObjectCreater::create('EnrollApplyDao')->update($id, $data);
	}

	public function insert_update_apply($data)
	{
		return ObjectCreater::create('EnrollApplyDao')->insert_or_update($data);
	}

	public function delete_apply($id)
	{
		return ObjectCreater::create('EnrollApplyDao')->delete($id);
	}

	public function get_status_counts($enroll_id)
	{
		$counts = array();
		if(is_numeric($enroll_id)){
			$apply_dao = ObjectCreater::create('EnrollApplyDao');
			$counts['status_-1'] = $apply_dao->fetch_status_count($enroll_id,-1);
			$counts['status_0'] = $apply_dao->fetch_status_count($enroll_id,0);
			$counts['status_1'] = $apply_dao->fetch_status_count($enroll_id,1);
			$counts['status_2'] = $apply_dao->fetch_status_count($enroll_id,2);
			$counts['status_3'] = $apply_dao->fetch_status_count($enroll_id,3);
			$counts['status_4'] = $apply_dao->fetch_status_count($enroll_id,4);
			$counts['status_all'] = $counts['status_-1'] + $counts['status_0']+ $counts['status_1']+ $counts['status_2']+ $counts['status_3']+ $counts['status_4'];
		}
		return $counts;
	}

	public function get_status_options($selected=null)
	{
		$status_options = '';
		$set_selected = false;
		foreach ($this->status as $k=>$v){
			if(is_numeric($selected) && $selected == $k){
				$status_options .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
				$set_selected = true;
			}else{
				$status_options .= '<option value="'.$k.'">'.$v.'</option>';
			}
		}
		if($set_selected){
			$status_options = '<option value="999" selected="selected">全部</option>'.$status_options;
		}else{
			$status_options = '<option value="999">全部</option>'.$status_options;
		}
		return $status_options;
	}

	/**
	 * 获取申请状态 0未审核 -1不通过 1通过 2确认参与 3确认无法参与 4取消
	 * @param unknown $status
	 * @return string
	 */
	public  function get_status_text($status)
	{
		$status_text = isset($this->status[$status]) ? $this->status[$status] : '未审核';
		return $status_text;
	}

	//导入
	public function import_apply($enroll_id, $excel)
	{
		$fp   = fopen($excel,'r'); 
		$uids = $unexist = $unable = array();
		$act  = false;
		while($value = fgetcsv($fp)) {
			$uid = trim($value[0]); //echo iconv('gb2312', 'utf-8', $item);
			$url = trim($value[1]);

			if(!preg_match('/^[1-9][0-9]{0,9}$/iu', $uid)){
				continue;
			}

			$act = true;

			$apply = ObjectCreater::create('EnrollApplyDao')->fetch_by_cid_uid($enroll_id, $uid, false);
			$update_apply = array();
			if(empty($url) && isset($apply['status'])  && in_array($apply['status'], array(0,-1))){
				$uids[] = $uid;
			}else{
				if(empty($apply)){
					$unexist[] = $uid;
					continue;
				}				
				if(!preg_match('/^(http|https)\:\/\/.{5,200}$/iu', $url)){
					$unable[] = $uid;
					continue;
				}
				$path = HelperQRCode::qrcode($url);
				if(empty($path)){
					$unable[] = $uid;
					continue;
				}
				if(!in_array($apply['status'], array(1,2))){
					$unable[] = $uid;
					continue;
				}
				$update_apply['invitation_pic'] = $path;
			}
			$apply_id = $apply['id'];
			ObjectCreater::create('EnrollApplyDao')->update($apply_id, $update_apply);
		}

		fclose($fp);

		if($uids){
			ObjectCreater::create('EnrollApplyDao')->multi_through($uids,$enroll_id);
			ObjectCreater::create('EnrollApplyDao')->multi_unthrough($enroll_id);
			$act = true;
		}

		self::throw_exception(!$act, array('code'=>400,'message'=>'文件数据格式错误'));

		if($act && (!empty($unable) || !empty($unexist))){
			$failed_uids = implode(',', $unable);
			HelperLog::writelog('enroll', 'failed_uids:'.$failed_uids.' unexist: '.implode(',', $unexist));
			$message = '操作结束'.($failed_uids ? '，失败用户：'.$failed_uids : '').($unexist ? '，用户不存在：'.implode(',', $unexist) : '');
			self::throw_exception(true, array('code'=>201,'message'=>$message));
		}
	}

}