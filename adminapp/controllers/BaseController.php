<?php

class BaseController 
{
	private $_action = 'index';
	protected $logic = null;
	//是否统一处理异常
	protected $handle_exception = true;

	public static $WEB_SUCCESS_RT = array('code'=>200, 'message'=>'success');

	//验证参数
    public function verify_param($array_param, $rules, $is_throw_exception = true)
    {
    	$result =  ObjectCreater::create('Validator')->validate($rules, $array_param);
    	BizException::throw_exception(!$result && $is_throw_exception, array('code'=>400, 'message'=>'参数错误！'));
    	return $result;
	}

	//controller action执行前执行   可以用于权限判断等
	public function before_action($action)
	{
		//允许跨域
		$actions = HelperConfig::get_config('csrf::actions');
		if(is_array($actions) && in_array($action, $actions) && isset($_SERVER['HTTP_REFERER']) && HelperBiz::check_redirect_url($_SERVER['HTTP_REFERER'])){
			header('Access-Control-Allow-Credentials: true');
			$url_info = parse_url($_SERVER['HTTP_REFERER']);
			header('Access-Control-Allow-Origin: '.$url_info['host']);
		}

		return true;
	}

	//controller action执行后执行   可以用于日志保存、运行统计等
	public function after_action($controller, $data=array())
	{
    	$db = Nice::app()->getComponent('DataBase');
    	if(isset($_GET['query_debug'])){
    		var_dump($db->sqls);
    	}
		return true;
	}

	//执行action
	public function run($action)
	{
		if($this->before_action($action))
		{
			if(!is_callable(array($this, $action))){
				throw new CoreException('Your request is invalid.');
			}

			//统一处理异常
			if($this->handle_exception){
		       try{
		        	$this->$action();
		        } catch (BizException $e) {	
		        	BizException::handle_exception($e, $this);
					exit();
		        }
			}else{
		        $this->$action();
	    	}
			
			$this->after_action($this);
		}
	}

	//输出错误  json
	public function throw_error($throw, $data, $plus=array(), $callback=null)
	{
		if($throw){
			$data = is_array($plus) && !empty($plus) ? array_merge($data, $plus) : $data;
			$_callback = (string)$this->get_param('callback', '');
			if (!empty($_callback)) {
				$this->render_jsonp($data, $_callback);
				exit();
			}
			$this->render_json($data);
			exit();
		}
	}

	//显示错误页
	public function error_page($show, $data)
	{
		if($show){
			$data['back_url'] = !isset($data['back_url']) || !$data['back_url'] ? (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null) : $data['back_url'];
			
			$this->render('error_page.php', $data);
			exit();
		}
	}	

	//获取参数
	public function get_param($key, $default = null)
	{
		return Nice::app()->getComponent('Request')->getParam($key, $default);
	}

	//判断请求是否post
	public function is_post()
	{
		return Nice::app()->getComponent('Request')->isPostRequest();
	}

	public function getUrl() {
		return Nice::app()->getComponent('Request')->getUrl();
	}

	//渲染json 带callback时为jsonp
	public function render_json($data, $filter=false, $callback=null, $plus=array())
	{	
		if($data instanceof Exception){
			$data = array(
				'code'    => $data->getCode(),
				'message' => $data->getMessage(),
			);
		}

		header("Content-type: text/json; charset=utf-8");

		$data = is_array($plus) && !empty($plus) ? array_merge($data, $plus) : $data;

		if(method_exists($this, 'after_action')){
			$this->after_action($data);
		}

		$data = $filter ? HelperUtils::jsonHtmlFilter($data) : $data;

		$_callback = (string)$this->get_param('callback', '');
		if (!empty($_callback)) {
			echo $_callback.'('.json_encode($data).')';
			exit();
		}
		echo json_encode($data);
		exit();
	}

	//渲染界面
	public function render($view, $data=null, $return=false)
	{
		if(($viewFile=$this->get_template_file($view))!==false){
			$output = $this->render_file($viewFile, $data, true);
			if($return){
				return $output;
			}else{
				echo $output;
			}
		}else{
			$controller = '';
			$message    = sprintf('%s cannot find the requested template %s.', $controller, $view);
			throw new CoreException($message);
		}
	}

	//获取模板文件
	public function get_template_file($view)
	{
		if(empty($view)){
			return false;
		}

		$controller = strtolower(Nice::app()->getController());
		
		//应用各自的模板
		$tpl_path   = Nice::app()->getTemplatePath();
		$tpl_file   = $tpl_path.DIRECTORY_SEPARATOR.$controller.DIRECTORY_SEPARATOR.$view;
		if(is_file($tpl_file)){
			return $tpl_file; 
		}

		//共用的模板
		$tpl_path = Nice::app()->getDefaultTemplatePath();
		$tpl_file = $tpl_path.DIRECTORY_SEPARATOR.$controller.DIRECTORY_SEPARATOR.$view;
		if(is_file($tpl_file)){
			return $tpl_file; 
		}		

		return false;
	}

	//内容渲染到模板文件中
	public function render_file($_viewFile_, $_data_=null, $_return_=false)
	{
		if(is_array($_data_)){
			extract($_data_,EXTR_PREFIX_SAME,'data');
		}else{
			$data=$_data_;
		}

		if(!is_file($_viewFile_)){
			$message = sprintf('Cannot find the requested template %s.', $_viewFile_);
			throw new CoreException($message);
		}

		if($_return_){
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}else{
			require($_viewFile_);
		}
	}

	//widget 工厂
	public function create_widget($class_name,$properties=array())
	{
		$widget = new $class_name($this);
		//属性赋值
		foreach($properties as $name=>$value){
			$widget->$name = $value;
		}
		return $widget;
	}

	//调用 widget
	public function widget($class_name, $properties=array())
	{
		$widget = $this->create_widget($class_name, $properties);
		$widget->run();

		return $widget;
	}
	/**
	 * jsonp
	 * @param unknown $data
	 * @param string $callback
	 * @param boolean $filter
	 * @param boolean $with_time
	 * @param boolean $with_login
	 */
	public function render_jsonp($data, $callback, $filter=false, $with_time=true)
	{
		if($data instanceof Exception)
		{
			$data = array(
					'code'    => $data->getCode(),
					'message' => $data->getMessage(),
			);
		}
	
		if(method_exists($this, 'after_action')){
			$this->after_action($data);
		}
		if($with_time){
			$data['timestamp'] = TIMESTAMP;
		}
	
		if($filter){
			array_walk_recursive($data, "htmlspecialchars");
		};
		header("Access-Control-Allow-Credentials: true");
		header("Content-type: text/json; charset=utf-8");
	
		echo htmlspecialchars($callback).'('.json_encode($data).')';
		exit();
	}

}
