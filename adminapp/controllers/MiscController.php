<?php
class MiscController extends AdminController 
{
	private $_subject_logic;
	/*
	 * 允许灰度测试的块
	 */
	private $gray_keys = array('tab_config','hot_banner', 'activity_page', 'new_activity_page');
	public $page_paths = array(
		'detail'   => '帖子详情页', 
		'native'   => '原生页', 
		'picture'  => '魅拍GO详情页',
		'phototag' => '魅拍GO标签页', 
		'webpage'  => '普通H5页'
	);
	public $native_pages = array(
		'topic'                  => '话题', 
		'recommend'              => '编辑推荐',
	);

	public function __construct() {
		parent::__construct();
		$this->_subject_logic = ObjectCreater::create('MiscLogic');
	}

	public function index() {
		$this->list_subject();
	}

	public function list_subject() {
		$member = ObjectCreater::create('AdminLogic')->get_current_member();
		$is_founder = ObjectCreater::create('AdminLogic')->check_founder($member);

		$page = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 20);
		$start = $page > 1 ? ($page - 1) * $limit : 0;

		$count = $this->_subject_logic->get_subject_count();
		$list = $this->_subject_logic->get_subject_list($start, $limit, 'desc');

		$pager = HelperPager::paging($count, $limit, $page, 'index.php?mod=misc', true);
		include(BASE_ROOT . '/template/misc/index.php');		
	}

	public function edit_subject() {
		$key = HelperUtils::xss_bug((string)$this->get_param('key', ''));
		$info = $key ?  $this->_subject_logic->get_subject_by_key($key, true) : array();
		$extdata_array = array(
			'keys' => [],
			'k_v' => [],
			);
		if (isset($info['extdata']) && $info['extdata']) {
			$extdata_array = json_decode($info['extdata'], true);
		}
		include(BASE_ROOT . '/template/misc/editsubject.php');
	}

	public function post_subject() {
		$key        = $this->get_param('subject_key');
		$name       = $this->get_param('name');
		$show_pic   = (int)$this->get_param('show_pic');
		$expire     = (int)$this->get_param('expire');
		$pic_width  = (int)$this->get_param('pic_width');
		$pic_height = (int)$this->get_param('pic_height');
		$pic_size   = (int)$this->get_param('pic_size');
		$show_count = (int)$this->get_param('show_count');
		$start_time = (int)$this->get_param('start_time');
		$random     = (int)$this->get_param('random');

		$extdata	= $this->get_param('extdata', '');
		$extdata_array = @json_decode($extdata, true);
		if (!$extdata_array || !is_array($extdata_array) || empty($extdata_array)) {
			$extdata = '';
		} else {
			$extdata_format = array(
				'keys' => array(),
				'k_v' => array(),
				);
			foreach ($extdata_array as $k_v) {
				foreach ($k_v as $_k => $_v) {
					$this->throw_error(preg_match('/^[_a-zA-Z]{1,}[_a-zA-Z0-9]{0,}$/', $_k) === 0, array(
						'code' => 400,
						'message' => '自定义字段需要以下划线或者字母开始，只能包含下划线数字和字母',
						));
					$this->throw_error(in_array($_k, $extdata_format['keys']), array(
						'code' => 400,
						'message' => '自定义字段key重复',
						));
					$extdata_format['keys'][] = $_k;
					$extdata_format['k_v'][$_k] = $_v;
				}
			}
		}
		if (!empty($extdata_format['keys'])) {
			$extdata = json_encode($extdata_format);
		}
		$this->throw_error(mb_strlen($extdata) >= 200, array(
			'code' => 400,
			'message' => '自定义字段数据总数量过多或者字段总长度过长',
			));		

		$this->throw_error(!$name, array(
			'code' => 400,
			'message' => '参数 name 错误',
			));
		$this->throw_error(!$show_count, array(
			'code' => 400,
			'message' => '参数 show_count 错误',
			));
		$this->throw_error($show_pic && (!$pic_height || !$pic_width || !$pic_size), array(
			'code' => 400,
			'message' => '参数 show_pic 错误',
			));
		$this->throw_error(!$key, array(
			'code' => 400,
			'message' => '参数 key 错误',
			));
		$data = array(
			'key'        => $key,
			'name'       => $name,
			'expire'     => $expire,
			'pic_height' => $pic_height,
			'pic_width'  => $pic_width,
			'pic_size'   => $pic_size,
			'show_count' => $show_count,
			'start_time' => $start_time,
			'random'     => $random,
			'show_pic'   => $show_pic,
			'extdata'    => $extdata,
		);

		$this->_subject_logic->insert_or_update_subject($data);
		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			));
	}

	public function data() {
		$key   = $this->get_param('key', '');
		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 100);
		$start = $page > 1 ? ($page-1) * $limit : 0;
		
		$this->throw_error(!$key, array(
			'code' => 400,
			'message' => '参数 key 错误',
			));

		$info = $this->_subject_logic->get_subject_by_key($key, true);
		$extdata_array = array(
			'keys' => [],
			'k_v' => [],
			);
		if ($info['extdata']) {
			$extdata_array = json_decode($info['extdata'], true);
		}
		$list = $this->_subject_logic->get_data_list_by_key($key, $start, $limit);
		$page_paths   = $this->page_paths;
		$native_pages = $this->native_pages;
		
		include(BASE_ROOT . '/template/misc/data.php');		
	}

	public function post_data() {
		$key        = $this->get_param('key');
		$title      = $this->get_param('title');
		$url        = $this->get_param('url');
		$expire     = $this->get_param('expire');
		$start_time = $this->get_param('start_time');
		
		$info = $this->_subject_logic->get_subject_by_key($key, true);
		$extdata = array();
		if ($info['extdata']) {
			$extdata_array = json_decode($info['extdata'], true);
			foreach ($extdata_array['keys'] as $v_key) {
				if ($this->get_param($v_key)) {
					$extdata[$v_key] = $this->get_param($v_key, '');
				}
			}
		}
		if (!empty($extdata)) {
			$extdata = json_encode($extdata);
		} else {
			$extdata = '';
		}

		$expire     = is_numeric($expire) ? intval($expire) : strtotime($expire);
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$picurl     = $this->get_param('picurl');
		$order      = (int)$this->get_param('order');

		$this->throw_error(!$key, array('code'=>400,'message'=>'参数错误'));
		$this->throw_error(!$title && empty($picurl), array('code'=>400, 'message'=>'参数错误'));

		$data = array(
			'key'        => $key,
			'title'      => $title,
			'url'        => $url,
			'pic'        => $picurl,
			'expire'     => $expire,
			'start_time' => $start_time,
			'order'      => $order,
			'dateline'   => TIMESTAMP,
			'envirnment' => (int)$this->get_param('envirnment'),
			'extdata'    => $extdata,
		);

		
		ObjectCreater::create('MiscLogic')->post_data($data);

		$this->render_json(array(
			'code' => 200,
			'message' => 'OK',
			));
	}

	public function update_data() {
		$id         = (int)$this->get_param('id');
		$key        = $this->get_param('key');
		$title      = $this->get_param('title');
		$url        = $this->get_param('url');
		$expire     = $this->get_param('expire');
		$start_time = $this->get_param('start_time');
		
		$extdata_array = @json_decode($extdata, true);

		$info = $this->_subject_logic->get_subject_by_key($key, true);
		$extdata = array();
		if ($info['extdata']) {
			$extdata_array = json_decode($info['extdata'], true);
			foreach ($extdata_array['keys'] as $v_key) {
				if ($this->get_param($v_key)) {
					$extdata[$v_key] = $this->get_param($v_key, '');
				}
			}
		}
		if (!empty($extdata)) {
			$extdata = json_encode($extdata);
		} else {
			$extdata = '';
		}

		$expire     = is_numeric($expire) ? intval($expire) : strtotime($expire);
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$picurl     = $this->get_param('picurl');
		$order      = (int)$this->get_param('order');

		$this->throw_error(!$key, array(
			'code' => 400,
			'message' => '参数 key 错误',
			));

		$this->throw_error(!$title && empty($picurl), array(
			'code' => 400,
			'message' => '参数 title 或者 picurl 错误',
			));

		$data = array(
			'key'        => $key,
			'title'      => $title,
			'url'        => $url,
			'expire'     => $expire,
			'start_time' => $start_time,
			'order'      => $order,
			'dateline'   => TIMESTAMP,
			'envirnment' => (int)$this->get_param('envirnment'),
			'extdata'    => $extdata,
		);

		if ($picurl) {
			$data['pic'] = $picurl;
		}

		ObjectCreater::create('MiscLogic')->update_data($id, $data);

		$this->render_json(array('code'=>200, 'message'=>'OK'));		
	}

	public function del_data()
	{
		$id  = $this->get_param('id');
		$key = $this->get_param('key');

		$return_url = 'index.php?mod=misc&action=data&key='.$key;

		$this->throw_error(!$id, array(
			'code' => 400,
			'message' => '参数 id 错误',
			));

		$this->_subject_logic->delete_data_by_id($id, $key);

		header('Location: ' . $return_url);
	}

	public function edit_data() {

		$id  = (int)$this->get_param('id');
		$key = $this->get_param('key');

		$return_url = 'index.php?mod=misc&action=data&key='.$key;

		$this->throw_error(!$id, array(
			'code' => 400,
			'message' => '参数 id 错误',
			));
		$info  = $this->_subject_logic->get_data_by_id($id, true);

		$this->throw_error(empty($info), array(
			'code' => 400,
			'message' => '参数 id 错误',
			));

		$extdata_array = array();
		if ($info['extdata']) {
			$extdata_array = json_decode($info['extdata'], true);
		}
		$subject_info  = $this->_subject_logic->get_subject_by_key($key, true);
		$extdata_key_array = array(
			'keys' => [],
			'k_v' => [],
			);
		if ($subject_info['extdata']) {
			$extdata_key_array = json_decode($subject_info['extdata'], true);
		}

		$page_paths   = $this->page_paths;
		$native_pages = $this->native_pages;

		include(BASE_ROOT . '/template/misc/editdata.php');		
	}

}




