<?php
class ShopLogic extends Logic 
{
	public function __construct() 
	{
		$this->_dao = ObjectCreater::create('ShopOrderDao');
		$this->_goods_dao = ObjectCreater::create('ShopGoodsDao');
		$this->_order_goods_dao = ObjectCreater::create('ShopOrderGoodsDao');
		$this->_goods_type_dao = ObjectCreater::create('ShopGoodsTypeDao');
	}

	public function restore_goods_count($id, $data, $info)
	{
		$this->_dao->begin();
		//退煤球 --为什么用if：有可能0煤球
		$res = true;
		if($info['amount']){
			$res = $this->send_mb($info['uid'], $info['amount'], 'SOP');
		}
		//恢复库存
		$goods_list = $this->_order_goods_dao->get_list_by_orderid($id);
		$res2 = true;
		foreach($goods_list as $goods){
			$res_tmp = $this->_goods_dao->increase_goods_count($goods['goods_id'], $goods['count']);
			if(!$res_tmp){
				$res2 = false;
			}
		}
		//更新状态
		$res3 = $this->_dao->update($id, $data);
		if($res3 && $res2 && $res){
			$this->_dao->commit();
			$this->_dao->del_user_order_cache($info['uid']);
		}else{
			$this->_dao->rollback();
		}		
	}


	public function get_order($id) {
		return $this->_dao->fetch($id);
	}

	public function get_orders($ids) {
		return $this->_dao->fetch_all($ids);
	}

	public function update_order($id, $data) {
		return $this->_dao->update($id, $data);
	}

	public function get_count($status=0, $goods_id=0, $start_time=0, $end_time=0, $belong=null) {
		return $this->_dao->get_count($status, $goods_id, $start_time, $end_time, $belong);
	}

	public function get_list($status=0, $goods_id=0, $start_time=0, $end_time=0, $start=0, $limit=15, $belong=null) {
		return $this->_dao->get_list($status, $goods_id, $start_time, $end_time, $start, $limit, $belong);
	}

	public function del_user_order_cache($uid, $start=0) {
		return $this->_dao->del_user_order_cache($uid, $start);
	}

	public function get_goods_by_id($id) {
		return $this->_goods_dao->fetch($id);
	}

	public function update_goods($id, $data) {
		return $this->_goods_dao->update($id, $data);
	}

	public function save_goods($data) {
		return $this->_goods_dao->insert($data);
	}

	public function get_goods_range() {
		return $this->_goods_dao->range();
	}

	//商品个数
	public function get_goods_count($domain) 
	{
		return $this->_goods_dao->get_goods_count($domain);
	}

	//商品列表
	public function get_goods_list($domain, $start=0, $limit=15) 
	{
		return $this->_goods_dao->get_goods_list($domain, $start, $limit);
	}

	public function get_order_goods_list_by_orderids($orderids) {
		return $this->_order_goods_dao->get_list_by_orderids($orderids);
	}

	public function get_goods_type($id) {
		return $this->_goods_type_dao->fetch($id);
	}

	public function update_goods_type($id, $data) {
		return $this->_goods_type_dao->update($id, $data);
	}

	public function save_goods_type($data) {
		return $this->_goods_type_dao->insert($data);
	}

	public function delete_goods_type($id) {
		return $this->_goods_type_dao->delete($id);
	}

	public function get_goods_types($domain) {
		return $this->_goods_type_dao->get_goods_types($domain);
	}

	public function get_goods_type_count() {
		return $this->_goods_type_dao->count();
	}

	public function get_goods_type_list($start, $limit) {
		return $this->_goods_type_dao->get_list($start, $limit);
	}
}