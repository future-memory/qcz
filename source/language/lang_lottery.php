<?php

class lang_lottery{

  //错误码
  const ERROR_PARAM       = 400;
  const ERROR_UNLOGIN     = 401;
  const ERROR_NO_CHANCE   = 402;
  const ERROR_CANNOT_PAY  = 403;  
  const ERROR_NOT_FOUND   = 404;
  const ERROR_NO_PHONE    = 407;
  const ERROR_CHEAT       = 408;

  const ERROR_ENABLE      = 301;
  const ERROR_NOT_START   = 302;
  const ERROR_END         = 303;

  const ERROR_Q_EMPTY     = 201;
  const ERROR_NOQ_SAVE    = 203;
  const ERROR_MISS        = 204;
  const ERROR_WIN_LIMIT   = 205;
  const ERROR_CANNOT_SAVE = 207;

  const ERROR_SYS_BUSY    = 502;

    //定义错误及提示信息输出
    public static $ErrDescription = array(
      self::ERROR_PARAM       => '参数错误！',
      self::ERROR_UNLOGIN     => '请先登录！',
      self::ERROR_NOT_FOUND   => '请求错误！',
      self::ERROR_ENABLE      => '活动未开启！',
      self::ERROR_NOT_START   => '请耐心等待，活动未开始！',
      self::ERROR_END         => '来晚了一步，活动已经结束！',
      self::ERROR_SYS_BUSY    => '大家太热情了，服务器忙不过来，请稍后再试！',
      self::ERROR_CHEAT       => '很遗憾，您没有中奖，请继续努力！',
      self::ERROR_WIN_LIMIT   => '很遗憾，您没有中奖，请继续努力！',
      self::ERROR_MISS        => '很遗憾，您没有中奖，请继续努力！',
      self::ERROR_NO_CHANCE   => '您的抽奖机会已用完！',
      self::ERROR_CANNOT_PAY  => '煤球余额不足！',
      self::ERROR_NO_PHONE    => '您的账号还未绑定手机号，无法参与活动。',
      self::ERROR_Q_EMPTY     => '很遗憾，您没有中奖，请继续努力！',
      self::ERROR_CANNOT_SAVE => '很遗憾，您没有中奖，请继续努力！',
      self::ERROR_NOQ_SAVE    => '很遗憾，您没有中奖，请继续努力！',            
    );

}
