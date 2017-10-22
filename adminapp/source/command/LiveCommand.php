<?php
/**
 * 直播发帖计划任务
 * @property LiveLogic $liveLogic
 */
class LiveCommand {
	
	private $liveLogic;
	
	public function __construct(){
		$this->liveLogic = ObjectCreater::create('LiveLogic');
	}
	public function run(){
		$this->liveLogic->daemon();
	}
}
