<?php
class SigninTest extends TestCase
{
	public $logic = null;
	public $dao   = null;
	public function __construct()
	{
		//$_GET['query_debug'] = 1;
		parent::__construct();
		$this->logic = ObjectCreater::create('SigninLogic');
		$this->dao   = ObjectCreater::create('SigninDao');
		//设定测试的用户
		$this->logic->cur_member = array('uid'=>7680184, 'username'=>'chen444', 'groupid'=>1);
	}

	//签到及判断是否已签到
	public function testSigned()
	{
		//签到前
		$signed = $this->logic->fetch($this->logic->cur_member['uid']);

		try{
			$msg  = $this->logic->sign();
			$this->logic->save_log();

			$code = 200;
        } catch (BizException $e) {
        	$code = $e->getCode();
        }

        //签到后
        $data = $this->logic->fetch($this->logic->cur_member['uid']);

		$signed['cons'] = isset($signed['cons']) ? $signed['cons'] : 0;
		$yestoday_0     = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
		$cons           = isset($signed['lasttime']) && $signed['lasttime']>=$yestoday_0 ? intval($signed['cons'])+1 : 1;

        $this->assertTrue(($code==201 && $signed['cons']==$data['cons']) || $cons==$data['cons']);

		$is_signed = $this->logic->check_signed($this->logic->cur_member['uid']);

		$this->assertTrue($is_signed);
	}

	//签到列表
	public function testSignedList()
	{
		$list = $this->logic->get_signed_list($this->logic->cur_member['uid'], $from_date=0);
		$dcnt = intval(date('d'));
		$this->assertTrue(count($list)==$dcnt);
	}

	// //补签
	public function testResign()
	{
		//补签前
		$last_signed = $this->logic->fetch($this->logic->cur_member['uid']);

		$list        = $this->logic->get_signed_list($this->logic->cur_member['uid'], $from_date=0);

		$the_date = null;  //补签日期
		$signed   = 0;     //已签到天数
		$unsign   = 0;
		foreach($list as $item) {
			if($item['signed']==0){
				$unsign += 1;
				$the_date = $the_date===null ? $item['date'] : $the_date;
			}
			if($item['signed']==1){
				$signed += 1;
			}
		}

		try{
			$last = 0; 
			$msg  = $this->logic->resign($this->logic->cur_member['uid'], $the_date, $last);
			$this->logic->save_log();
			$code = 200;
        } catch (BizException $e) {
        	$code = $e->getCode();
        }

		//补签后
		$list    = $this->logic->get_signed_list($this->logic->cur_member['uid'], $from_date=0);
		$signed2 = 0;     //已签到天数
		foreach($list as $item) {
			if($item['signed']==1){
				$signed2 += 1;
			}
		}

		//补签的前一天
		$the_day_before = strtotime($the_date) - 86400;
		$data_before    = $this->logic->get_signed_item($this->logic->cur_member['uid'], $the_day_before);		

		//只差一天未补时
		if($unsign===1 && strtotime($the_date)<strtotime(date('Y-m-d 00:00:00'))){
			$new_signed = $this->logic->fetch($this->logic->cur_member['uid']);
			//连续签到天数加上
			$this->assertTrue($new_signed['cons'] == ($last_signed['cons'] + 1 + $data_before['last_days']));
		}else{
			if($the_date){

				//补签当天的数据
				$the_data                       = $this->logic->get_signed_item($this->logic->cur_member['uid'], $the_date);
				
				$the_data['last_days']          = isset($the_data['last_days']) ? intval($the_data['last_days']) : 0;
				$the_data['accumulate_days']    = isset($the_data['accumulate_days']) ? intval($the_data['accumulate_days']) : 0;

				$data_before['last_days']       = isset($data_before['last_days']) ? intval($data_before['last_days']) : 0;
				$data_before['accumulate_days'] = isset($data_before['accumulate_days']) ? intval($data_before['accumulate_days']) : 0;


				//$this->assertTrue($the_data['last_days']===$data_before['last_days']+1);	  //没有连贯性时（如前一天是之前签到过） 此断言不成立
			}
		}


		$this->assertTrue($the_date===null || $code!=200 || $signed+1==$signed2);	
	}


	//补签  错误的日期
	public function testErrDateResign()
	{
		//补签前
		$list = $this->logic->get_signed_list($this->logic->cur_member['uid'], $from_date=0);
		$the_date = null;  //补签日期
		$signed   = 0;     //已签到天数
		foreach($list as $item) {
			$the_date = $item['date'];
		}

		try{
			$last = 0; 
			$msg  = $this->logic->resign($this->logic->cur_member['uid'], $the_date, $last);
			$code = 200;
        } catch (BizException $e) {
        	$code = $e->getCode();
        }

		$this->assertTrue(isset($code) && $code==408);	
	}

	
    
}