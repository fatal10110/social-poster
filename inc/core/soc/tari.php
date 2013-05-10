<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class TARI extends poster
{   
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $this->c->get('https://www.taringa.net/login?redirect=%2F');
        
       $r = $this->c->post('https://www.taringa.net/registro/login-submit.php','nick='.$login.'&pass='.$pass.'&redirect=%2F&connect');
       
    }
    
    public function post()
    {
        $url = urlencode($this->url);
        
        
        $r = $this->c->get('http://www.taringa.net');
      
        $r = $this->c->post('http://www.taringa.net/ajax/shout/attach','url='.$url);
        
        preg_match('#"id"\:"([^"]+?)",#',$r,$id);
        
        $r = $this->c->post('http://www.taringa.net/ajax/shout/add','body&privacy=0&attachment_type=3&attachment='.$id[1]);
        
        sleep(2);
        
        if(preg_match('#data-uid="\d+?">#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $r = $this->c->get('http://www.taringa.net');
        
        preg_match('#href="(/logout/[^"]+?)"#',$r,$out);
        
        $this->c->get('http://www.taringa.net'.$out[1]);
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new TARI();
?>