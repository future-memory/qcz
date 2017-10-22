<?php

class HelperSeo {

	private static $trunsform_tmp = array();

	public static function get_seosetting($page, $data = array(), $defset = array()) 
	{
		$setting_logic = ObjectCreater::create('SettingLogic');
		$bbname   = $setting_logic->get('bbname');

		$searchs = array('{bbname}');
		$replaces = array($bbname);

		$titletext       = isset($defset['seotitle']) ? $defset['seotitle'] : null;
		$descriptiontext = isset($defset['seodescription']) ? $defset['seodescription'] : null;
		$keywordstext    = isset($defset['seokeywords']) ? $defset['seokeywords'] : null;
		if(!$titletext){
			$seotitles = $setting_logic->get('seotitle');
			$seotitles = is_array($seotitles) ? $seotitles : unserialize($seotitles);
			$titletext = $seotitles[$page];		
		}
		if(!$descriptiontext){
			$seodescriptions = $setting_logic->get('seodescription');
			$seodescriptions = is_array($seodescriptions) ? $seodescriptions : unserialize($seodescriptions);
			$descriptiontext = $seodescriptions[$page];		
		}
		if(!$keywordstext){
			$seokeywords = $setting_logic->get('seokeywords');
			$seokeywords = is_array($seokeywords) ? $seokeywords : unserialize($seokeywords);
			$keywordstext = $seokeywords[$page];		
		}

		preg_match_all("/\{([a-z0-9_-]+?)\}/", $titletext.$descriptiontext.$keywordstext, $pageparams);


		$seotitle = $seodescription = $seokeywords = '';

		if($pageparams) { 
			foreach($pageparams[1] as $var) {
				$searchs[] = '{'.$var.'}';
				if($var == 'page') {
					$data['page'] = $data['page'] > 1 ? HelperLang::lang('core', 'page', array('page' => $data['page'])) : '';
				}
				$replaces[] = isset($data[$var]) ? strip_tags($data[$var]) : '';
			}
			if($titletext) {
				$seotitle = HelperSeo::strreplace_strip_split($searchs, $replaces, $titletext);
			}

			if($descriptiontext) {
				$seodescription = HelperSeo::strreplace_strip_split($searchs, $replaces, $descriptiontext);
			}
			if($keywordstext) {
				$seokeywords = HelperSeo::strreplace_strip_split($searchs, $replaces, $keywordstext);
			}
             
		}
		return array($seotitle, $seodescription, $seokeywords);
	}


	public static function strreplace_strip_split($searchs, $replaces, $str)
	{
		$searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
		$replacespace = array('-', ',', '|', ' ', '_');
		return trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $str)), ' ,-|_');
	}

	public static function get_title_page($navtitle, $page)
	{
		if($page > 1) {
			$navtitle .= ' - '.HelperLang::lang('core', 'page', array('page' => $page));
		}
		return $navtitle;
	}

	public static function get_related_link($extent) 
	{
		$cache_logic = ObjectCreater::create('CacheLogic');
		$relatedlinks = $cache_logic->load_syscache('relatedlink');

		$allextent = array('article' => 0, 'forum' => 1, 'group' => 2, 'blog' => 3);
		$links = array();
		if($relatedlinks['relatedlink'] && isset($allextent[$extent])) {
			foreach($relatedlinks['relatedlink'] as $link) {
				$link['extent'] = sprintf('%04b', $link['extent']);
				if($link['extent'][$allextent[$extent]] && $link['name'] && $link['url']) {
					$links[] = HelperUtils::daddslashes($link);
				}
			}
		}
		rsort($links);
		return $links;
	}

	public static function parse_related_link($content, $extent) 
	{
		$cache_logic = ObjectCreater::create('CacheLogic');
		$relatedlinks = $cache_logic->load_syscache('relatedlink');

		$allextent = array('article' => 0, 'forum' => 1, 'group' => 2, 'blog' => 3);
		if($relatedlinks['relatedlink'] && isset($allextent[$extent])) {
			$searcharray = $replacearray = array();
			foreach($relatedlinks['relatedlink'] as $link) {
				$link['extent'] = sprintf('%04b', $link['extent']);
				if($link['extent'][$allextent[$extent]] && $link['name'] && $link['url']) {
					$searcharray[$link[name]] = '/('.preg_quote($link['name']).')/i';
					$replacearray[$link[name]] = "<a href=\"$link[url]\" target=\"_blank\" class=\"relatedlink\">$link[name]</a>";
				}
			}
			if($searcharray && $replacearray) {
				$content = preg_replace("/(<script\s+.*?>.*?<\/script>)|(<a\s+.*?>.*?<\/a>)|(<img\s+.*?[\/]?>)|(\[attach\](\d+)\[\/attach\])/ies", "HelperSeo::base64_transform('encode', '<relatedlink>', '\\1\\2\\3\\4', '</relatedlink>')", $content);
				$content = preg_replace($searcharray, $replacearray, $content, 1);
				$content = preg_replace("/<relatedlink>(.*?)<\/relatedlink>/ies", "HelperSeo::base64_transform('decode', '', '\\1', '')", $content);
			}
		}
		return $content;
	}


	public static function base64_transform($type, $prefix, $string, $suffix) 
	{
		if($type == 'encode') {
			self::$trunsform_tmp[] = base64_encode(str_replace("\\\"", "\"", $string));
			return $prefix.(count(self::$trunsform_tmp) - 1).$suffix;
		} elseif($type == 'decode') {
			return $prefix.base64_decode(self::$trunsform_tmp[$string]).$suffix;
		}
	}
}

?>