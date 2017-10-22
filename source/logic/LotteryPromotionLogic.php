<?php
class LotteryPromotionLogic extends Logic 
{
	public function __construct() 
	{
		$this->_dao = ObjectCreater::create('LotteryPromotionDao');
	}


	public function get_current_promotion()
	{
		return $this->_dao->get_current_promotion();
	}

	public function check_imei($id, $imei, $use=false)
	{
		$promotion = ObjectCreater::create('LotteryPromotionDao')->fetch($id);
		$lottery   = ObjectCreater::create('LotteryActivityDao')->fetch($promotion['lottery_id']);

		$lottery['end_time']   = intval($lottery['end_time']);
		$lottery['start_time'] = intval($lottery['start_time']);
		//判断是否开启
		self::throw_exception(!$lottery['enable'], lang_lottery::ERROR_ENABLE);
		//判断是否在期间内
		self::throw_exception(TIMESTAMP < $lottery['start_time'] , lang_lottery::ERROR_NOT_START);
		self::throw_exception(TIMESTAMP > $lottery['end_time'], lang_lottery::ERROR_END);
	

		$data = ObjectCreater::create('LotteryPromotionImeiDao')->get_item_by_imei_pid($imei, $id);
		$this->throw_exception(empty($data), array('code'=>400, 'message'=>'抱歉，您不符合本次活动的参与条件，感谢您的关注'));
		$this->throw_exception($data && intval($data['uid']), array('code'=>400, 'message'=>'IMEI已使用，不能重复参与'));

		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$log = ObjectCreater::create('LotteryPromotionImeiDao')->get_used_by_uid($member['uid'], $id);
		self::throw_exception(!empty($log), array('code'=>405, 'message'=>'您已抽奖，不能重复参与'));

		if($use){
			ObjectCreater::create('LotteryPromotionImeiDao')->update($imei, array('uid'=>$member['uid']));
			ObjectCreater::create('LotteryPromotionImeiDao')->del_user_cache($member['uid'], $id);
		}
	}

}