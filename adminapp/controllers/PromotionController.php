<?php
class PromotionController extends AdminController 
{

	//活动列表页
	public function index() 
	{
		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 15);
		
		$start = $page > 1 ? ($page-1) * $limit : 0;
		$list  = ObjectCreater::create('LotteryPromotionDao')->get_promotion_list($start, $limit);
		$count = ObjectCreater::create('LotteryPromotionDao')->count();

		$url   = 'index.php?mod=promotion&action=index';
		$pager = HelperPager::paging($count, $limit, $page, $url);

		include(BASE_ROOT . '/template/promotion/index.php');				
	}

	//添加或编辑活动页
	public function edit()
	{
		$id   = (int)$this->get_param('id', 0);
		$info = ObjectCreater::create('LotteryPromotionDao')->fetch($id, true);

		$lottery_list = ObjectCreater::create('LotteryActivityDao')->fetch_all_enble();

		include(BASE_ROOT . '/template/promotion/edit.php');
	}

	//活动保存
	public function update()
	{
		$id  = (int)$this->get_param('id', 0);
		$lid = (int)$this->get_param('lottery_id', 0);

		$this->throw_error(!$lid, array('code'=>400, 'message'=>'参数错误！'));

		$data = array(
			'lottery_id' => $lid,
		);

		$id && $data['id'] = $id;

		ObjectCreater::create('LotteryPromotionDao')->insert_or_update($data);
		
		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//活动保存
	public function set_current()
	{
		$id  = (int)$this->get_param('id', 0);

		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));

		$res = ObjectCreater::create('LotteryPromotionDao')->set_current($id);

		$this->throw_error(!$res, array('code'=>500, 'message'=>'更新失败'));
		
		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//
	public function imei() 
	{
		$pid   = (int)$this->get_param('id', 0);
		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 15);
		
		$start = $page > 1 ? ($page-1) * $limit : 0;
		$list  = ObjectCreater::create('LotteryPromotionImeiDao')->get_imei_list_by_pid($pid, $start, $limit);
		$count = ObjectCreater::create('LotteryPromotionImeiDao')->get_imei_count_by_pid($pid);
		
		$url   = 'index.php?mod=promotion&action=imei&id='.$pid;
		$pager = HelperPager::paging($count, $limit, $page, $url);

		$promotion = ObjectCreater::create('LotteryPromotionDao')->fetch($pid);
		$lottery   = ObjectCreater::create('LotteryActivityDao')->fetch($promotion['lottery_id']);

		include(BASE_ROOT . '/template/promotion/imei.php');				
	}

	//活动列表页
	public function import() 
	{
		$pid   = (int)$this->get_param('id', 0);

		$promotion = ObjectCreater::create('LotteryPromotionDao')->fetch($pid);
		$lottery   = ObjectCreater::create('LotteryActivityDao')->fetch($promotion['lottery_id']);

		include(BASE_ROOT . '/template/promotion/import.php');				
	}

	//导出
	public function export()
	{
        ob_end_clean();

		$limit = 1000;
		$pid   = (int)$this->get_param('id');
		$obj   = ObjectCreater::create('LotteryPromotionImeiDao');

		$promotion = ObjectCreater::create('LotteryPromotionDao')->fetch($pid);
		$lottery   = ObjectCreater::create('LotteryActivityDao')->fetch($promotion['lottery_id']);

        $title_arr  = array(
            'A' => 'IMEI',
            'B' => '日期',
        );

		$index = $loop_index = 0;
		$total = $obj->get_used_imei_count_by_pid($pid);

		HelperUtils::export_csv_start(iconv('utf-8', 'gbk//ignore', $lottery['name'].'-参与抽奖的IMEI-'.date('Y-m-d').'.csv'), $title_arr);
        do{
			$list = $obj->get_used_imei_list_by_pid($pid, $loop_index, $limit);
			$row  = '';
            foreach ($list as $key => $item) {
				$row = $item['imei'] . ',' .($item['win_time'] ? date('Y-m-d', $item['win_time']) : '') . "\n";
                echo iconv('utf-8', 'gbk//ignore', $row);
                $index++;
                if($index%1000 ===0){
                    @flush();
                    @ob_flush();
                }
            }
            $loop_index += $limit;
        }while ($loop_index < $total);

        HelperUtils::export_csv_end();	
	}


	public function doimport()
	{
		$id = (int)$this->get_param('id', 0);

		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));


		$this->throw_error(!(isset($_FILES['imeis']) && $_FILES['imeis']['tmp_name']), array('code'=>400, 'message'=>'请选择要导入的文件！'));

		$fp   = fopen($_FILES["imeis"]["tmp_name"], 'r');

		$data = array();
		$i    = 0;

		while ($content = fgetcsv($fp)) {
			if(isset($content[0]) && is_numeric(trim($content[0]))){
				$data[] = array('pid'=>$id, 'imei'=>trim($content[0]));
				$i++;
				if($i%2000===0){
					ObjectCreater::create('LotteryPromotionImeiDao')->batch_insert($data);
					$data = array();
				}
			}
		}

		ObjectCreater::create('LotteryPromotionImeiDao')->batch_insert($data);

		//$this->throw_error(!$res, array('code'=>500, 'message'=>'导入失败'));

		$this->render_json(self::$WEB_SUCCESS_RT);
	}

	//活动保存
	public function delimei()
	{
		$pid  = $this->get_param('pid');
		$imei = $this->get_param('imei');

		$this->throw_error(!$pid || !$imei, array('code'=>400, 'message'=>'参数错误！'));

		ObjectCreater::create('LotteryPromotionImeiDao')->del_by_imei_pid($imei, $pid);
		
		$this->render_json(self::$WEB_SUCCESS_RT);
	}

}