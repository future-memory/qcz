<?php

abstract class BaseDao
{
    protected $_db;
    protected $_table;
    protected $_old_table;
    protected $_pk;
    protected $_memory;
    protected $_pre_cache_key;
    protected $_cache_ttl = 600;
    protected $_allowmem = true;

    public function __construct() 
    {
        $this->_db            = Nice::app()->getComponent('DataBase');
        $this->_memory        = Nice::app()->getComponent('Memory');
        $this->_pre_cache_key = $this->_pre_cache_key ? $this->_pre_cache_key : $this->_table.'_';
    }

    public function getTable() 
    {
        return $this->_table;
    }

    public function setTable($name) 
    {
        $this->_old_table = $this->_table;
        return $this->_table = $name;
    }

    public function revertTable()
    {
        $this->_table = $this->_old_table;
    }

    public function count() 
    {
        $count = (int) $this->_db->result_first("SELECT count(*) FROM ".$this->_db->table($this->_table));
        return $count;
    }

    //添加没有用的占位$not_use参数 解决 forum_attachment_n forum_post分表后 继承后,由于开启了严格模式 多了个参数而报错的问题
    public function update($val, $data, $unbuffered = false, $low_priority = false) 
    {
        if(isset($val) && !empty($data) && is_array($data)) {
            $this->checkpk();
            $ret = $this->_db->update($this->_table, $data, $this->_db->field($this->_pk, $val), $unbuffered, $low_priority);
            foreach((array)$val as $id) {
                $this->update_cache($id, $data);
            }
            return $ret;
        }
        return !$unbuffered ? 0 : false;
    }

    //添加没有用的占位$not_use参数 解决 forum_attachment_n forum_post分表后 继承后,由于开启了严格模式 多了个参数而报错的问题
    public function delete($val, $unbuffered = false,$not_use=true) 
    {
        $ret = false;
        if(isset($val)) {
            $this->checkpk();
            $ret = $this->_db->delete($this->_table, $this->_db->field($this->_pk, $val), null, $unbuffered);
            $this->clear_cache($val);
        }
        return $ret;
    }

    public function truncate() 
    {
        $this->_db->query("TRUNCATE ".$this->_db->table($this->_table));
    }

    //添加没有用的占位$not_use参数 解决 forum_attachment_n forum_post分表后 继承后,由于开启了严格模式 多了个参数而报错的问题
    public function insert($data, $return_insert_id = false, $replace = false, $silent = false, $not_use=null) 
    {
        if($replace && isset($data[$this->_pk])){
            $this->clear_cache($data[$this->_pk]);
        }        
        return $this->_db->insert($this->_table, $data, $return_insert_id, $replace, $silent);
    }

    //
    public function insert_or_update($data, $return_insert_id = false, $silent = false ,$not_use=true) 
    {
        if(isset($data[$this->_pk])){
            $this->clear_cache($data[$this->_pk]);
        }
        return $this->_db->insert_or_update($this->_table, $data, $return_insert_id, $silent);
    }


    //
    public function batch_insert($data, $return_insert_id = false, $replace = false, $silent = false,$not_use=true) 
    {
        return $this->_db->batch_insert($this->_table, $data, $return_insert_id, $replace, $silent);
    }

    public function set_pk($key)
    {
        $this->_pk = $key;
    }    

    public function checkpk() 
    {
        if(!$this->_pk) {
            throw new DbException('Table '.$this->_table.' has not PRIMARY KEY defined');
        }
    }

    public function fetch($id, $force_from_db = false)
    {
        $data = array();
        if(!empty($id)) {
            if($force_from_db || ($data = $this->fetch_cache($id)) === false) {
                $data = $this->_db->fetch_first('SELECT * FROM '.$this->_db->table($this->_table).' WHERE '.$this->_db->field($this->_pk, $id));
                if(!empty($data)) $this->store_cache($id, $data);
            }
        }
        return $data;
    }

    public function fetch_all($ids, $force_from_db = false) 
    {
        $data = array();
        if(!empty($ids)) {
            if($force_from_db || ($data = $this->fetch_cache($ids)) === false || count($ids) != count($data)) {
                if(is_array($data) && !empty($data)) {
                    $ids = array_diff($ids, array_keys($data));
                }
                if($data === false) $data =array();
                if(!empty($ids)) {
                    $query = $this->_db->query('SELECT * FROM '.$this->_db->table($this->_table).' WHERE '.$this->_db->field($this->_pk, $ids));
                    
                    while(($value = $this->_db->fetch($query))!=false) {
                        $data[$value[$this->_pk]] = $value;
                        $this->store_cache($value[$this->_pk], $value);
                    }
                    
                    if(is_array($ids)){     // 没有查到的 ID 也照样 记录到缓存中
                        foreach($ids as $id){
                            if(!isset($data[$id]) ){
                                $this->store_cache($id, '' , 60);   // 保存  60 秒
                            }
                        }
                    }
                    
                }
            }
        }
        return $data;
    }

    public function fetch_all_field()
    {
        $data = false;
        $query = $this->_db->query('SHOW FIELDS FROM '.$this->_db->table($this->_table), '', 'SILENT');
        if($query) {
            $data = array();
            while(($value = $this->_db->fetch($query))!=false) {
                $data[$value['Field']] = $value;
            }
        }
        return $data;
    }

    public function range($start = 0, $limit = 0, $sort = '') 
    {
        if($sort) {
            $this->checkpk();
        }
        return $this->_db->fetch_all('SELECT * FROM '.$this->_db->table($this->_table).($sort ? ' ORDER BY '.$this->_db->order($this->_pk, $sort) : '').$this->_db->limit($start, $limit), null, $this->_pk ? $this->_pk : '');
    }

    public function optimize() 
    {
        $this->_db->query('OPTIMIZE TABLE '.$this->_db->table($this->_table), 'SILENT');
    }

    public function fetch_cache($ids, $pre_cache_key = null) 
    {
        $data = false;
        if($this->_allowmem) {
            if($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            $data = $this->_memory->cmd('get', $ids, $pre_cache_key);
        }
        return $data;
    }

    public function store_cache($id, $data, $cache_ttl = null, $pre_cache_key = null) 
    {
        $ret = false;
        if($this->_allowmem) {
            if($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            $ret = $this->_memory->cmd('set', $id, $data, $cache_ttl, $pre_cache_key);
        }
        return $ret;
    }

    public function clear_cache($ids, $pre_cache_key = null) 
    {
        $ret = false;
        if($this->_allowmem) {
            if($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            $ret = $this->_memory->cmd('rm', $ids, $pre_cache_key);
        }
        return $ret;
    }

    //添加没有用的占位$not_use参数 解决 forum_attachment_n forum_post分表后 继承后,由于开启了严格模式 多了个参数而报错的问题
    public function update_cache($id, $data, $cache_ttl = null, $pre_cache_key = null,$not_use=true) 
    {
        $ret = false;
        if($this->_allowmem) {
            if($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            if(($_data = $this->_memory->cmd('get', $id, $pre_cache_key)) !== false) {
                $ret = $this->store_cache($id, array_merge($_data, $data), $cache_ttl, $pre_cache_key);
            }
        }
        return $ret;
    }

    public function update_batch_cache($ids, $data, $cache_ttl = null, $pre_cache_key = null) 
    {
        $ret = false;
        if($this->_allowmem) {
            if($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            if(($_data = $this->_memory->cmd('get', $ids, $pre_cache_key)) !== false) {
                foreach($_data as $id => $value) {
                    $ret = $this->store_cache($id, array_merge($value, $data), $cache_ttl, $pre_cache_key);
                }
            }
        }
        return $ret;
    }

    public function reset_cache($ids, $pre_cache_key = null) 
    {
        $ret = false;
        if($this->_allowmem) {
            $keys = array();
            if(($cache_data = $this->fetch_cache($ids, $pre_cache_key)) !== false) {
                $keys = array_intersect(array_keys($cache_data), $ids);
                unset($cache_data);
            }
            if(!empty($keys)) {
                $this->fetch_all($keys, true);
                $ret = true;
            }
        }
        return $ret;
    }

    public function increase_cache($ids, $data, $cache_ttl = null, $pre_cache_key = null) 
    {
        if($this->_allowmem) {
            if(($cache_data = $this->fetch_cache($ids, $pre_cache_key)) !== false) {
                foreach($cache_data as $id => $one) {
                    foreach($data as $key => $value) {
                        if(is_array($value)) {
                            $one[$key] = $value[0];
                        } else {
                            $one[$key] = $one[$key] + ($value);
                        }
                    }
                    $this->store_cache($id, $one, $cache_ttl, $pre_cache_key);
                }
            }
        }
    }

    public function __toString() 
    {
        return $this->_table;
    }

    public function begin()
    {
        $this->_db->query_master("BEGIN"); 
    }

    public function commit()
    {
        $this->_db->query_master("COMMIT"); 
    }

    public function rollback()
    {
        $this->_db->query_master("ROLLBACK");
    }
	public function quote($str){
		return $this->_db->quote($str);
	}
}
