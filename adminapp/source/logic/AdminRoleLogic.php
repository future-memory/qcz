<?php
class AdminRoleLogic extends Logic
{
	public function __construct()
	{
		$this->_dao = ObjectCreater::create('AdminRoleDao');
	}

}