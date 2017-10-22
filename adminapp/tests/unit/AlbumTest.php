<?php
class AlbumTest extends TestCase {
	public function __construct() {
		parent::__construct();
		$this->logic = ObjectCreater::create('AlbumLogic');
	}

	public function testList() {
		$authorid = 2959099;
		$fid = 84;
		$start = 0;
		$max_limit = 50;

		$page = 0;
		$perpage = 0;
        $page = $page < 1 ? 1 : $page;
        $limit = ($perpage === 0) ? $max_limit : $perpage;
        $limit = $limit > $max_limit ? $max_limit : $limit;
        $start = ($page - 1) * $limit;

        $this->assertTrue($limit === $max_limit);
        $this->assertTrue(!$start);
		$result = $this->logic->get_list($authorid, $fid, $start, $limit);
		$this->assertArrayHasKey('next', $result);
		$this->assertArrayHasKey('thumbs_count', $result);
		$this->assertArrayHasKey('list', $result);

		$start = 0;
		$limit = 0;
		$result = $this->logic->get_list($authorid, $fid, $start, $limit);
		// var_dump($result);
		$this->assertArrayHasKey('next', $result);
		$this->assertArrayHasKey('thumbs_count', $result);
		$this->assertArrayHasKey('list', $result);		

		$page = 2;
		$perpage = 60;
        $page = $page < 1 ? 1 : $page;
        $limit = ($perpage === 0) ? $max_limit : $perpage;
        $limit = $limit > $max_limit ? $max_limit : $limit;
        $start = ($page - 1) * $limit;
        $this->assertEquals($limit, 50);
        $this->assertEquals($start, 50);
		$result = $this->logic->get_list($authorid, $fid, $start, $limit);
		$this->assertArrayHasKey('next', $result);
		$this->assertEquals($result['next'], 1);
		$this->assertArrayHasKey('thumbs_count', $result);
		$this->assertArrayHasKey('list', $result);
	}

	public function testCount() {
		$authorid = 2959099;
		$max_limit = 2;
       	$fid = 84;

       	$page = 0;
       	$perpage = 0;
       	$start = 0;
       	$limit = ($perpage === 0) ? $max_limit : $perpage;
       	$limit = $limit > $max_limit ? $max_limit : $limit;

		$result = $this->logic->get_counts($authorid, $fid, $start, $limit);
		$this->assertArrayHasKey('thumb_count', $result);
		$this->assertArrayHasKey('img_count', $result);			
	}
}