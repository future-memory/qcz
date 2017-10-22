<?php
/**
 * @property SiteLogic $logic
 *
 */
class SiteController extends BaseController 
{

    public function __construct()
    {
        $this->logic = ObjectCreater::create('SiteLogic');
    }

	/**
	 * @api {get} /index.php?mod=site&action=info&domain=xxx 配置数据
	 * @apiName siteinfo
	 * @apiGroup Site
	 * @apiVersion 2.0.1
	 * @apiDescription 配置数据
	 * @apiParam {String} domain 
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 详细数据
	 * @apiSuccessExample {json} Response 200
	 *
     *{
     *  "code": 200, 
     *  "data": {
     *  }
     *}
	 * 
	*/
	public function info()
	{
		$domain = $this->get_param('domain');
		$this->throw_error(!$domain, array('code'=>400, 'message'=>'请求错误！'));

		$data = $this->logic->fetch($domain);
		$data = array('domain'=>$data['domain'],'name'=>$data['name'],'intro'=>$data['intro']);


		$this->render_json(array('code'=>200, 'data'=>$data));
	}

}