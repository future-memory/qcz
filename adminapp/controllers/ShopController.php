<?php
class ShopController extends AdminController 
{
	public $order_status_array = array(
		9 => '未审核',	
		1 => '审核通过', 
		2 => '审核不通过', 
		3 => '已发货', 
		4 => '已收货',
		5 => '已完成',
		8 => '已取消', 
	);
	private $_base_url = 'index.php?mod=shop';

	public $tabs = array(
		'index'           => '订单列表',
		'goods'           => '商品列表',
		'edit_goods'      => '添加商品',
		'goods_type'      => '商品类型',
		'edit_goods_type' => '添加类型',
	);

	public $current_action = 'index';

	public function __construct() 
	{
		parent::__construct();

		$this->current_action = Nice::app()->getAction();
		$this->logic = ObjectCreater::create('ShopLogic');
	}

	public function index() 
	{
		$page       = (int)$this->get_param('page', 1);
		$limit      = (int)$this->get_param('limit', 15);
		$start      = $page > 1 ? ($page-1) * $limit : 0;
		$status     = (int)$this->get_param('status', 0);
		$goods_id   = (int)$this->get_param('goods_id', 0);
		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);
		
		$count      = $this->logic->get_count($status, $goods_id, $start_time, $end_time);
		$list       = $this->logic->get_list($status, $goods_id, $start_time, $end_time, $start, $limit);

		$orderids = array();
		foreach($list as $key=>$item){
			$orderids[]    = $item['id'];
		}

		$order_goods = $this->logic->get_order_goods_list_by_orderids($orderids);
		$order_goods = $order_goods ? $order_goods : array();
		$goods_list  = array();
		foreach($order_goods as $goods){
			$goods_list[$goods['order_id']]   = isset($goods_list[$goods['order_id']]) ? $goods_list[$goods['order_id']] : array();
			$goods_list[$goods['order_id']][] = $goods;
		}

		$order_status_array = $this->order_status_array;
		$goods_list_all = $this->logic->get_goods_range();
		
		$url = 'index.php?' . urlencode($_SERVER['QUERY_STRING']);
		$pager = HelperPager::paging($count, $limit, $page, 'index.php?mod=shop&status='.$status.'&start_time='.$start_time.'&end_time='.$end_time.'&goods_id='.$goods_id);

		include(APP_ROOT . '/template/shop/order.php');
	}

	public function export() 
	{
        ob_end_clean();

		$limit      = 1000;
		
		$page       = (int)$this->get_param('page', 1);
		$start      = $page > 1 ? ($page-1) * $limit : 0;

		$status     = (int)$this->get_param('status', 0);
		$goods_id   = (int)$this->get_param('goods_id', 0);
		
		$start_time = $this->get_param('start_time');
		$end_time   = $this->get_param('end_time');
		$start_time = is_numeric($start_time) ? intval($start_time) : strtotime($start_time);
		$end_time   = is_numeric($end_time) ? intval($end_time) : strtotime($end_time);

		$order_status_array   = $this->order_status_array;
        $title_arr  = array(
            'A' => 'id',
            'B' => 'UID',
            'C' => '收货人',
            'D' => '电话',
            'E' => '收货地址',
            'F' => '商品',
            'G' => '总计',
            'H' => '时间',
            'I' => '订单状态'
        );

        $name_plus = '';
        if($start_time && $end_time){
        	$name_plus = date('Y-m-d_H_i_s', $start_time).'至'.date('Y-m-d_H_i_s', $end_time);
        }elseif($start_time){
        	$name_plus = date('Y-m-d_H_i_s', $start_time).'起';
        }elseif($end_time){
        	$name_plus = date('Y-m-d_H_i_s', $end_time).'止';
        }

        $name_plus .= isset($order_status_array[$status]) ? $order_status_array[$status] : '';

		HelperUtils::export_csv_start(iconv('utf-8', 'gbk//ignore', '兑换订单'.$name_plus.'.csv'), $title_arr);

		$loop_index = $index = 1 ;
		$total      = $this->logic->get_count($status, $goods_id, $start_time, $end_time);
        do{
			$list    = $this->logic->get_list($status, $goods_id, $start_time, $end_time, $loop_index, $limit);
			$csv_row = '';
			// $index   = $index===1 ? 2 : $index;

			$orderids = array();
			foreach($list as $val){
				$orderids[]    = $val['id'];
			}

			$order_goods = $this->logic->get_order_goods_list_by_orderids($orderids);
			$goods_list  = array();
			foreach($order_goods as $goods){
				$goods_list[$goods['order_id']]   = isset($goods_list[$goods['order_id']]) ? $goods_list[$goods['order_id']] : array();
				$goods_list[$goods['order_id']][] = $goods;
			}
            foreach ($list as $key => $item) {
                $csv_row = $item['id']. ',' . $item['uid'] . ',' . $item['name'] . ',' . $item['phone'] . ',' . $item['address']  . ',' ;
				
				$sum = 0;
				if(isset($goods_list[$item['id']])){
					$addon = '';
					foreach ($goods_list[$item['id']] as $value) {
						$csv_row .= $addon . $value['name'] . ' x ' . $value['count'];
						$addon = '、';
						$sum += $value['price'] * $value['count'];
					}
				}else{
					$csv_row .= '-'; 
				}

				//$csv_row = $item['id']. ',' . $item['uid'] . ',' . $item['name'] . ',' .$item['phone'] . ',' .$item['address'] . ',' $item['goods_name'] . ' * ' . $item['count'] . ',' .;
				//$sum = $item['count'] * $item['price'];
                $csv_row .=  ',' . $sum . ',' . ($item['dateline'] ? date('Y-m-d H:i:s', $item['dateline']) : '-') . ','. $order_status_array[$item['status']] . "\n";
                echo iconv('utf-8', 'gbk//ignore', $csv_row);
                $index++;
                if($index%1000 ===0){
                    flush();
                    @ob_flush();
                }
            }
            $loop_index += $limit;
        }while ($loop_index < $total);

        HelperUtils::export_csv_end();	
	}


	public function goods() 
	{
		$type        = (int)$this->get_param('type', 0);
		$page        = (int)$this->get_param('page', 1);
		$limit       = (int)$this->get_param('limit', 15);
		$start       = $page > 1 ? ($page-1) * $limit : 0;
		$price_range = $this->get_param('price_range');
		$price_array = explode('-', $price_range);
		$price_start = isset($price_array[0]) ? intval($price_array[0]) : 0;
		$price_end   = isset($price_array[1]) ? intval($price_array[1]) : 0;
		
		$domain = ObjectCreater::create('AdminLogic')->get_current_domain();
		$count  = $this->logic->get_goods_count($domain);
		$list   = $this->logic->get_goods_list($domain, $start, $limit);
		$types  = $this->logic->get_goods_types($domain);		
		$pager  = HelperPager::paging($count, $limit, $page, $this->_base_url . '&action=goods');
		
		include(APP_ROOT . '/template/shop/goods.php');
	}

	public function edit_goods() 
	{
		$id   = (int)$this->get_param('id', 0);
		$info = $id ? $this->logic->get_goods_by_id($id) : array();
		if(!empty($info)){
			$info['goods_pic'] = isset($info['goods_pic']) && $info['goods_pic'] ? HelperUtils::get_pic_url($info['goods_pic'], 'shop') : null;

			$this->tabs[$this->current_action] = '编辑商品';
		}

		$domain = ObjectCreater::create('AdminLogic')->get_current_domain();
		$types  = $this->logic->get_goods_types($domain);	
		
		include(APP_ROOT . '/template/shop/edit_goods.php');	
	}

	public function edit_goods_count() 
	{
		$id   = (int)$this->get_param('id', 0);
		$info = $id ? $this->logic->get_goods_by_id($id) : array();

		include(APP_ROOT . '/template/shop/edit_goods_count.php');	
	}

	public function update_goods() 
	{
		$id         = (int)$this->get_param('id', 0);
		$name       = $this->get_param('name');
		$type       = (int)$this->get_param('type');
		$is_online  = (int)$this->get_param('is_online');
		$price      = (float)$this->get_param('price');
		$orig_price = (float)$this->get_param('orig_price', 0);
		$credit     = (int)$this->get_param('credit');
		$limit      = (int)$this->get_param('limit');
		$sort_order = (int)$this->get_param('sort_order', 0);
		$count      = (int)$this->get_param('count');
		$intro      = $this->get_param('intro');

		parent::throw_error(!$name || !$type, array('code'=>400, 'message'=>'参数错误'));
		parent::throw_error(!$price, array('code'=>400,'message' => '价格不能为空'));

		$data = array(
			'name'       => $name,
			'type'       => $type,
			'is_online'  => $is_online,
			'price'      => $price, 
			'orig_price' => $orig_price, 
			'limit'      => $limit,
			'sort_order' => $sort_order,
			'credit'     => $credit, 
			'intro'      => $intro
		);

		if($_FILES['goods_pic']['name']){
			$path = ObjectCreater::create('AttachmentLogic')->get_filepath('shop', $_FILES['goods_pic']['name']);
			$res  = ObjectCreater::create('AttachmentLogic')->upload($path, $_FILES['goods_pic']);
			parent::throw_error(!$res, array('code'=>502, 'message'=>'上传图片失败'));
			
			$data['goods_pic'] = $path;	
		}

        $domain = ObjectCreater::create('AdminLogic')->get_current_domain();
        //站点管理员添加的用户
        if($domain && $domain!='www'){
            $data['domain'] = $domain;
        }

		if($id){
			$this->logic->update_goods($id, $data);
		}else{
			$data['count'] = $count;
			$this->logic->save_goods($data);
		}

		$this->render_json(array(
			'code'    => 200,
			'message' => '操作成功',
			'returl'  => $this->_base_url . '&action=goods',
		));
	}

	public function update_goods_count() 
	{
		$id    = (int)$this->get_param('id', 0);
		$count = (int)$this->get_param('count');

		parent::throw_error(!$id, array(
			'code' => 403,
			'message' => '参数错误',
		));		

		$data = array(
			'count' => $count,           
		);

		$this->logic->update_goods($id, $data);

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $this->_base_url . '&action=goods',
		));
	}	

	public function goods_type() {
		$page    = (int)$this->get_param('page', 1);
		$limit   = (int)$this->get_param('limit', 15);
		$start   = $page > 1 ? ($page-1) * $limit : 0;
		
		$count   = $this->logic->get_goods_type_count();
		$list    = $this->logic->get_goods_type_list($start, $limit);
		
		$pager   = HelperPager::paging($count, $limit, $page, $this->_base_url . "&action=goods_type");

		include(APP_ROOT . '/template/shop/goods_type.php');
	}

	public function edit_goods_type() {
		$id   = (int)$this->get_param('id', 0);
		$info = $id ? $this->logic->get_goods_type($id) : array();
		if (!empty($info)) {
			$this->tabs[$this->current_action] = '编辑商品类型';
		}
		include(APP_ROOT . '/template/shop/edit_goods_type.php');	
	}

	public function update_goods_type() {
		$id   = (int)$this->get_param('id', 0);
		$name = $this->get_param('name');

		parent::throw_error(!$name, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$data = array('name' => $name);

        $domain = ObjectCreater::create('AdminLogic')->get_current_domain();
        //站点管理员添加的用户
        if($domain && $domain!='www'){
            $data['domain'] = $domain;
        }

		$id ? $this->logic->update_goods_type($id, $data) : $this->logic->save_goods_type($data);

		$this->render_json(array(
			'code'    => 200,
			'message' => '操作成功',
			'returl'  => $this->_base_url . '&action=goods_type',
		));
	}

	public function del_goods_type() {
		$id   = (int)$this->get_param('id', 0);

		parent::throw_error(!$id, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$count = $this->logic->get_goods_count($id);
		parent::throw_error($count>0, array(
			'code' => 403,
			'message' => '该分类下还有商品，请移走或删除后再试！',
			));		

		$this->logic->delete_goods_type($id);
		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $this->_base_url . '&action=goods_type',
			));
	}	

	public function online() {
		$id   = (int)$this->get_param('id', 0);
		$val  = (int)$this->get_param('val', 0);
		$val  = $val ? 1 : 0;

		parent::throw_error(!$id, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$data = array('is_online'=>$val);

		$this->logic->update_goods($id, $data);
		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $this->_base_url . '&action=goods',
			));	
	}

	public function order_delivery() {
		$id = (int)$this->get_param('id', 0);
		$url = $this->get_param('url');

		include(APP_ROOT . '/template/shop/order_delivery.php');
	}

	public function delivery_order() {
		$id          = (int)$this->get_param('id', 0);
		$send        = (int)$this->get_param('send');
		$message     = $this->get_param('message');
		$deliver     = $this->get_param('deliver');
		$delivery_sn = $this->get_param('delivery_sn');
		$url = $this->get_param('url');

		parent::throw_error(!$id || !$deliver || !$delivery_sn, array(
			'code' => 403,
			'message' => '参数错误',
			));

		parent::throw_error($send && !$message, array(
			'code' => 403,
			'message' => '消息不能为空',
			));

		$info = $this->logic->get_order($id);
		parent::throw_error(!$info, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$data = array('status'=>3, 'delivery_time'=>TIMESTAMP, 'delivery_sn'=>$delivery_sn, 'deliver'=>$deliver);
		$this->logic->update_order($id, $data);

		//发送系统消息
		if($send){
			$member = ObjectCreater::create('AdminLogic')->get_member_by_uid($info['uid']);
			$message = str_replace('{deliver}', $deliver, $message);
			$message = str_replace('{sn}', $delivery_sn, $message);
			$this->logic->send_message($member, $message);
		}

		$this->logic->del_user_order_cache($info['uid']);

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => urldecode($url),
			));
	}

	public function order_audit() {
		$id  = (int)$this->get_param('id', 0);
		$url = $this->get_param('url');

		include(APP_ROOT . '/template/shop/order_audit.php');
	}

	public function audit_order() {
		$id      = (int)$this->get_param('id', 0);
		$send    = (int)$this->get_param('send', 0);
		$status  = (int)$this->get_param('status', 0);
		$url     = $this->get_param('url');
		$message = $this->get_param('message');

		$return_url = ltrim(urldecode($url), '/'); //'action=shop&page='.$page;

		parent::throw_error(!$id || !$status, array(
			'code' => 403,
			'message' => '参数错误',
			));

		parent::throw_error($status!=1 && $status!=2, array(
			'code' => 403,
			'message' => '参数错误',
			));

		parent::throw_error($send && !$message, array(
			'code' => 403,
			'message' => '消息不能为空',
			));

		$info = $this->logic->get_order($id);
		parent::throw_error(!$info, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$_member = ObjectCreater::create('AdminLogic')->get_current_member();
		$info['status'] = intval($info['status']);
		$data           = array('status'=>$status, 'auditor'=>$_member['uid'], 'audit_time'=>TIMESTAMP);

		parent::throw_error($info['status']!==9, array(
			'code' => 403,
			'message' => '该订单已审核，请勿重复提交！',
			));

		//未进行退煤球和恢复库存的
		if($status===2 && $info['status']===9){
			$this->logic->restore_goods_count($id, $data, $info);
		}else{
			$this->logic->update_order($id, $data);			
		}
		
		//发送系统消息
		if($send){
			$member  = ObjectCreater::create('AdminLogic')->get_member_by_uid($info['uid']);
			$message = str_replace('{audit_result}', $status==2 ? '未通过' : '已通过', $message);
			$this->logic->send_message($member, $message);
		}

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $return_url,
			));
	}

	public function batch_audit() {
		$ids      = $this->get_param('ids', array());
		$send    = (int)$this->get_param('send', 0);
		$status  = (int)$this->get_param('status', 0);
		$page    = (int)$this->get_param('page', 0);
		$message = $this->get_param('message');

		parent::throw_error(!$ids || !$status, array(
			'code' => 403,
			'message' => '参数错误',
			));

		parent::throw_error($status!==1 && $status!==2, array(
			'code' => 403,
			'message' => '参数错误',
			));

		parent::throw_error($send && !$message, array(
			'code' => 403,
			'message' => '消息不能为空',
			));

		$orders = $this->logic->get_orders($ids);
		parent::throw_error(!$orders, array(
			'code' => 403,
			'message' => '消息不能为空',
			));

		$_member = ObjectCreater::create('AdminLogic')->get_current_member();
		foreach($orders as $info){

			$info['status'] = intval($info['status']);
			$data           = array('status'=>$status, 'auditor'=>$_member['uid'], 'audit_time'=>TIMESTAMP);
			//只处理未审核
			if($info['status']!==9){
				continue;
			}
			//未进行退煤球和恢复库存的
			if($status===2 && $info['status']===9){
				$this->logic->restore_goods_count($info['id'], $data, $info);
			}else{
				$this->logic->update_order($info['id'], $data);		
			}
			
			//发送系统消息
			if($send){
				$member = ObjectCreater::create('AdminLogic')->get_member_by_uid($info['uid']);
				$message = str_replace('{audit_result}', $status==2 ? '未通过' : '已通过', $message);
				$this->logic->send_message($member, $message);
			}
		}

		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $this->_base_url . '&action=shop&page=' . $page,
			));
	}

	public function order_done() {
		$id = (int)$this->get_param('id', 0);

		parent::throw_error(!$id, array(
			'code' => 403,
			'message' => '参数错误',
			));

		$data = array('status'=>5);
		$ret  = $this->logic->update_order($id, $data);
		parent::throw_error(!$ret, array(
			'code' => 403,
			'message' => '参数错误',
			));
		$this->render_json(array(
			'code' => 200,
			'message' => '操作成功',
			'returl' => $this->_base_url . '&action=shop',
			));
	}
}