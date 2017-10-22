<?php

$_config = array();

//oauth config

$_config['weibo'] = array(
	'appid'        => '439445218',//'3341792891',
	'secret'       => '27a7ed80f79a17b393bd2e72f0abc996',//'3978d1e4597ab49c86db21d12fd4cb50',
	'callback_url' => DOMAIN.'index.php?mod=oauth&action=wbcallback',
);

$_config['weixin'] = array(
	'appid'        => 'wx65a7fb2fec33bab0',
	'secret'       => 'bd43281e515c2249e97610b52306cf74',
	'callback_url' => DOMAIN.'index.php?mod=oauth&action=wxcallback',
);

return $_config;