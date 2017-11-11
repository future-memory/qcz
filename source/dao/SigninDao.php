<?php
class SigninDao extends BaseDao
{
    public function __construct() 
    {
        $this->_pk        = 'uid';
        $this->_table     = 'signin';
        $this->_table_log = 'signin_log';
        $this->_table_config = 'signin_config';
        parent::__construct();
    }

    //删除签到列表缓存
    public function del_signed_list_cache($uid, $start_time)
    {
        $mem_key = 'signed_list_'.$uid.'_'.$start_time;
        return $this->_memory->cmd('rm', $mem_key);
    }

    //定期删除日志
    public function delete_exceed($exceedtime) 
    {
        $this->_db->query("DELETE FROM %t WHERE dateline<%d", array($this->_table_log, TIMESTAMP - intval($exceedtime)), false, true);
    }

    //获取已签到列表
    public function get_signed_list($uid, $start_time)
    {
        if($this->_allowmem) {
            $mem_key = 'signed_list_'.$uid.'_'.$start_time;
            $data = $this->_memory->cmd('get', $mem_key);
            if($data!==false){
                return $data;
            }
        }

        $sql  = 'SELECT id,dateline,flag,last_days,days,circle_last_days,code FROM %t WHERE uid=%d AND dateline>=%d ORDER BY dateline ASC';
        $data = $this->_db->fetch_all($sql, array($this->_table_log, $uid, $start_time));

        if($this->_allowmem) {
            $this->_memory->cmd('set', $mem_key, $data, 600);
        }
        return $data;       
    }

    //获取单天已签到数据
    public function get_signed_item($uid, $start_time)
    {
        if($this->_allowmem) {
            $mem_key = 'signed_item_'.$uid.'_'.$start_time;
            $data = $this->_memory->cmd('get', $mem_key);
            if($data!==false){
                return $data;
            }
        }

        $sql  = 'SELECT dateline,flag,last_days,days,circle_last_days,code FROM %t WHERE uid=%d AND dateline>=%d ORDER BY dateline ASC LIMIT 1';
        $data = $this->_db->fetch_first($sql, array($this->_table_log, $uid, $start_time));

        if($this->_allowmem) {
            $this->_memory->cmd('set', $mem_key, $data, 60);
        }
        return $data;       
    }

    public function get_all_signin_config()
    {
        //切换数据表
        $this->setTable($this->_table_config);
        //获取数据
        $data = $this->range();
        //数据表恢复
        $this->revertTable();
        
        return $data;
    }

    public function fix_log_data($ids, $days)
    {
        if(empty($ids) || !$days){
            return false;
        }
        return $this->_db->query("UPDATE %t set last_days=last_days+%d WHERE ".$this->_db->field('id',$ids), array($this->_table_log,$days));
    }



}