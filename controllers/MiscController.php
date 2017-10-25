<?php
/**
 * @property MiscLogic $logic
 *
 */
class MiscController extends BaseController 
{

    public function __construct()
    {
        $this->logic = ObjectCreater::create('MiscLogic');
    }

	/**
	 * @api {get} /index.php?mod=misc&action=data&key=xxx 配置数据
	 * @apiName data
	 * @apiGroup Misc
	 * @apiVersion 2.0.1
	 * @apiDescription 配置数据
	 * @apiParam {String} keys 配置的keys，多个使用英文逗号分隔
	 * @apiParam {Int} need_tid 是否需要帖子数据
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 详细数据
	 * @apiSuccessExample {json} Response 200
	 *
     *{
     *  "code": 200, 
     *  "data": {
     *    "list":  
     		[
	 *        {
	 *        "url": "/index.php?mod=thread&action=tid=2493275&app=thread",
	 *        "order": "1",
	 *        "title": "我心中的佛祖",
	 *        "expire": 0,
	 *        "start_time": 0,
	 *        "server_timestamp": 1492743700,
	 *        "img": "",
	          "extdata": {                // 拓展字段，如果没有，则为空字符串
	            "background": "9.png", 
	            "app": "1.1.1"
	          }
	 *        "tid": "2493275",           //以下内容 参数need_tid=1时 才有
	 *        "replies": "1206"           //帖子回复数
	 *        },
     *     ]
     *  }
     *}
	 * 
	*/
	public function data()
	{
		$key  = $this->get_param('key');
		$this->throw_error(!$key, array('code'=>400, 'message'=>'请求错误！'));

		$list = $this->logic->get_data_by_key($key);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));
	}


	/**
	 * @api {get} /index.php?mod=misc&action=data&keys=xxx 配置数据
	 * @apiName data
	 * @apiGroup Misc
	 * @apiVersion 2.0.1
	 * @apiDescription 配置数据
	 * @apiParam {String} keys 配置的keys，多个使用英文逗号分隔
	 * @apiParam {Int} need_tid 是否需要帖子数据
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 详细数据
	 * @apiSuccessExample {json} Response 200
	 *
     *{
     *  "code": 200, 
     *  "data": {
     *    "list": {
     *      "test_key3": [
	 *        {
	 *        "url": "/index.php?mod=thread&action=tid=2493275&app=thread",
	 *        "order": "1",
	 *        "title": "我心中的佛祖",
	 *        "expire": 0,
	 *        "start_time": 0,
	 *        "server_timestamp": 1492743700,
	 *        "img": "",
	          "extdata": {                // 拓展字段，如果没有，则为空字符串
	            "background": "9.png", 
	            "app": "1.1.1"
	          }
	 *        "tid": "2493275",           //以下内容 参数need_tid=1时 才有
	 *        "replies": "1206"           //帖子回复数
	 *        },
     *      ]
     *    }
     *  }
     *}
	 * 
	*/
	public function data_list()
	{
		$keys     = $this->get_param('keys', '');
		$this->throw_error(empty($keys), array('code'=>400, 'message'=>'请求错误！'));
		$keys     = is_array($keys) ? $keys : explode(',', $keys);
		$need_tid = $this->get_param('need_tid');
		$this->throw_error(empty($keys) || count($keys)>10, array('code'=>400, 'message'=>'请求错误！'));

		$list = $this->logic->get_data_by_keys($keys, $need_tid);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));
	}

	/**
	 * @api {post} /index.php?mod=misc&action=read_push 将推送消息置为已读
	 * @apiName readpush
	 * @apiGroup Misc
	 * @apiVersion 2.0.0
	 * @apiDescription 将推送消息置为已读
	 * @apiParam {String} msg_id    
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 * 
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function read_push()
	{
		$push_type = $this->get_param('push_type');
		$msg_id    = $this->get_param('msg_id', null);

		$this->throw_error(!$msg_id, array('code'=>400, 'message'=>'参数错误！'));


		$result = self::$WEB_SUCCESS_RT;
        try{
        	$push_logic = new PushLogic();
			$push_logic->set_msg_status($msg_id, 2);

        } catch (BizException $e) {
            $result = $e;
        }

        $this->render_json($result);
	}	 


}