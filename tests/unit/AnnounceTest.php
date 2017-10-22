<?php
class AnnounceTest extends TestCase {
	private  $logic;
	public function __construct() {
		$this->logic = ObjectCreater::create('AnnouncepmLogic');
	}

	public function testList() {
		$uid = 2959099;
		$start = 0;
		$limit = 20;
		$result = $this->logic->get_list_by_uid($uid, $start, $limit);
		$this->assertArrayHaskey('count', $result);
		$this->assertArrayHaskey('list', $result);
		$this->assertGreaterThanOrEqual(count($result['list']), $result['count']);
	}

	public function testDetail() {
		$uid = 2959099;
		$gpmid = 8888888;
		// get fail.
		$is_valid_ret = $this->logic->is_gpmid_valid($uid, $gpmid);
		$this->assertFalse($is_valid_ret);
		// get success.
		$gpmid = 580478;
		$is_valid_ret = $this->logic->is_gpmid_valid($uid, $gpmid);
		$this->assertArrayHaskey('uid', $is_valid_ret);
		$this->assertEquals($uid, $is_valid_ret['uid']);
	}

	public function testSetReaded() {
		$uid = 2959099;
		$gpmid = 8888888;
		// update fail.
		$result = $this->logic->set_readed($uid, $gpmid);
		// update success.
		$gpmid = 580478;
		ObjectCreater::create('AnnouncepmDao')->update_by_gpmid($uid, $gpmid, array('status' => 0));
		$result = $this->logic->set_readed($uid, $gpmid);

		// check
		$result = $this->logic->is_gpmid_valid($uid, $gpmid);
		$this->assertEquals(1, $result['status']);
	}

	public function testSetDeleted() {
		$uid = 2959099;
		$gpmid = 8888888;
		// update fail.
		$result = $this->logic->set_deleted($uid, $gpmid);
		// update success.
		$gpmid = 580478;
		ObjectCreater::create('AnnouncepmDao')->update_by_gpmid($uid, $gpmid, array('status' => 1));
		$result = $this->logic->set_deleted($uid, $gpmid);
		// check
		$result = $this->logic->is_gpmid_valid($uid, $gpmid);
		$this->assertEquals(-1, $result['status']);		
	}
}