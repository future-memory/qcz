<?php

/*
* @author: 4061470@qq.com
*/

class HelperResult
{
    static public function ensureFalse($result, $errno, $msg_param = null)
    {
        if($result !== false)
        {
            throw new BizException(is_array($msg_param) ? vsprintf(Prompts::$ErrDescription[$errno], $msg_param) : Prompts::$ErrDescription[$errno], $errno);
        }
        return $result;
    }
}

class BizException extends Exception
{
    public function __construct($errmsg, $errno) 
    {
        parent::__construct($errmsg, $errno);
    }
}
