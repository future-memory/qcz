<?php
class ThreadTest extends TestCase
{
	public $logic = null;
	public $dao   = null;
	public function __construct()
	{
		//$_GET['query_debug'] = 1;
		parent::__construct();
		$this->logic = ObjectCreater::create('ThreadLogic');
		$this->dao   = ObjectCreater::create('ThreadDao');

		ObjectCreater::create('MemberLogic')->cur_member = array('uid'=>7680184, 'username'=>'chen444', 'groupid'=>1);
	}

	//发送私信
	public function testModerate()
	{
		$op = 'del';
		$tids = array(5803635);
		$fid = 22;
		$expire = null;
		$reason = '';
		$val = '';

		$this->logic->do_moderate($op, $tids, $fid, $expire, $reason);
		//$this->assertTrue($is_signed);
	}


    
}