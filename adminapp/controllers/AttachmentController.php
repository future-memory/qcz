<?php

class AttachmentController extends AdminController 
{
 
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
        $module   = $this->get_param('module', 'forum');
        $member = ObjectCreater::create('AdminLogic')->get_current_member();
        //判断是否登录
        $this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

        //$group   = ObjectCreater::create('GroupLogic')->get_cur_group_fields();
        //$maxsize = isset($group['maxattachsize']) && $group['maxattachsize'] ? $group['maxattachsize'] : 2*1024*1024 ;
        $maxsize = 5 * 1024 * 1024;

        $data = ObjectCreater::create('HelperUpyun')->upyun_sign($member['uid'], $module, $file_num, $maxsize);

        $this->render_json(array('code'=>200,'data'=>$data));        

    }

}