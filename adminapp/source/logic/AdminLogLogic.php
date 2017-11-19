<?php

class AdminLogLogic extends Logic
{
	public function get_log_by_month($month, $start, $limit, $logname = "cplog") {
		$logdir = BASE_ROOT . '/data/log/';
		$logfiles = $this->_get_log_files($logdir, $logname);

		$logs = array();
		$lastkey = count($logfiles) - 1;
		$lastlog = $logfiles[$lastkey];
		krsort($logfiles);

		$months = array();
		foreach ($logfiles as $key => $value) {
			$months[] = explode('_', $value)[0];
		}

		if ($month) {
			$logs = file($logdir . $month . '_' . $logname .'.php');
		} else {
			$logs = file($logdir . $lastlog);
		}
		$logs = array_reverse($logs);

		$selected = explode('_', $lastlog)[0];
		if ($month) {
			$selected = $month;
		}

		$count = count($logs);
		$logs  = array_slice($logs, $start, $limit);

		return array('selected'=>$selected, 'logs'=>$logs, 'count'=>$count, 'months' => $months);		
	}

	private function _get_log_files($logdir = '', $action = '') 
	{
		$dir = opendir($logdir);
		$files = array();
		while($entry = readdir($dir)) {
			$files[] = $entry;
		}
		closedir($dir);

		if($files) {
			sort($files);
			$logfile  = $action;
			$logfiles = array();
			$ym       = '';
			$domain   = ObjectCreater::create('AdminLogic')->get_current_domain();
			$domain   = $domain ? $domain : 'www';

			foreach($files as $file) {
				if(strpos($file, $logfile) !== FALSE) {
					if(substr($file, 0, 6) != $ym) {
						$ym = substr($file, 0, 6);
					}
					if($file==$ym.'_cplog_'.$domain.'.php'){
						$logfiles[$ym][] = $file;
					}
				}
			}
			if($logfiles) {
				$lfs = array();
				foreach($logfiles as $ym => $lf) {
					$lastlogfile = $lf[0];
					unset($lf[0]);
					$lf[] = $lastlogfile;
					$lfs = array_merge($lfs, $lf);
				}

				return $lfs;
			}
			return array();
		}
		return array();		
	}

}