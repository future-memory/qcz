<?php

define('CENSOR_SUCCEED', 0);
define('CENSOR_BANNED', 1);
define('CENSOR_MODERATED', 2);
define('CENSOR_REPLACED', 3);

class HelperCensor {
	public $censor_words = array();
	public $bbcodes_display;
	public $result;
	public $words_found = array();

	public $highlight;

	public function __construct() 
	{
		$caches                = ObjectCreater::create('CacheLogic')->load_syscache(array('censor', 'bbcodes_display'));
		$this->censor_words    = !empty($caches['censor']) ? $caches['censor'] : array();
		$cur_member            = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->bbcodes_display = $caches['bbcodes_display'][$cur_member['groupid']];
	}

	public function highlight($message, $badwords_regex) 
	{
		$color = $this->highlight;
		if(empty($color)) {
			return $message;
		}
		$message = preg_replace($badwords_regex, '<span style="color: '.$color.';">\\1</span>', $message);
		return $message;
	}

	public function check(&$message, $modword = NULL) 
	{
		$limitnum = 500;
		$this->words_found = array();
		$bbcodes = 'b|i|color|size|font|align|list|indent|email|hide|quote|code|free|table|tr|td|img|swf|attach|payto|float'.($this->bbcodes_display ? '|'.implode('|', array_keys($this->bbcodes_display)) : '');
		if(is_array($this->censor_words['banned']) && !empty($this->censor_words['banned'])) {
			foreach($this->censor_words['banned'] as $banned_words) {
				if(preg_match_all($banned_words, @preg_replace(array("/\[($bbcodes)=?.*\]/iU", "/\[\/($bbcodes)\]/i"), '', $message), $matches)) {
					$this->words_found = $matches[0];
					$this->result = CENSOR_BANNED;
					$this->words_found = array_unique($this->words_found);
					$message = $this->highlight($message, $banned_words);
					return CENSOR_BANNED;
				}
			}
		}
		if(is_array($this->censor_words['mod']) && !empty($this->censor_words['mod'])) {
			if($modword !== NULL) {
				$message = preg_replace($this->censor_words['mod'], $modword, $message);
			}
			foreach($this->censor_words['mod'] as $mod_words) {
				if(preg_match_all($mod_words, @preg_replace(array("/\[($bbcodes)=?.*\]/iU", "/\[\/($bbcodes)\]/i"), '', $message), $matches)) {
					$this->words_found = $matches[0];
					$this->result = CENSOR_MODERATED;
					$message = $this->highlight($message, $mod_words);
					$this->words_found = array_unique($this->words_found);
					return CENSOR_MODERATED;
				}
			}
		}
		if(!empty($this->censor_words['filter'])) {
			$i = 0;
			while(($find_words = array_slice($this->censor_words['filter']['find'], $i, $limitnum))!=false) {
				if(empty($find_words)) break;
				$replace_words = array_slice($this->censor_words['filter']['replace'], $i, $limitnum);
				$i += $limitnum;
				$message = preg_replace($find_words, $replace_words, $message);
			}
			$this->result = CENSOR_REPLACED;
			return CENSOR_REPLACED;
		}
		$this->result = CENSOR_SUCCEED;
		return CENSOR_SUCCEED;
	}

	public function modbanned() 
	{
		return $this->result == CENSOR_BANNED;
	}

	public function modmoderated() 
	{
		return $this->result == CENSOR_MODERATED;
	}

	public function modreplaced() 
	{
		return $this->result == CENSOR_REPLACED;
	}

	public function modsucceed() 
	{
		return $this->result == CENSOR_SUCCEED;
	}
}