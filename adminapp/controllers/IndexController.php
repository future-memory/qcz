<?php
/**
 * @property AdminLogic $logic
 *
 */
class IndexController extends AdminController 
{
    public function index()
    {
        $member = $this->logic->get_current_member();        
        // menu
        $menu_list = ObjectCreater::create('MenuLogic')->get_perm_menu_list();

    	include(APP_ROOT . '/template/main/index.php');
    }

    public function info() 
    {
        $data = array();
        $data['onlines'] = array(); //$this->logic->get_admin_online();
        include(APP_ROOT . '/template/main/info.php');
    }

 
    public function logout()
    {
        $this->logic->logout();
        header("location: ./");       
    }


    public function login()
    {
        $member = $this->logic->get_current_member();
        if($member && $member['uid']){
            header('Location: ./');
            exit;
        }
        
        $times_key   = 'login_times_'.HelperUtils::getClientIP();
        $login_times = ObjectCreater::create('KVDataLogic')->get_val($times_key);
        $need_vcode  = $login_times > LOGIN_TIMES_TO_VERIFY;

        include(APP_ROOT . '/template/main/login.php');
    }
    
    public function dologin()
    {
        $result = self::$WEB_SUCCESS_RT;

        $passVerify = $this->verify_param($_POST, array(
            array("username", "required"),
            array("userpass", "required"),
            //array("vcode", "required"),
        ));
        $login_name  = $this->get_param('username', null);
        $passwd      = $this->get_param('userpass', null);
        $vcode       = $this->get_param('vcode', null);
        $keep_alive  = (int)$this->get_param('remember', 0);
        $client_time = (int)$this->get_param('client_time', 0);

        $ret  = $this->logic->login($login_name, $passwd, $client_time, $keep_alive, $vcode);

        $this->render_json($result);
    }

    //验证码图片
    public function vcode()
    {
        Header("Content-type: image/PNG"); 
        srand((double)microtime(true) * 1000);


        $im    = imagecreate(55, 22) or die("Failed to initialize new GD image stream!"); 
        $black = ImageColorAllocate($im, 0, 0, 0);
        $white = ImageColorAllocate($im, 255, 255, 255); 
        $gray  = ImageColorAllocate($im, 200, 200, 200);

        imagefill($im, 0, 0, $gray);
        $usedchar = "3,4,6,7,8,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,T,U,V,W,X,Y";
        $list     = explode(",", $usedchar);
        $authnum  = '';
        for($i = 0; $i < 4; $i++){
            $randnum = rand(0, count($list) - 1);
            $authnum .= $list[$randnum];
        }

        HelperCookie::set('vcode', $authnum, 0, true);

        imagestring($im, 5, 10, 3, $authnum, $black);
        for($i = 0; $i < 200; $i++){ 
            $randcolor = ImageColorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand() % 60 , rand() % 40 , $randcolor); 
        } 
        ImagePNG($im); 
        ImageDestroy($im);

        exit;
    }

    //切换domain
    public function switch_domain() 
    {
        $domain = $this->get_param('domain');
        $this->throw_error(!$domain, array('code'=>400, 'message'=>'domain empty'));

        $member  = $this->logic->get_current_member();
        $founder = $this->logic->check_founder($member);
        $this->throw_error(!$founder && $member['domain']!='www', array('code'=>400, 'message'=>'forbidden'));

        HelperCookie::set('current_domain', $domain, 0, true);

        $this->render_json(self::$WEB_SUCCESS_RT);
    }
    
}





