<?php

class MasterController extends AdminController 
{
    public function __construct() 
    {
        parent::__construct();
        $this->hint_url = "index.php?mod=master";
    }

    public function index() 
    {
        $page     = (int)$this->get_param('page', 1);
        $limit    = 10;
        $start    = $page > 1 ? ($page-1)*$limit : 0;        
        $founders = HelperConfig::get_config('global::founders');
        $founders = explode(',', $founders);        
        $roles    = ObjectCreater::create('AdminRoleLogic')->range();
        $domain   = $this->logic->get_current_domain();
        $members  = ObjectCreater::create('AdminMemberDao')->get_member_list($domain, $start, $limit);

    	include(APP_ROOT . '/template/master/index.php');
    }

    public function role_list() 
    {
        $member     = $this->logic->get_current_member();
        $is_founder = $this->logic->check_founder($member);

        $domain = $this->logic->get_current_domain();
        $roles  = ObjectCreater::create('AdminRoleDao')->get_role_list($domain);

        include(APP_ROOT . '/template/master/role_list.php');
    }

    public function role_edit()
    {
        $id     = (int)$this->get_param('id', 0);
        $role   = ObjectCreater::create('AdminRoleDao')->fetch($id);
        $perms  = ObjectCreater::create('AdminRolePermDao')->fetch_all_by_role($id);

        $permed = array();
        foreach($perms as $key => $value) {
            $permed[] = $value['perm'];
        }

        $member     = $this->logic->get_current_member();
        $is_founder = $this->logic->check_founder($member);
        $menu_list  = ObjectCreater::create('MenuLogic')->get_perm_menu_list();

        include(APP_ROOT . '/template/master/role_edit.php');
    }

    public function role_update()
    {
        $id   = (int)$this->get_param('id', 0);
        $mods = $this->get_param('mods');
        $mods = is_array($mods) ? $mods : explode(',', $mods);

        $this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误'));

        $mod_list   = ObjectCreater::create('MenuLogic')->get_mod_list();
        $mod_list[] = '_allowpost';

        //把原有的权限清除
        ObjectCreater::create('AdminRolePermDao')->delete_perms_by_role($id);

        //保存选择的权限
        $data = array();
        foreach ($mods as $mod) {
            if(in_array($mod, $mod_list)){
                $data[] = array('role_id'=>$id, 'perm'=>$mod);
            }
        }

        ObjectCreater::create('AdminRolePermDao')->batch_insert($data);

        $this->render_json(array('code'=>200, 'message'=>'编辑成功'));
    }


    public function role_delete() 
    {
        $id = (int)$this->get_param('id', 0);
        $this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误'));

        ObjectCreater::create('AdminRolePermDao')->delete_perms_by_role($role_id);
        ObjectCreater::create('AdminMemberDao')->delete_member_by_role(array('role_id' => $role_id));
        ObjectCreater::create('AdminRoleDao')->delete($role_id);

        $this->render_json(array('code'=>200, 'message'=>'删除成功'));
    }

    public function role_add() 
    {
        $name = HelperUtils::xss_bug(trim($this->get_param('name', '')));
        $this->throw_error(!$name, array('code'=>400, 'message'=>'参数错误'));

        $exists = ObjectCreater::create('AdminRoleDao')->fetch_by_name($name);
        $this->throw_error(!empty($exists), array('code'=>400, 'message'=>'角色已经存在'));

        $data = array('name' => $name);

        $domain = $this->logic->get_current_domain();
        //站点管理员添加的用户
        if($domain && $domain!='www'){
            $data['domain'] = $domain;
        }

        ObjectCreater::create('AdminRoleDao')->insert($data);
        $this->render_json(array('code'=>200, 'message'=>'添加成功'));
    }

    public function user_edit() 
    {
        $uid  = (int)$this->get_param('uid', 0);
        $user = $uid ? ObjectCreater::create('AdminMemberDao')->fetch($uid) : array(); 
        $role_id = isset($user['role_id']) ? $user['role_id'] : 0;

        $roles = ObjectCreater::create('AdminRoleDao')->range();

        include(APP_ROOT . '/template/master/user_edit.php');
    }

    public function user_update() 
    {
        $uid      = (int)$this->get_param('uid');
        $role_id  = (int)$this->get_param('role_id');
        $username = (string)trim($this->get_param('username'));
        $username = HelperUtils::xss_bug($username);
        $password = (string)trim($this->get_param('password'));
        $password = HelperUtils::xss_bug($password);
        $password = $password ? md5($password.PASS_SLAT) : null;

        $this->throw_error(!$username, array('code'=>400,'message'=>'用户名不能为空'));
        $this->throw_error(!$role_id, array('code'=>400,'message'=>'请选择用户角色'));

        $exists = ObjectCreater::create('AdminMemberDao')->get_member_by_username($username);
        $this->throw_error($exists && $exists['uid']!=$uid, array('code'=>400,'message'=>'用户名已存在'));

        $data = array(
            'role_id'  => $role_id,
            'username' => $username,
        );

        $domain = $this->logic->get_current_domain();
        //站点管理员添加的用户
        if(!$uid && $domain && $domain!='www'){
            $data['domain'] = $domain;
        }

        $uid && $data['uid'] = $uid;
        $password && $data['password'] = $password;
        
        ObjectCreater::create('AdminMemberDao')->insert_or_update($data);

        $this->render_json(array('code'=>200,'message'=>'更新成功'));     
    }    


    public function user_delete() 
    {
        $uid = (int)$this->get_param('uid', 0);
        ObjectCreater::create('AdminMemberDao')->delete($uid);

        $this->render_json(array(
            'code' => 200,
            'message' => '删除成功'
        ));
    }
}