<?php
class HungbaoTest extends TestCase {

	public function __construct() {
		parent::__construct();
		$this->_logic = ObjectCreater::create('HungbaoLogic');
	}

	public function testGetChance() {
		$id = '';
		$show = $this->_logic->get_chance_by_id($id);
		var_dump($show);
	}
}