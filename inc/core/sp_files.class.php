<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */

class SP_FILES
{
    private $file = '';
    private $res = null;
    private $locked = 0;
    
    public function __construct($file)
    {
        $this->file = $file;  
    }
    
    public function fflock()
    {
        if(!empty($this->file))
        {
            if(($this->res = fopen($this->file,'a+b')))
            {
                flock($this->res,LOCK_EX);
                $this->locked = 1;  
                return true;
            }
        }
        
        return false;
    }
    
    public function ffunlock()
    {
        if(!is_resource($this->res)) return false;
        
        if($this->locked == 1)
        {
            fflush($this->res);
            flock($this->res,LOCK_UN);
            fclose($this->res);
        }
        
        $this->locked = 0;
    }
    
    public function ffget_size()
    {
        if(!$this->locked)
            if(!$this->fflock()) return false;
        
        clearstatcache();
        
        return filesize($this->file);
    }
    
    public function ffread()
    {        
        if(!$this->locked)
            if(!$this->fflock()) return false;
        
            $size = $this->ffget_size();
            
            if($size === 0)
                $cont = '';
            else
                $cont = fread($this->res,$size);
        
        return $cont;
    }   
    
    public function ffwrite($data)
    {
        if(!$this->locked)
            if(!$this->fflock()) return false;
            
        ftruncate($this->res, 0);
        fseek($this->res, 0, SEEK_SET);
        
        if(!empty($data)) $data .= "\r\n";
        
        fwrite($this->res,$data);
            
    }
    
    public function ffclear()
    {
        if($this->fflock())
            ftruncate($this->res, 0);
    }
    
    public function __destruct()
    {
        if(is_resource($this->res))
            $this->ffunlock();
    }
}

?>