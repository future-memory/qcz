<?php
class MonitorCommand {
	
	public function __construct(){
		$this->logic = ObjectCreater::create('MonitorLogic');
	}
	public function run(){
		$this->logic->run();
	}
}