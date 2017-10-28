<?php

class AttachmentController extends AdminController 
{
    public function __construct()   
    {
        $this->logic = ObjectCreater::create('AttachmentLogic');
    }
 
    /**
     * @api {post} /index.php?mod=photo&action=upyun_sign 又拍云上传参数签名
     * @apiName upyun_sign
     * @apiGroup Attachment
     * @apiVersion 2.0.0
     * @apiDescription 又拍云上传参数签名
     * @apiPermission Logined
     * @apiParam {Int} file_num 
     * @apiParam {Sting} module    
     * @apiSuccess (response) {Number} code success 200
     * @apiSuccess (response) {json} message 提示信息
     * @apiSuccessExample {json} Response 200
     * 
     *{
     * "code": 200,
     * "data": {
     *  "url": "http://v0.api.upyun.com/bbs-image",
     *  "signs": [
     *  {
     *   "signature": "d67f2bf126d77d29509a5ca86ae6fe9d",
     *   "policy": "eyJidWNrZXQiOiJtei1iYnMtaW1hZ2UiLCJleH=="
     *  }
     * ]
     * }
     *}
     * 
    */
    public function upyun_sign()
    {
        $file_num = (int)$this->get_param('file_num', 0);
        $module   = $this->get_param('module', 'misc');
        $member = ObjectCreater::create('AdminLogic')->get_current_member();
        //判断是否登录
        $this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

        //$group   = ObjectCreater::create('GroupLogic')->get_cur_group_fields();
        //$maxsize = isset($group['maxattachsize']) && $group['maxattachsize'] ? $group['maxattachsize'] : 2*1024*1024 ;
        $maxsize = 5 * 1024 * 1024;

        $data = ObjectCreater::create('HelperUpyun')->upyun_sign($member['uid'], $module, $file_num, $maxsize);

        $this->render_json(array('code'=>200,'data'=>$data));        

    }

    public function qiniu_sign()
    {
        $module = $this->get_param('module', 'misc');
        $name   = $this->get_param('filename');
        $member = ObjectCreater::create('AdminLogic')->get_current_member();
        //判断是否登录
        $this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

        $bucket = 'qcz-file';
        $token  = ObjectCreater::create('HelperQiniu')->sign($bucket, $module, $name);
        $url    = 'https://up.qbox.me';//'http://up.qiniu.com';

        $this->render_json(array('code'=>200,'data'=>array('token'=>$token, 'key'=>$name, 'url'=>$url)));           
    }

    public function local_sign()
    {
        $module = $this->get_param('module', 'misc');
        $name   = $this->get_param('filename');
        $member = ObjectCreater::create('AdminLogic')->get_current_member();
        //判断是否登录
        $this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

        $module = !in_array($module, $this->logic::$modules) ? $this->logic::$default_module : $module;
        
        $name = $this->logic->get_filepath($module, $name);
        
        $url  = 'index.php?mod=attachment&action=upload';//'http://up.qiniu.com';

        $this->render_json(array('code'=>200,'data'=>array('filepath'=>$name, 'url'=>$url)));       
    }

    public function upload()
    {
        $filepath = $this->get_param('filepath');

        $this->throw_error(!$filepath, array('code'=>400, 'message'=>'参数错误'));
        $this->throw_error(!$_FILES['file']['tmp_name'], array('code'=>400, 'message'=>'请选择要上传的文件'));

        $member = ObjectCreater::create('AdminLogic')->get_current_member();
        $this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));


        $this->logic->upload($filepath, $_FILES['file']);

        $this->render_json(array('code'=>200, 'data'=>array('filepath'=>$filepath)));  
    }


}