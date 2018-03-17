<?php

//是否开启debug模式
define('DEBUG_MOD', TRUE);

define('TIMESTAMP', time());
//用户验证串 密钥
define('AUTH_KEY', 'e2a7edKLD31Xkmgo@uth');
// 密码盐
define('PASS_SLAT', 'e2a7edKLD31Xkmgo@$$');


//错误页
define("PAGE_NOT_FOUND", DOMAIN . 'html/error/not_found.html');
define("PAGE_SYS_ERROR", DOMAIN . 'html/error/server_busy.html');

//附件域名
define('FILE_DOMAIN', 'https://file.shop.com/');	// 图片cdn 加速地址 http://oy9ikh8hd.bkt.clouddn.com/  http://localhost/qcz/adminapp/data/attach/
//静态资源域名
define('RES_DOMAIN', 'https://res.shop.com/');


define('LOGIN_TIMES_TO_VERIFY', 5);
define('LOGIN_TIMES_TO_FORBIDDEN', 10);



define('QN_ACCESS', 'Ktb0TXPPQRTs-GGqEc0_ftpGAKEp874swZia3s5s');
define('QN_SECRET', 'uC6ZlPi4yaF9erNDOte9tTh02Dl3EGrdgPDv7F_b');
define('QN_EXPIRE', 60);
