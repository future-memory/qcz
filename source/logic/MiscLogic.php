<?php
/**
 * @property ThreadDao $threadDao
 *
 */
class MiscLogic extends Logic
{
	public function __construct()
	{
		$this->_dao = ObjectCreater::create('MiscDataDao');
	}

	public function fetch_subject($key)
	{
		return ObjectCreater::create('MiscSubjectDao')->fetch_subject($key);
	}

	public function get_data_by_keys($keys, $need_tid=false)
	{
		if(empty($keys)){
			return array();
		}

		$data = array();
		$info = ObjectCreater::create('MiscSubjectDao')->fetch_all($keys);

		$env  = $this->get_client_envirnment();

		$max_dateline = 0;

		foreach($keys as $key){
			$limit = isset($info[$key]['random']) && $info[$key]['random'] ? 10000 : (isset($info[$key]) && isset($info[$key]['show_count']) ? intval($info[$key]['show_count']) : 10);
			$list  = $this->_dao->get_list($key, 0, $limit, $env);
			if(isset($info[$key]['random']) && $info[$key]['random']){
				$show_count = isset($info[$key]) && isset($info[$key]['show_count']) ? intval($info[$key]['show_count']) : 10;
				shuffle($list);
				$list = array_slice($list, 0, $show_count);
			}

			$data[$key] = array();

			foreach($list as $item){
				if ((int)$info[$key]['expire'] > 0 && TIMESTAMP >= intval($item['expire'])) {
					continue;
				}
				if ((int)$info[$key]['start_time'] > 0 && TIMESTAMP < intval($item['start_time'])) {
					continue;
				}
				$data[$key][] = $this->format_data($item, $need_tid);
				$max_dateline = $max_dateline > $item['dateline'] ? $max_dateline : intval($item['dateline']);
			}
		}

		return $data;		
	}


	/**
	 * 根据imei获取环境  0 线上环境 1灰度环境
	 */
	public  function get_client_envirnment()
	{
		$imei = isset($_SERVER['HTTP_IMEI']) ? $_SERVER['HTTP_IMEI'] : HelperUtils::get_param('imei'); 
		if(!empty($imei)){
			$imeis = ObjectCreater::create('AppSettingDao')->get('test_imei');
			$imei_arr = explode(',', $imeis['svalue']);
			return in_array($imei, $imei_arr) ? 1 : 0;
		}
		return 0;
	}


	public function format_data($item, $need_tid=false)
	{
		$row = array(
			'url'        => $item['url'] ? $item['url'] : DOMAIN . 'template/upgrade/upgrade.html',
			'pagepath'   => $item['pagepath'] ? $item['pagepath'] : $this->get_pagepath($item['url']),
			'order'      => $item['order'],
			'title'      => $item['title'],
			'expire'     => intval($item['expire']),
			'start_time' => intval($item['start_time']),
			'img'        => $item['pic'] ? HelperUtils::get_pic_url($item['pic'], 'app') : '',
			'extdata'    => $item['extdata'] ? @json_decode($item['extdata'], true) : '',
			'server_timestamp' => TIMESTAMP,
		);

		if (strpos($item['pagepath'], '?tagid=') !== false) {
			$url_array = parse_url($item['pagepath']);
			if (isset($url_array['query']) && $url_array['query']) {
				$params_str = $url_array['query'];
				foreach (explode('&', $params_str) as $value) {
					$tagid_id = explode('=', $value);
					if ($tagid_id && is_array($tagid_id) && isset($tagid_id[0]) && strpos($tagid_id[0], 'tagid') !== false) {
						$tagid = (int)$tagid_id[1];
						$forum_tag = ObjectCreater::create('ForumTagDao')->fetch($tagid);
						if ($forum_tag) {
							$row['forum_tag'] = array(
								'tagid' => $forum_tag['tagid'],
								'tagname' => $forum_tag['tagname'],
								'tagimg' => $forum_tag['tagimg'],
								);
						}
						break;
					}
				}
			}
		}

		if($need_tid){//字段中没有tid app端控制跳转需要用到tid
			preg_match('/tid=(\d{1,10})/iu', $item['url'], $m);
			if(empty($m[1])){
				preg_match('/tid=(\d{1,10})/iu', $item['pagepath'], $m);
			}
			$row['tid']  =  isset($m[1]) ? intval($m[1]) : '';
			if($row['tid']  > 0){
				$this->threadDao = ObjectCreater::create('ThreadDao');
				$thread = $row['tid']>0 ? $this->threadDao->fetch($row['tid']) : array();
				if(!empty($thread)){
					$row['avatar'] = HelperUtils::avatar($thread['authorid']);
					$row['author'] = $thread['author'];
					$row['replies'] = $thread['replies'];
				}
			}
		}

		return $row;
	}

	//获取数据列表
	public function get_data_by_key($key)
	{
		$info = ObjectCreater::create('MiscSubjectDao')->fetch($key);
		$env  = $this->get_client_envirnment();

		$limit = isset($info['random']) && $info['random'] ? 100 : (isset($info) && isset($info['show_count']) ? intval($info['show_count']) : 10);

		$list = $this->_dao->get_list($key, $start, $limit, $env);

		if(isset($info['random']) && $info['random']){
			$show_count = isset($info) && isset($info['show_count']) ? intval($info['show_count']) : 10;
			shuffle($list);
			$list = array_slice($list, 0, $show_count);
		}

		foreach ($list as $key => $value) {
			if ((int)$info['expire'] > 0 && TIMESTAMP >= intval($value['expire'])) {
				unset($list[$key]);
				continue;
			}
			if ((int)$info['start_time'] > 0 && TIMESTAMP < intval($value['start_time'])) {
				unset($list[$key]);
				continue;
			}

			$list[$key]['pic'] = HelperUtils::get_pic_url($value['pic'], 'app');
		}
		
		return $list;	
	}

}
