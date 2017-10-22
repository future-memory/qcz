<?php

class Logic
{
	public static $lang = null;
    protected     $_dao = null;

    //兼容 提示
    public static function throw_exception($result, $flag, $extra_data=null)
    {
        if($result){
            $exception = array('code'=>999, 'message'=>'发生未知错误');
        	if(is_array($flag) && isset($flag['message']) && isset($flag['code'])){
                $exception = $flag;
        	}
        	if(is_array(self::$lang) && !empty(self::$lang) && is_numeric($flag)){
                $exception['code']    = $flag;
                $exception['message'] = self::$lang[$flag];
        	}
            BizException::throw_exception($result, $exception, $extra_data);
        }
        return $result;
    }

     //直接调用dao
     public function __call($name, $arguments) 
     {
        if (!$this->_dao) {
            return false;
        }

        if(!method_exists($this->_dao, $name)){
            return false;
        }

        return call_user_func_array(array($this->_dao, $name), $arguments);
     }
    
}