<?php
class MemberController extends BaseController 
{
	//
	public function login()
	{
		$refer = $this->get_param('refer');

		ObjectCreater::create('MemberLogic')->gologin($refer);
	}

}