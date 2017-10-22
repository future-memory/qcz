<?php

class BaseWidget
{
	protected $_owner;

	public function __construct($owner=null)
	{
		$this->_owner = $owner===null ? Nice::app()->getController() : $owner;
	}

	public function run()
	{
	}

}
