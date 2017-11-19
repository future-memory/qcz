<?php

class SiteController extends AdminController 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function index() 
    {
        $sites = ObjectCreater::create('SiteDao')->range();

    	include(APP_ROOT . '/template/site/index.php');
    }


    public function edit() 
    {
        $domain = $this->get_param('domain');
        $site   = $domain ? ObjectCreater::create('SiteDao')->fetch($domain) : array(); 

        include(APP_ROOT . '/template/site/edit.php');
    }

    public function update() 
    {
        $name   = (string)trim($this->get_param('name'));
        $name   = HelperUtils::xss_bug($name);
        $domain = (string)trim($this->get_param('domain'));
        $domain = HelperUtils::xss_bug($domain);
        $appkey = (string)trim($this->get_param('appkey'));
        $appkey = HelperUtils::xss_bug($appkey);
        $secret = (string)trim($this->get_param('secret'));
        $secret = HelperUtils::xss_bug($secret);
        $old_domain = (string)trim($this->get_param('old_domain'));
        $old_domain = HelperUtils::xss_bug($old_domain);

        $this->throw_error(!$name, array('code'=>400,'message'=>'名称不能为空'));
        $this->throw_error(!$domain, array('code'=>400,'message'=>'domain不能为空'));

        if($domain!=$old_domain){
            $exists = ObjectCreater::create('SiteDao')->fetch($domain);
            $this->throw_error($exists, array('code'=>400,'message'=>'domain已存在'));
        }

        $data = array(
            'domain' => $domain,
            'name'   => $name,
            'appkey' => $appkey,
            'secret' => $secret,           
        );
        
        ObjectCreater::create('SiteDao')->insert_or_update($data);

        $this->render_json(array('code'=>200,'message'=>'更新成功'));     
    }    


    public function delete() 
    {
        $domain = $this->get_param('domain');
        //ObjectCreater::create('SiteDao')->delete($domain);

        $this->render_json(array('code' => 200, 'message' => '删除成功'));
    }

    public function perm()
    {
        $domain = $this->get_param('domain');
        $site   = ObjectCreater::create('SiteDao')->fetch($domain);
        $perms  = ObjectCreater::create('SitePermDao')->fetch_all_by_domain($domain);

        $permed = array();
        foreach($perms as $key => $value) {
            $permed[] = $value['perm'];
        }

        $menu_list  = ObjectCreater::create('MenuLogic')->get_perm_menu_list();

        include(APP_ROOT . '/template/site/perm.php');
    }

    public function perm_update()
    {
        $domain = $this->get_param('domain');
        $mods   = $this->get_param('mods');
        $mods   = is_array($mods) ? $mods : explode(',', $mods);

        $this->throw_error(!$domain, array('code'=>400, 'message'=>'参数错误'));

        $mod_list = ObjectCreater::create('MenuLogic')->get_mod_list();

        //把原有的权限清除
        ObjectCreater::create('SitePermDao')->delete_perms_by_domain($domain);

        //保存选择的权限
        $data = array();
        foreach ($mods as $mod) {
            if(in_array($mod, $mod_list)){
                $data[] = array('domain'=>$domain, 'perm'=>$mod);
            }
        }

        ObjectCreater::create('SitePermDao')->batch_insert($data);

        $this->render_json(array('code'=>200, 'message'=>'编辑成功'));
    }

}