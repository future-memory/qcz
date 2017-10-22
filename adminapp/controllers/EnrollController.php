<?php
class EnrollController extends AdminController 
{
	private $perpage = 15;
	private $export_limit = 5000;
	private $genders = array(0=>'保密',1=>'男',2=>'女');

	public function __construct() {
		parent::__construct();
		$this->logic = ObjectCreater::create('EnrollLogic');
	}

	public function index() 
	{
		$page = (int)$this->get_param('page');
		$page = $page>1 ?  $page : 1;
		$start = ($page - 1) * $this->perpage;

		$current_enroll = $this->logic->get_current_enroll();
		$current_enroll_id = $current_enroll['id'];
		$current_enroll_name = $current_enroll['name'];
		$clist = $this->logic->get_list_by_page($this->perpage, $start, false);

		$count = $this->logic->get_count();

		$url   = '/index.php?mod=enroll';
		$pager = HelperPager::paging($count, $this->perpage, $page, $url, true);

		include(BASE_ROOT . '/template/enroll/index.php');	
	}

	public function delete_enroll() 
	{
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code' => 400,'message' => 'id 不存在'));

		$this->logic->delete_enroll_by_id($id);
		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => 'index.php?mod=enroll',
		));
	}

	public function update_enroll_id() 
	{
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code' => 400,'message' => 'id 不存在'));

		ObjectCreater::create('KVDataLogic')->set('enroll', $id);

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => 'index.php?mod=enroll',
		));
	}

	/**
	 * 添加、编辑发布会活动
	 * 	id     
		name  	活动名称
		begin   活动开始时间
		end   活动结束时间
		address  活动地点
		description  说明
		pic  海报图
		notes  注意事项
		busline  乘车路线
		num 招募人数
	 */
	public function add() 
	{
		$id        = (int)$this->get_param('id');
		$enroll    = $id ? $this->logic->get_enroll_by_id($id, false) :array();			
		$extcolumn = isset($enroll['extdata']) && $enroll['extdata'] ? @json_decode($enroll['extdata'], true) : array();

		include(BASE_ROOT . '/template/enroll/add.php');
	}

	public function post_enroll() {
		$enroll_id = (int)trim($this->get_param('enroll_id',''));

		$start = trim($this->get_param('start'));
		$begin = trim($this->get_param('begin'));
		$end = trim($this->get_param('end'));
		$name = htmlspecialchars(trim($this->get_param('name')));
		$num = trim($this->get_param('num'));
		$invitation_time = trim($this->get_param('invitation_time'));

		// $this->throw_error(!$start || !strtotime($start), array(
		// 	'code' => 400,
		// 	'message' => '输入正确的活动开始时间',
		// 	));
		$this->throw_error(!$begin || !strtotime($begin), array(
			'code' => 400,
			'message' => '输入正确的报名开始时间',
			));
		$this->throw_error(!$end || !strtotime($end), array(
			'code' => 400,
			'message' => '输入正确的报名结束时间',
			));
		$this->throw_error(!$name, array(
			'code' => 400,
			'message' => '请填写活动名称',
			));
		$this->throw_error(!$num || !preg_match('/^\s*[1-9][0-9]{0,9}\s*$/iu', $num), array(
			'code' => 400,
			'message' => '请输入正确的招募人数',
			));
		$this->throw_error(!$invitation_time || !strtotime($_POST['invitation_time']), array(
			'code' => 400,
			'message' => '输入正确的邀请函显示时间',
			));

		$enroll_pic = ltrim($this->get_param('pic', ''), '/');
		if ($enroll_pic) {
			$enroll['pic'] = $enroll_pic;
		}
		$invitation_pic = ltrim($this->get_param('invitation_pic', ''), '/');
		if ($invitation_pic) {
			$enroll['invitation_pic'] = $invitation_pic;
		}

		$enroll['start'] =  $start;
		$enroll['begin'] =  $begin;
		$enroll['end'] =  $end;
		$enroll['name'] = $name;
		$enroll['num'] = $num;
		$enroll['invitation_time'] = $invitation_time;
		$enroll['address'] =  htmlspecialchars($this->get_param('address', ''));
		$enroll['description'] =  $this->get_param('description', '');
		$enroll['sucess_tips'] =  $this->get_param('sucess_tips', '');
		$enroll['need_verify'] =  (int)$this->get_param('need_verify', 0);
		$enroll['reward_mq']   =  (int)$this->get_param('reward_mq', 0);

		
		//$enroll['notes'] =  $this->get_param('notes', '');
		//$enroll['busline'] =  $this->get_param('busline', '');

		$extdata = $this->get_param('extdata', '');
		$extdata = @json_decode($extdata, true);

		if (!$extdata || !is_array($extdata) || empty($extdata)) {
			$extdata = '';
		} else {
			$exist_keys = array();
			foreach ($extdata as $k=>$item) {
				$this->throw_error(preg_match('/^[_a-zA-Z]{1,}[_a-zA-Z0-9]{0,}$/', $item['key']) === 0, array(
					'code' => 400,
					'message' => '自定义字段需要以下划线或者字母开始，只能包含下划线数字和字母',
				));
				$this->throw_error(in_array($item['key'], $exist_keys), array(
					'code' => 400,
					'message' => '自定义字段key重复',
				));
				$extdata[$k]['range'] = is_array($item['range']) ? $item['range'] : @json_decode($item['range'], true);
				$extdata[$k]['range'] = is_array($extdata[$k]['range']) ? $extdata[$k]['range'] : array();

				$exist_keys[] = $item['key'];
			}
			$extdata = json_encode($extdata);
		}

		$this->throw_error(mb_strlen($extdata) >= 500, array(
			'code' => 400,
			'message' => '自定义字段数据总数量过多或者字段总长度过长',
		));

		$enroll['extdata'] = $extdata;

		if(empty($enroll_id)){
			$this->logic->post_enroll_data($enroll);
		}else{
			$this->logic->update_enroll_data($enroll_id, $enroll);
		}

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => 'index.php?mod=enroll',
		));
	}

	private function _get_pic_url($pic) {
		return HelperUtils::check_url($pic) ? $pic : FILE_DOMAIN . $pic;
	}


	/**
	 * 发布会报名列表列表 默认显示最新的 
	 */
	public function apply_list()
	{
		$page = (int)$this->get_param('page');
		$page = $page>1 ?  $page : 1;
		$start = ($page - 1) * $this->perpage;

		$status = $this->get_param('status');
		$id = $this->get_param('enroll_id');

		$all_status = array_keys($this->logic->status);
		if(!is_numeric($status) || !in_array($status, $all_status)){
			$status = 'all';
		}
		
		$enroll = $this->logic->fetch($id);

		$alist = $this->logic->fetch_apply_list_by_status($id, $status, $this->perpage, $start, false);

		$status_counts = $this->logic->get_status_counts($id);
		$status_options = $this->logic->get_status_options($status);

		$count = $status=='all' ? $status_counts['status_all'] : $status_counts['status_'.intval($status)];

		$url   = '/index.php?mod=enroll&action=apply_list&enroll_id='.$id.($status ? '&status='.$status : '');
		$pager = HelperPager::paging($count, $this->perpage, $page, $url, true);

		$status_array = $this->logic->status;

		include(BASE_ROOT . '/template/enroll/apply_list.php');
	}

	//导出报名列表
	public function export()
	{
		$status = $this->get_param('status');
		$id = $this->get_param('enroll_id');

		$all_status = array_keys($this->logic->status);
		if(!is_numeric($status) || !in_array($status, $all_status)){
			$status = 'all';
		}
		
		$enroll = $this->logic->fetch($id);

		$alist = $this->logic->fetch_apply_list_by_status($id, $status, 10000000000, 0, false);	

		$title = array('uid','姓名','手机号','申请状态','申请理由','取消理由','申请时间','发帖数','回帖数');

		$extcolumn = isset($enroll['extdata']) && $enroll['extdata'] ? @json_decode($enroll['extdata'], true) : array();

		$keys = array();
		foreach ($extcolumn as $column) {
			$title[] = $column['name'];
			$keys[] = $column['key'];
		}

		HelperUtils::export_csv_start('apply.csv', $title);

		foreach ($alist as $item) {
			$extdata = isset($item['extdata']) && $item['extdata'] ? json_decode($item['extdata'], true) : array();
			$tmp = '';
			foreach ($keys as $ek) {
				$tmp .= ',"'.(isset($extdata[$ek]) ? $extdata[$ek] : '').'"';
			}
			$status_text = $this->logic->get_status_text($item['status']);
			echo iconv('utf-8', 'gbk//IGNORE', $item['uid'].',"'.$item['realname'].'",'.$item['mobile'].','.$status_text.',"'.$item['apply_reason'].'","'.$item['cancel_reason'].'",'.($item['dateline'] ? date('Y-m-d H:i:s', $item['dateline']) : '').','.intval($item['threads']).','.intval($item['posts']).$tmp)."\n";
		}

		HelperUtils::export_csv_end();
	}

	/**
	 * 导入审核通过用户/邀请函图片
	 */
	public function import()
	{
		$enroll_id = (int)$this->get_param('enroll_id');
		$this->throw_error(!$enroll_id, array('code'=>400, 'message'=>'参数错误'));

		$excel = $_FILES['file']['tmp_name'];
		$this->throw_error(!file_exists($excel), array('code'=>400, 'message'=>'请选择需要导入的文件！'));

		$this->logic->import_apply($enroll_id, $excel);

		$this->render_json(array('code'=>200, 'message'=>'导入成功'));
	}

	/**
	 * 审核：1通过 -1 不通过
	 */
	public function verify()
	{
		$id = (int)$this->get_param('id');
		$status = (int)$this->get_param('status');

		$this->throw_error(!$id || !in_array($status,array(1,-1)), array('code'=>400,'message'=>'参数错误'));

		$apply = $this->logic->fetch_apply($id, false);
		$this->throw_error(!isset($apply['status']), array('code'=>400,'message'=>'报名信息不存在'));

		if(in_array($apply['status'], array(2,3,4))){
			$status_text = $this->logic->get_status_text($apply['status']);
			$this->render_json(array('code'=>406, 'message'=>'当前状态为"'.$status_text.'",不能审核'));
		}

		$this->logic->update_apply($id,array('status'=>$status));
		$this->render_json(array('code'=>200,'message'=>'操作成功','status_text'=>$this->logic->get_status_text($status)));
	}

	/**
	 * 删除用户报名数据
	 */
	public function delete_apply()
	{
		$id = (int)$this->get_param('id');
		$this->throw_error(!$id, array('code'=>400,'message'=>'参数错误'));

		$this->logic->delete_apply($id);

		$this->render_json(array('code'=>200,'message'=>'删除成功'));
	}

	public function apply_add()
	{
		$id = (int)$this->get_param('id', 0);
		$enroll_id = (int)$this->get_param('enroll_id', 0);

		$enroll = $this->logic->fetch($enroll_id, true);
		$extcolumn  = isset($enroll['extdata']) && $enroll['extdata'] ? @json_decode($enroll['extdata'], true) : array();

		$apply = $this->logic->fetch_apply($id, false);
		$extdata = isset($apply['extdata']) && $apply['extdata'] ? json_decode($apply['extdata'], true) : array();

		include(BASE_ROOT . '/template/enroll/apply_add.php');
	}

	public function apply_update()
	{
		$id = (int)$this->get_param('id');
		$uid = (int)$this->get_param('uid');
		$name   = htmlspecialchars(strip_tags(trim($this->get_param('realname'))));
		$mobile = (int)$this->get_param('mobile');
		$cancel_reason = strip_tags(trim($this->get_param('cancel_reason')));
		$apply_reason  = strip_tags(trim($this->get_param('apply_reason')));
		$enroll_id = (int)$this->get_param('enroll_id');

		$invitation_pic = strip_tags(ltrim($this->get_param('invitation_pic', ''), '/'));
		$invitation_picu = strip_tags($this->get_param('invitation_picu', ''));
		$invitation_picu = $invitation_picu && HelperUtils::check_url($invitation_picu) ? $invitation_picu : null;
		$invitation_pic = $invitation_pic ? $invitation_pic : $invitation_picu;

		$this->throw_error(!$enroll_id, array('code'=>400, 'message'=>'参数错误'));
		$this->throw_error(!$name, array('code'=>400, 'message'=>'请正确输入姓名'));
		$this->throw_error(strlen($apply_reason)>500, array('code'=>400, 'message'=>'申请理由不超过500个字（空格算一个）'));
		$this->throw_error(strlen($cancel_reason)>500, array('code'=>400, 'message'=>'取消理由不超过500个字（空格算一个）'));

		$info = $this->logic->fetch($enroll_id, true);
		$this->throw_error(!$info, array('code'=>404, 'message'=>'活动不存在'));

		$extdata = array();
		if($info['extdata']) {
			$extdata_array = @json_decode($info['extdata'], true);
			foreach ($extdata_array as $column) {
				$v_key = $column['key'];
				$extdata[$v_key] = strip_tags(trim($this->get_param($v_key)));
				$this->throw_error(!$extdata[$v_key], array('code'=>400, 'message'=>'请填写完整再提交'));
			}
		}
		$extdata = !empty($extdata) ? json_encode($extdata) : '';

		$data = array(
			'uid' => $uid,
			'mobile' => $mobile,
			'extdata' => $extdata,
			'dateline' => TIMESTAMP,
			'realname' => $name,
			'enroll_id' => $enroll_id,
			'apply_reason' => $apply_reason,
			'cancel_reason' => $cancel_reason,
			'invitation_pic' => $invitation_pic		
		);

		if($id){
			$data['id'] = $id;
		}

		$this->logic->insert_update_apply($data);

		$this->render_json(array('code'=>200,'message'=>'操作成功'));
	}



}