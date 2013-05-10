<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */

class status 
{
    private $path = '';
    private $conf = '';
    private $conn = '';
    
    private $host = '';
    private $user = '';
    private $passwd = '';
    private $db_name = '';
    private $pre = '';
    
    public function __construct($path)
    {
        $this->path = $path;
        
        $this->get_conf();
        $this->eval_conf();
        $this->connect();
    }
    
    private function get_conf()
    {
        if(!empty($this->path) && file_exists($this->path.'wp-config.php'))
        {
            $this->conf = file_get_contents($this->path.'wp-config.php');
            $this->conf = preg_replace('#require_once.+?;#','',$this->conf);
            $this->conf = str_ireplace('<?php','',$this->conf);
            
            return true;
        }
        
        return false;
    }
    
    private function eval_conf()
    {
        if(!empty($this->conf))
        {
            eval($this->conf);
            
            if(defined('DB_HOST'))
            {
                $this->user = DB_USER;
                $this->passwd = DB_PASSWORD;
                $this->host = DB_HOST;
                $this->db_name = DB_NAME;
                    
                $this->pre = $table_prefix;               
                
                return true;
            }
        }
        
        
        return false;
    }
    
    private function connect()
    {
        if(!empty($this->user))
        {
            if(($this->conn = mysql_connect($this->host,$this->user,$this->passwd)))
                mysql_select_db ($this->db_name);
                
            return true;
        }
        
        return false;
    }
    
    public function set_status($status,$id)
    {
        if(is_resource($this->conn))
            return mysql_query('UPDATE `'.$this->pre.'sp_logs` SET `date`=NOW(),`status` = "'.$status.'" WHERE `id` = "'.(int)$id.'"',$this->conn);    
        
        return false;
    }
    
    public function insert_log($status)
    {
        if(is_resource($this->conn))
            return mysql_query('INSERT INTO `'.$this->pre.'sp_logs` SET `date` = NOW() , `status` = "'.$status.'"',$this->conn);
        
        return false;
    }
    
    public function get_id()
    {
        if(is_resource($this->conn))
            return mysql_insert_id();
        
        return false;
    }
    
    public function __destruct()
    {
        if(is_resource($this->conn))
            mysql_close($this->conn);
    }
    

}

?>