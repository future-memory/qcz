<?php


class HelperBbcode 
{
	private static $delattach   = array();
	private static $post_attach = array();
	private static $codes       = array(
		'pcodecount' => 0,
		'codehtml'   => array(),
		'audio'      => array(),
		'video'      => array(),
		'media'      => array(),
		'image'      => array(),
		'attach'     => array()
	);

	public static function complie($message, $tid = 0, $pid = 0, $length = 0, $allowimg = true) 
	{
		if((strpos($message, '[/code]')!== FALSE || strpos($message, '[/CODE]')) !== FALSE) {
			$message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/is", "", $message);
			$message = preg_replace_callback("/\s?\[code\](.+?)\[\/code\]\s?/is", function($matches){ return ''; }, $message);
		}

		$msglower = strtolower($message);
		$htmlon   = 0;
		$message  = htmlspecialchars($message);
		$message  = self::fparsesmiles($message);

		if(strpos($msglower, 'attach://') !== FALSE) {
			$message = preg_replace("/attach:\/\/(\d+)\.?(\w*)/i", '', $message);
		}

		if(strpos($msglower, 'ed2k://') !== FALSE) {
			$message = preg_replace("/ed2k:\/\/(.+?)\//", '', $message);
		}

		if(strpos($msglower, '[/url]') !== FALSE) {
			$message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/is", function($matches) { return self::parseurl($matches[1], $matches[5], $matches[2]); }, $message);
		}

		if(strpos($msglower, '[/i]') !== FALSE) {
			$message = preg_replace("/\s*\[i=s\][\n\r]*(.+?)[\n\r]*\[\/i\]\s*/is", '', $message);
		}

		$message = str_replace('[/p]', "\n", $message);
		$message = str_replace(array(
			'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]',
			'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
			'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]', '[/game_score]'
			), '', preg_replace(array(
			"/\[color=([#\w]+?)\]/i",
			"/\[color=(rgb\([\d\s,]+?\))\]/i",
			"/\[backcolor=([#\w]+?)\]/i",
			"/\[backcolor=(rgb\([\d\s,]+?\))\]/i",
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
			"/\[font=([^\[\<]+?)\]/i",
			"/\[align=(left|center|right)\]/i",
			"/\[float=left\]/i",
			"/\[float=right\]/i",
			"/\[game_score\]/i"
			), '', $message));

		if(strpos($msglower, '[/p]') !== FALSE) {
			$message = preg_replace("/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i", "<p style=\"line-height:\\1px;text-indent:\\2em;text-align:left;\">", $message);
			$message = str_replace('[/p]', '</p>', $message);
		}

		if(strpos($msglower, '[/quote]') !== FALSE) {
			$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", '', $message);
		}
		if(strpos($msglower, '[/free]') !== FALSE) {
			$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", '', $message);
		}

		//$bbcodes[-$allowbbcode] 为null
		// $bbcodes = ObjectCreater::create('CacheLogic')->load_syscache($key);
		// if(is_array($bbcodes) && !empty($bbcodes) &&  isset($bbcodes[-$allowbbcode])) {
		// 	$message = preg_replace($bbcodes[-$allowbbcode]['searcharray'], '', $message);
		// }

		if(strpos($msglower, '[/hide]') !== FALSE) {
			preg_replace_callback("/\[hide.*?\]\s*(.*?)\s*\[\/hide\]/is", function($matches) { return self::hideattach($matches[1]); }, $message);
			if(strpos($msglower, '[hide]') !== FALSE) {
				$message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", '', $message);
			}
			if(strpos($msglower, '[hide=') !== FALSE) {
				$message = preg_replace("/\[hide=(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", '', $message);
			}
		}

		if(strpos($msglower, '[/email]') !== FALSE) {
			$message = preg_replace_callback("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/is", function($matches) { return self::fparseemail($matches[1], $matches[4]); }, $message);

		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			$message = preg_replace_callback("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is", function($matches) { return self::fparsetable($matches[1], $matches[2], $matches[3]); }, $message);

			if(++$nest > 4) break;
		}

		if(strpos($msglower, '[/media]') !== FALSE) {
			$message = preg_replace_callback("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", function($matches) { return self::fparsemedia($matches[1], $matches[2]); }, $message);

		}
		if(strpos($msglower, '[/audio]') !== FALSE) {
			$post['message'] = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", function($matches) { return self::fparseaudio($matches[2]); }, $post['message']);

		}
		if(strpos($msglower, '[/flash]') !== FALSE) {
			$message = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", function($matches) { return self::fparseflash($matches[4]); }, $message);

		}

		//$parsetype != 1 && 
		if(strpos($msglower, '[swf]') !== FALSE) {
			$message = preg_replace_callback("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/is", function($matches) { return self::bbcodeurl($matches[1], ' <img src="'.STATICURL.'image/filetype/flash.gif" align="absmiddle" alt="" /> <a href="{url}" target="_blank">Flash: {url}</a> '); }, $message);

		}
		$flag  = $length ? 1 : 0;
		$extra = $tid ? "onclick=\"changefeed($tid, $pid, $flag, this)\"" : '';

		if(strpos($msglower, '[/img]') !== FALSE) {
			$message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", function($matches) use($allowimg, $extra) { return $allowimg ? self::fparseimg($matches[1], $extra) : ''; }, $message);

			$message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", function($matches) use($allowimg, $extra) { return $allowimg ? self::fparseimg($matches[3], $extra) : ''; }, $message);
		}

		if($tid && $pid) {
			self::$post_attach = ObjectCreater::create('AttachmentNDao')->fetch_all_by_id(HelperBiz::get_attach_tableid($tid), 'pid', $pid);
			foreach(self::$post_attach as $aid => $attach) {
				if(!empty(self::$delattach) && in_array($aid, self::$delattach)) {
					continue;
				}
				$message .= '[attach]'.$attach['aid'].'[/attach]';
				$message = preg_replace("/\[attach\]$attach[aid]\[\/attach\]/i", self::fparseattach($attach['aid'], $length, $extra), $message, 1);
			}
		}

		if(strpos($msglower, '[/attach]') !== FALSE) {
			$message = preg_replace_callback("/\[attach\]\s*([^\[\<\r\n]+?)\s*\[\/attach\]/is", function($matches) { return ''; }, $message);
		}

		$message = self::clearnl($message);

		if($length) {
			$sppos = strpos($message, chr(0).chr(0).chr(0));
			if($sppos !== false) {
				$message = substr($message, 0, $sppos);
			}
			$checkstr = HelperUtils::cutstr($message, $length, '');
			if(strpos($checkstr, '[') && strpos(strrchr($checkstr, "["), ']') === FALSE) {
				$length = strpos($message, ']', strrpos($checkstr, strrchr($checkstr, "[")));
			}
			$message = HelperUtils::cutstr($message, $length+1, ' <a href="javascript:;" class="flw_readfull xi2 xs1"'.$extra.'>'.HelperLang::lang('space', 'follow_view_fulltext').'</a>');
		} elseif($allowimg && !empty($extra)) {
			$message .= '<div class="ptm cl"><a href="javascript:;" class="flw_readfull y xi2 xs1"'.$extra.'>'.HelperLang::lang('space', 'follow_retract').'</a></div>';
		}

		for($i = 0; $i <= self::$codes['pcodecount']; $i++) {
			$code = '';
			if(isset(self::$codes['codehtml'][$i]) && !empty(self::$codes['codehtml'][$i])) {
				$code = self::$codes['codehtml'][$i];
			} elseif(!$length) {
				if(isset(self::$codes['audio'][$i]) && !empty(self::$codes['audio'][$i])) {
					$code = self::$codes['audio'][$i];
				} elseif(isset(self::$codes['video'][$i]) && !empty(self::$codes['video'][$i])) {
					$code = self::$codes['video'][$i];
				} elseif(isset(self::$codes['media'][$i]) && !empty(self::$codes['media'][$i])) {
					$code = self::$codes['media'][$i];
				} elseif(isset(self::$codes['image'][$i]) && !empty(self::$codes['image'][$i])) {
					$code = self::$codes['image'][$i];
				} elseif(isset(self::$codes['attach'][$i]) && !empty(self::$codes['attach'][$i])) {
					$code = self::$codes['attach'][$i];
				}
			}
			$message = str_replace("[\tD_$i\t]", $code, $message);
		}

		$message = self::clearnl($message);
		unset($msglower);

		if($length) {
			$count = 0;
			$imagecode = $mediacode = $videocode = $audiocode = $mediahtml = '';
			for($i = 0; $i <= self::$codes['pcodecount']; $i++) {
				if(isset(self::$codes['audio'][$i]) && !empty(self::$codes['audio'][$i])) {
					$audiocode .= '<li>'.self::$codes['audio'][$attachcodei].'</li>';
				} elseif(isset(self::$codes['video'][$i]) && !empty(self::$codes['video'][$i])) {
					$videocode .= '<li>'.self::$codes['video'][$i].'</li>';
				} elseif(isset(self::$codes['media'][$i]) && !empty(self::$codes['media'][$i])) {
					$mediacode .= '<li>'.self::$codes['media'][$i].'</li>';
				} elseif(isset(self::$codes['image'][$i]) && !empty(self::$codes['image'][$i]) && $count < 4) {
					$imagecode .= '<li>'.self::$codes['image'][$i].'</li>';
					$count++;
				} elseif(isset(self::$codes['attach'][$i]) && !empty(self::$codes['attach'][$i])) {
					$attachcode .= '<li>'.self::$codes['attach'][$i].'</li>';
				}
			}
			if(!empty($audiocode)) {
				$message .= '<div class="flw_music"><ul>'.$audiocode.'</ul></div>';
			}
			if(!empty($videocode)) {
				$message .= '<div class="flw_video"><ul>'.$videocode.'</ul></div>';
			}
			if(!empty($mediacode)) {
				$message .= '<div class="flw_video"><ul>'.$mediacode.'</ul></div>';
			}
			if(!empty($imagecode)) {
				$message = '<div class="flw_image'.($count < 2 ? ' flw_image_1' : '').'"><ul>'.$imagecode.'</ul></div>'.$message;
			}
			if(!empty($attachcode)) {
				$message .= '<div class="flw_attach"><ul>'.$attachcode.'</ul></div>';
			}
		}
		return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), ' ', $message));
	}


	public static function fcodedisp($code, $type='codehtml') 
	{
		self::$codes['pcodecount']++;
		self::$codes[$type][self::$codes['pcodecount']] = $code;
		//self::$codes['codecount']++;
		return "[\tD_".self::$codes['pcodecount']."\t]";
	}

	public static function clearnl($message) 
	{
		$message = preg_replace("/[\r\n|\n|\r]\s*[\r\n|\n|\r]/i", "\n", $message);
		$message = preg_replace("/^[\r\n|\n|\r]{1,}/i", "", $message);
		$message = preg_replace("/[\r\n|\n|\r]{2,}/i", "\n", $message);

		return $message;
	}

	public static function fparsesmiles(&$message) 
	{
		static $enablesmiles;
		static $smile_caches;
		
		if($enablesmiles === null) {
			$smile_caches =  ObjectCreater::create('CacheLogic')->load_syscache(array('smilies', 'smileytypes'));
			$enablesmiles = false;
			if(!empty($smile_caches['smilies']) && is_array($smile_caches['smilies'])) {
				foreach($smile_caches['smilies']['replacearray'] AS $key => $smiley) {
					$img_class 		= "";
					//if($smile_caches['smileytypes'][$smile_caches['smilies']['typearray'][$key]]['directory']=='mx'){
						$img_class	= "class='smilies'";
					//}
					$smile_caches['smilies']['replacearray'][$key] = '<img '.$img_class.' src="'.RES_DOMAIN.'resources/php/bbs/static/image/smiley/'.$smile_caches['smileytypes'][$smile_caches['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" class="s" alt="" />';
				}
				$enablesmiles = true;
			}
		}
		$maxsmilies = ObjectCreater::create('SettingLogic')->get('maxsmilies');	
		$enablesmiles && $message = preg_replace($smile_caches['smilies']['searcharray'], $smile_caches['smilies']['replacearray'], $message, $maxsmilies);
		return $message;
	}

	// 
	public static function fparseurl($url, $text, $scheme) 
	{
		$html = '';
		if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
			$url = $matches[0];
			$length = 65;
			if(strlen($url) > $length) {
				$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
			}
			$html = '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
		} else {
			$url = substr($url, 1);
			if(substr(strtolower($url), 0, 4) == 'www.') {
				$url = 'http://'.$url;
			}
			$url = !$scheme ? SITE_URL.$url : $url;
			$atclass = substr(strtolower($text), 0, 1) == '@' ? ' class="xi2" ' : '';
			$html = '<a href="'.$url.'" target="_blank" '.$atclass.'>'.$text.'</a>';
		}
		return self::fcodedisp($html);
	}

	public static function parseurl($url, $text, $scheme) 
	{
		if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
			$url = $matches[0];
			$length = 65;
			if(strlen($url) > $length) {
				$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
			}
			return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
		} else {
			$url = substr($url, 1);
			if(substr(strtolower($url), 0, 4) == 'www.') {
				$url = 'http://'.$url;
			}
			$url = !$scheme ? SITE_URL.$url : $url;
			return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
		}
	}

	public static function hideattach($hidestr) 
	{
		preg_match_all("/\[attach\]\s*(.*?)\s*\[\/attach\]/is", $hidestr, $del);
		foreach($del[1] as $aid) {
			self::$delattach[$aid] = $aid;
		}
	}

	public static function fparseemail($email, $text) 
	{
		$text = str_replace('\"', '"', $text);
		$html = '';
		if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
			$email = trim($matches[0]);
			$html = '<a href="mailto:'.$email.'">'.$email.'</a>';
		} else {
			$html = '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
		}
		return self::fcodedisp($html);
	}

	public static function fparsetable($width, $bgcolor, $message) 
	{
		$html = '';
		if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
			$rows = explode("\n", $message);
			$html = '<table cellspacing="0" class="t_table" '.
				($width == '' ? NULL : 'style="width:'.$width.'"').
				($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>');
			foreach($rows as $row) {
				$html .= '<tr><td>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</td><td>', "\n"), $row).'</td></tr>';
			}
			$html .= '</table>';
		} else {
			if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
				return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
			}
			if(substr($width, -1) == '%') {
				$width = substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%';
			} else {
				$width = intval($width);
				$width = $width ? ($width <= 560 ? $width.'px' : '98%') : '';
			}

			$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/i", function($matches) { return self::parsetrtd($matches[1], 0, 0, $matches[2]); }, $message);
			$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/i", function($matches) { return self::parsetrtd('td', 0, 0, $matches[1]); }, $message);
			$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", function($matches) { return self::parsetrtd($matches[1], $matches[2], $matches[3], $matches[4]); }, $message);

			$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", function($matches) { return self::parsetrtd('td', $matches[1], $matches[2], $matches[3]); }, $message);

			$message = str_replace('\\"', '"', preg_replace("/\[\/td\]\s*\[\/tr\]\s*/i", '</td></tr>', $message));

	 		$html = '<table cellspacing="0" class="t_table" '.
	 			($width == '' ? NULL : 'style="width:'.$width.'"').
	 			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>').$message.'</table>';

		}
		return self::fcodedisp($html);
	}

	public static function parsetrtd($bgcolor, $colspan, $rowspan, $width) 
	{
		return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
	}


	public static function fparseattach($aid, $length = 0, $extra = '') 
	{
		$html = '';
		if(!empty(self::$post_attach) && !empty(self::$post_attach[$aid])) {
			$attach = self::$post_attach[$aid];
			unset(self::$post_attach[$attach['aid']]);

			$settings                   = ObjectCreater::create('SettingLogic')->muti_get(array('ftp','attachurl','attachrefcheck','attachimgpost'));
			$settings['ftp']            = isset($settings['ftp']) && $settings['ftp'] ? (is_array($settings['ftp']) ? $settings['ftp'] : unserialize($settings['ftp'])) : array();
			$settings['attachurl']      = isset($settings['attachurl']) ? $settings['attachurl'] : null;
			$settings['attachrefcheck'] = isset($settings['attachrefcheck']) ? $settings['attachurl'] : null;
			$settings['attachimgpost']  = isset($settings['attachimgpost']) ? $settings['attachimgpost'] : null;

			$attach['url']      = ($attach['remote'] ? $settings['ftp']['attachurl'] : $settings['attachurl']).'forum/';
			$attach['isimage']  = $attach['isimage'] && !$attach['price'] ? $attach['isimage'] : 0;
			$attach['refcheck'] = (!$attach['remote'] && $settings['attachrefcheck']) || ($attach['remote'] && ($settings['ftp']['hideurl'] || ($attach['isimage'] && $settings['attachimgpost'] && strtolower(substr($settings['ftp']['attachurl'], 0, 3)) == 'ftp')));
			
			$rimg_id = HelperAuth::random(5).$attach['aid'];
			if($attach['isimage'] && !$attach['price'] && !$attach['readperm']) {
				$nothumb = $length ? 0 : 1;
				$src = $attach['url'].(!$attach['thumb'] ? $attach['attachment'] : HelperBiz::get_img_thumb_name($attach['attachment']));
				$html = self::bbcodeurl($src, '<img id="aimg_'.$rimg_id.'" src="'.$src.'" border="0" alt="'.$attach['filename'].'" '.$extra.' style="cursor: pointer;" />');

				return self::fcodedisp($html, 'image');
			} else {
				if($attach['price'] || $attach['readperm']) {
					$html = '<a href="forum.php?mod=viewthread&tid='.$attach['tid'].'" id="attach_'.$rimg_id.'" target="_blank" class="flw_attach_price"><strong>'.$attach['filename'].'</strong><span>'.sizecount($attach['filesize']).'</span></a>';
				} else {
					$aidencode = ObjectCreater::create('AttachmentLogic')->aid_encode($attach['aid']);
					$attachurl = "forum.php?mod=attachment&aid=$aidencode";
					$html = '<a href="'.$attachurl.'" id="attach_'.$rimg_id.'"><strong>'.$attach['filename'].'</strong><span>'.HelperUtils::sizecount($attach['filesize']).'</span></a>';
				}
				return self::fcodedisp($html, 'attach');
			}
		}
		return '';
	}

	public static function fparsemedia($params, $url) 
	{
		$params = explode(',', $params);
		$url    = addslashes($url);
		$html   = '';
		if($flv = self::parseflv($url, 0, 0)) {
			return self::fmakeflv($flv);
		}
		if(in_array(count($params), array(3, 4))) {
			$type     = $params[0];
			$url      = str_replace(array('<', '>','javascript:'), '', str_replace('\\"', '\"', $url));
			$url_text = $url;
			if(!preg_match('/^[a-zA-Z0-9]{1,10}\:\/\/(.*?)/', $url)){
				$url = '';
			}
			switch($type) {
				case 'mp3':
					return self::fparseaudio($url,$url_text);
					break;
				case 'flv':
					$url = STATICURL.'image/common/flvplayer.swf?&autostart=true&file='.urlencode($url);
					return self::fmakeflv($url);
					break;
				case 'swf':
					return self::fparseflash($url);
					break;
				default:
					$html = '<a href="'.$url.'" target="_blank">'.$url_text.'</a>';
					break;
			}
		}
		return self::fcodedisp($html, 'media');
	}

	public static function fparseaudio($url,$url_text='') 
	{
		$url = str_replace(array('<', '>','javascript:'), '', str_replace('\\"', '\"', $url));
		if(empty($url_text)){
			$url_text = $url;
			if(!preg_match('/^[a-zA-Z0-9]{1,10}\:\/\/(.*?)/', $url)){
				$url = '';
			}
		}
		if(HelperUtils::fileext($url) == 'mp3') {
			$randomid = 'music_'.HelperAuth::random(3);

			$style = ObjectCreater::create('CacheLogic')->load_syscache('style_1');
			$imgdir = !empty($style) && isset($style['imgdir']) ? $style['imgdir'] : '';

			$html = '<img src="'.$imgdir.'/music.gif" alt="'.lang('space', 'follow_click_play').'" onclick="javascript:showFlash(\'music\', \''.$url.'\', this, \''.$randomid.'\');" class="tn" style="cursor: pointer;" />';
			return self::fcodedisp($html, 'audio');
		} else {
			$html = '<a href="'.$url.'" target="_blank">'.$url_text.'</a>';
			return $html;
		}
	}

	public static function parseflv($url, $width = 0, $height = 0) 
	{
		$lowerurl = strtolower($url);
		$flv      = '';
		$imgurl   = '';
		if($lowerurl != str_replace(array('player.youku.com/player.php/sid/','tudou.com/v/','player.ku6.com/refer/'), '', $lowerurl)) {
			$flv = $url;
		} elseif(strpos($lowerurl, 'v.youku.com/v_show/') !== FALSE) {
			$ctx = stream_context_create(array('http' => array('timeout' => 10)));
			if(preg_match("/http:\/\/v.youku.com\/v_show\/id_([^\/]+)(.html|)/i", $url, $matches)) {
				$flv = 'http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
				if(!$width && !$height) {
					$api = 'http://v.youku.com/player/getPlayList/VideoIDS/'.$matches[1];
					$str = stripslashes(@file_get_contents($api, false, $ctx));
					if(!empty($str) && preg_match("/\"logo\":\"(.+?)\"/i", $str, $image)) {
						$url = substr($image[1], 0, strrpos($image[1], '/')+1);
						$filename = substr($image[1], strrpos($image[1], '/')+2);
						$imgurl = $url.'0'.$filename;
					}
				}
			}
		} elseif(strpos($lowerurl, 'tudou.com/programs/view/') !== FALSE) {
			if(preg_match("/http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $url, $matches)) {
				$flv = 'http://www.tudou.com/v/'.$matches[2];
				if(!$width && !$height) {
					$str = file_get_contents($url, false, $ctx);
					if(!empty($str) && preg_match("/<span class=\"s_pic\">(.+?)<\/span>/i", $str, $image)) {
						$imgurl = trim($image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'v.ku6.com/show/') !== FALSE) {
			if(preg_match("/http:\/\/v.ku6.com\/show\/([^\/]+).html/i", $url, $matches)) {
				$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
				if(!$width && !$height) {
					$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
						$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'v.ku6.com/special/show_') !== FALSE) {
			if(preg_match("/http:\/\/v.ku6.com\/special\/show_\d+\/([^\/]+).html/i", $url, $matches)) {
				$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
				if(!$width && !$height) {
					$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
						$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'www.youtube.com/watch?') !== FALSE) {
			if(preg_match("/http:\/\/www.youtube.com\/watch\?v=([^\/&]+)&?/i", $url, $matches)) {
				$flv = 'http://www.youtube.com/v/'.$matches[1].'&hl=zh_CN&fs=1';
				if(!$width && !$height) {
					$str = file_get_contents($url, false, $ctx);
					if(!empty($str) && preg_match("/'VIDEO_HQ_THUMB':\s'(.+?)'/i", $str, $image)) {
						$url = substr($image[1], 0, strrpos($image[1], '/')+1);
						$filename = substr($image[1], strrpos($image[1], '/')+3);
						$imgurl = $url.$filename;
					}
				}
			}
		} elseif(strpos($lowerurl, 'tv.mofile.com/') !== FALSE) {
			if(preg_match("/http:\/\/tv.mofile.com\/([^\/]+)/i", $url, $matches)) {
				$flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
				if(!$width && !$height) {
					$str = file_get_contents($url, false, $ctx);
					if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
						$imgurl = trim($image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'v.mofile.com/show/') !== FALSE) {
			if(preg_match("/http:\/\/v.mofile.com\/show\/([^\/]+).shtml/i", $url, $matches)) {
				$flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
				if(!$width && !$height) {
					$str = file_get_contents($url, false, $ctx);
					if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
						$imgurl = trim($image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'video.sina.com.cn/v/b/') !== FALSE) {
			if(preg_match("/http:\/\/video.sina.com.cn\/v\/b\/(\d+)-(\d+).html/i", $url, $matches)) {
				$flv = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='.$matches[1];
				if(!$width && !$height) {
					$api = 'http://interface.video.sina.com.cn/interface/common/getVideoImage.php?vid='.$matches[1];
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str)) {
						$imgurl = str_replace('imgurl=', '', trim($str));
					}
				}
			}
		} elseif(strpos($lowerurl, 'you.video.sina.com.cn/b/') !== FALSE) {
			if(preg_match("/http:\/\/you.video.sina.com.cn\/b\/(\d+)-(\d+).html/i", $url, $matches)) {
				$flv = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='.$matches[1];
				if(!$width && !$height) {
					$api = 'http://interface.video.sina.com.cn/interface/common/getVideoImage.php?vid='.$matches[1];
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str)) {
						$imgurl = str_replace('imgurl=', '', trim($str));
					}
				}
			}
		} elseif(strpos($lowerurl, 'http://my.tv.sohu.com/u/') !== FALSE) {
			if(preg_match("/http:\/\/my.tv.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
				$flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
				if(!$width && !$height) {
					$api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
						$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'http://v.blog.sohu.com/u/') !== FALSE) {
			if(preg_match("/http:\/\/v.blog.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
				$flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
				if(!$width && !$height) {
					$api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
					$str = file_get_contents($api, false, $ctx);
					if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
						$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
					}
				}
			}
		} elseif(strpos($lowerurl, 'http://www.ouou.com/fun_funview') !== FALSE) {
			$str = file_get_contents($url, false, $ctx);
			if(!empty($str) && preg_match("/var\sflv\s=\s'(.+?)';/i", $str, $matches)) {
				$style = ObjectCreater::create('CacheLogic')->load_syscache('style_1');				
				$flv = $style['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($matches[1]);
				if(!$width && !$height && preg_match("/var\simga=\s'(.+?)';/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
		} elseif(strpos($lowerurl, 'http://www.56.com') !== FALSE) {

			if(preg_match("/http:\/\/www.56.com\/\S+\/play_album-aid-(\d+)_vid-(.+?).html/i", $url, $matches)) {
				$flv = 'http://player.56.com/v_'.$matches[2].'.swf';
				$matches[1] = $matches[2];
			} elseif(preg_match("/http:\/\/www.56.com\/\S+\/([^\/]+).html/i", $url, $matches)) {
				$flv = 'http://player.56.com/'.$matches[1].'.swf';
			}
			if(!$width && !$height && !empty($matches[1])) {
				$api = 'http://vxml.56.com/json/'.str_replace('v_', '', $matches[1]).'/?src=out';
				$str = file_get_contents($api, false, $ctx);
				if(!empty($str) && preg_match("/\"img\":\"(.+?)\"/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
		}
		if($flv) {
			if(!$width && !$height) {
				return array('flv' => $flv, 'imgurl' => $imgurl);
			} else {
				$width  = addslashes($width);
				$height = addslashes($height);
				$flv    = addslashes($flv);
				$randomid = 'flv_'.HelperAuth::random(3);
				return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$flv.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
			}
		} else {
			return FALSE;
		}
	}

	public static function fmakeflv($flv) 
	{
		$randomid = 'video_'.HelperAuth::random(3);
		$flv = is_array($flv) ? $flv : array('flv' => $flv);
		
		$style = ObjectCreater::create('CacheLogic')->load_syscache('style_1');
		$imgdir = !empty($style) && isset($style['imgdir']) ? $style['imgdir'] : '';

		if(!empty($flv['imgurl'])) {
			$html = '<table class="mtm" title="'.HelperLang::lang('space', 'follow_click_play').'" onclick="javascript:showFlash(\'flash\', \''.$flv['flv'].'\', this, \''.$randomid.'\');"><tr><td class="vdtn hm" style="background: url('.$flv['imgurl'].') no-repeat;    border: 1px solid #CDCDCD; cursor: pointer; height: 95px; width: 126px;"><img src="'.$imgdir.'/vds.png" alt="'.HelperLang::lang('space', 'follow_click_play').'" />	</td></tr></table>';
		} else {
			$html = '<img src="'.$imgdir.'/vd.gif" alt="'.HelperLang::lang('space', 'follow_click_play').'" onclick="javascript:showFlash(\'flash\', \''.$flv['flv'].'\', this, \''.$randomid.'\');" class="tn" style="cursor: pointer;" />';
		}
		return self::fcodedisp($html, 'video');
	}

	public static function fparseflash($url) 
	{
		preg_match("/((https?){1}:\/\/|www\.)[^\[\"']+/i", $url, $matches);
		$url = $matches[0];
		if(HelperUtils::fileext($url) != 'flv') {
			$rimg_id = 'swf_'.HelperAuth::random(5);

			$style = ObjectCreater::create('CacheLogic')->load_syscache('style_1');
			$imgdir = !empty($style) && isset($style['imgdir']) ? $style['imgdir'] : '';

			$html = self::bbcodeurl($url, '<img src="'.$imgdir.'/flash.gif" alt="'.HelperLang::lang('space', 'follow_click_play').'" onclick="javascript:showFlash(\'flash\', \''.$url.'\', this, \''.$rimg_id.'\');" class="tn" style="cursor: pointer;" />');
			return self::fcodedisp($html, 'media');
		} else {
			$url = STATICURL.'image/common/flvplayer.swf?&autostart=true&file='.urlencode($matches[0]);
			return self::fmakeflv($url);
		}
	}

	public static function bbcodeurl($url, $tags) 
	{
		if(!preg_match("/<.+?>/s", $url)) {
			if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
				$url = 'http://'.$url;
			}
			return str_replace(array('submit', 'member.php?mod=logging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
		} else {
			return '&nbsp;'.$url;
		}
	}

	public static function fparseimg($src, $extra = '') 
	{
		$immid = HelperAuth::random(5);
		$html  = self::bbcodeurl($src, '<img id="iimg_'.$immid.'" src="'.$src.'" border="0" alt="" '.$extra.' style="cursor: pointer;" />');
		return self::fcodedisp($html, 'image');
	}

}
