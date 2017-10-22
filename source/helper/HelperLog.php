<?php

class HelperLog {

	public static function runlog($file, $message, $halt=0) 
	{
		$nurl   = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$log    = date('Y-m-d H:i:s')."\t".$member['clientip']."\t$member[uid]\t{$url}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($message))."\n";
		self::writelog($file, $log);

		if($halt) {
			exit();
		}
	}

	public static function writelog($file, $log)
	{
		$yearmonth = date('Ym');
		$logdir    = BASE_ROOT.'/data/log/';
		$logfile   = $logdir.$yearmonth.'_'.$file.'.php';

		if(file_exists($logfile) && @filesize($logfile) > 2048000) {
			$dir    = opendir($logdir);
			$length = strlen($file);
			$maxid  = $id = 0;
			while(($entry = readdir($dir))!=false) {
				if(strpos($entry, $yearmonth.'_'.$file) !== false) {
					$id = intval(substr($entry, $length + 8, -4));
					$id > $maxid && $maxid = $id;
				}
			}
			closedir($dir);

			$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
			@rename($logfile, $logfilebak);
		}
		if(($fp = @fopen($logfile, 'a'))!=false) {
			@flock($fp, 2);
			if(!is_array($log)) {
				$log = array($log);
			}
			foreach($log as $tmp) {
				fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
			}
			fclose($fp);
		}
	}

	//用户操作日志
	public static function user_action_log($uid, $action) 
	{
		$uid = intval($uid);
		if(empty($uid) || empty($action)) {
			return false;
		}
		$action = self::get_user_action($action);
		ObjectCreater::create('ActionLogDao')->insert(array('uid'=>$uid, 'action'=>$action, 'dateline'=>TIMESTAMP));
		return true;
	}

	public static function get_user_action($var) 
	{
		$value = false;
		$ops = array('tid', 'pid', 'blogid', 'picid', 'doid', 'sid', 'aid', 'uid_cid', 'blogid_cid', 'sid_cid', 'picid_cid', 'aid_cid', 'topicid_cid', 'pmid');
		if(is_numeric($var)) {
			$value = isset($ops[$var]) ? $ops[$var] : false;
		} else {
			$value = array_search($var, $ops);
		}
		return $value;
	}

}
