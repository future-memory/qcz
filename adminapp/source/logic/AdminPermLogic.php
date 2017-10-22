<?php

class AdminPermLogic extends Logic
{

	public function __construct()
	{
		$this->_dao = ObjectCreater::create('AdminRolePermDao');
	}

	public function get_allow_mod_by_role($role_id)
	{
        $perms = $this->_dao->fetch_all_by_role($role_id);
        $mod_allow = array();
        foreach ($perms as $perms_key => $perms_value) {
            $mod_allow[] = $perms_value['perm'];
        }
        return $mod_allow;
	}


}