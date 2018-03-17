<?php

//是否开启debug模式
define('DEBUG_MOD', FALSE);

define('TIMESTAMP', time());
//用户验证串 密钥
define('AUTH_KEY', 'e2a7edKLD31Xkmgo@uth');


//分表 帖子
define('IS_POST_SPLIT_TABLE', 1);		// 帖子表是否分表		1 分表	0 不分表
define('POST_SPLIT_TABLE_COUNT', 10);	// 帖子表分为几个表,  	10个表

define('STICK_REPLIES_LIMIT', 10);	// 热门回复 

define('AD_SIGN_KEY', '￥a@%&&2d3Dggg');



//错误页
define("PAGE_NOT_FOUND", DOMAIN.'html/error/not_found.html');
define("PAGE_SYS_ERROR", DOMAIN.'html/error/server_busy.html');

//附件域名
define('FILE_DOMAIN', 'https://file.shop.com/');	// 图片cdn 加速地址
//静态资源域名
define('RES_DOMAIN', 'https://res.shop.com/');
