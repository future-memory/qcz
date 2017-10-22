<?php
class PmTest extends TestCase 
{
	public $logic = null;
	public $dao   = null;
	public function __construct()
	{
		//$_GET['query_debug'] = 1;
		parent::__construct();
		$this->logic = ObjectCreater::create('PmLogic');
		$this->dao   = ObjectCreater::create('PmDao');
	}

	//发送私信
	public function testSend()
	{
		$subject = 'test';
		$message = 'test';
		$this->logic->send(7680184, $subject, $message, 2);

		//$this->assertTrue($is_signed);
	}


    
}