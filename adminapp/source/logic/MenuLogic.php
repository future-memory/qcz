<?php

class MenuLogic extends Logic
{
	//获取所有模块
	public function get_mod_list($filter_master=true)
	{
        $menu_list = HelperConfig::get_config('menu');
        $mod_list  = array();
        foreach($menu_list['menu'] as $key => $value) {
            foreach ($value['submenu'] as $k => $v) {
            	if($filter_master && $v['mod'] === 'stationmaster'){
            		continue;
            	}
                $mod_list[] = $v['mod'];
            }
        }

        return $mod_list;		
	}

	//获取所有menu
	public function get_all_menu($filter_master=true)
	{
        $menu_list = HelperConfig::get_config('menu');
        foreach($menu_list['menu'] as $key => $value) {
            foreach ($value['submenu'] as $k => $v) {
            	if($filter_master && $v['mod'] === 'stationmaster'){
            		unset($menu_list['menu'][$key]['submenu'][$k]);
            	}
            }
        }

        return $menu_list;		
	}	

	//根据当前用户的权限  获取所有的menu
	public function get_perm_menu_list()
	{
         $member       = ObjectCreater::create('AdminLogic')->get_current_member();
         $menu_list    = HelperConfig::get_config('menu');
         $is_founder   = ObjectCreater::create('AdminLogic')->check_founder($member);
         $admin_member = ObjectCreater::create('AdminMemberDao')->fetch($member['uid']);
         $role_id      = isset($admin_member['role_id']) ? intval($admin_member['role_id']) : null;

        //过滤没权限的menu $admin_member==0 为副站长
        if(!$is_founder) {
            $mod_allow = $role_id==0 ? array() : ObjectCreater::create('AdminPermLogic')->get_allow_mod_by_role($role_id);

            foreach($menu_list['menu'] as $key => $value) {
                foreach($value['submenu'] as $k => $v) {
                    if($role_id!=0 && !in_array($v['mod'], $mod_allow)) {
                        unset($menu_list['menu'][$key]['submenu'][$k]);
                    }
                    if($v['mod']=='stationmaster'){
                    	unset($menu_list['menu'][$key]['submenu'][$k]);
                    }
                }
            }          
        }

        return $menu_list;	
	}


}