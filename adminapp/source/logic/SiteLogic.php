<?php

class SiteLogic extends Logic
{

	public function __construct()
	{
		$this->_dao = ObjectCreater::create('SiteDao');
	}

	public function get_allow_mod_by_domain($domain)
	{
        $perms = ObjectCreater::create('SitePermDao')->fetch_all_by_domain($domain);
        $mod_allow = array();
        foreach ($perms as $perms_key => $perms_value) {
            $mod_allow[] = $perms_value['perm'];
        }
        return $mod_allow;
	}


}