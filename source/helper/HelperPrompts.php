<?php

/*
* @author: 4061470@qq.com
*/

class HelperPrompts{

	//AJAX 错误码
	const AJAX_ERROR_CODE                       = 400;
	const AJAX_ERROR_CODE_UNKONW                = 4001;
	const WEB_ERROR_PARAM                       = 4002;

	const WEB_ERROR_UNREG                       = 4011;
	const WEB_ERROR_PPASSWORD_WRONG             = 4012;

	const WEB_ERROR_SQL 						= 5001;

	const AJAX_SUCCESS_CODE                     = 200;



    //定义错误及提示信息输出，供BizResult用
    public static $ErrDescription = array(
		self::WEB_ERROR_PARAM                       => '参数错误',
		self::AJAX_ERROR_CODE                       => '请求发生错误',
		self::AJAX_ERROR_CODE_UNKONW                => '发生未知错误',
		self::WEB_ERROR_SQL               			=> 'SQL语句错误！请联系管理员。',
		self::WEB_ERROR_UNREG						=> 'Email未注册',
		self::WEB_ERROR_PPASSWORD_WRONG 			=> '密码错误,请重试!',
    );

    //定义AJAX默认返回成功值
    public static $WEB_SUCCESS_RT = array(
    	'result' => self::AJAX_SUCCESS_CODE,
    	'reason' => 'success',
    );


}
