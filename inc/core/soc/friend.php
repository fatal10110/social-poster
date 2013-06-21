<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class FRIEND extends poster
{    
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('https://friendfeed.com/');

        preg_match('#name="at"[^>]+?value="([0-9_]+?)"#',$r,$at);

        $post = 'email='.$login.'&password='.$pass.'&next=http%3A%2F%2Ffriendfeed.com%2F&at='.urlencode($at[1]);
        $this->c->post('https://friendfeed.com/account/login?v=2',$post);
    }
    
    public function post()
    {
        $r = $this->c->get('https://friendfeed.com/');

        preg_match('#name="at"[^>]+?value="([0-9_]+?)"#',$r,$at);
        preg_match('#name="streams"[^>]+?value="(.+?)"#',$r,$stream);

        $post = 'title='.urlencode($this->text.' '.$this->url).'&maybetweet=0&streams='.urlencode($stream[1]).'&at='.urlencode($at[1]).'&_nano=1';
        $r = $this->c->post('http://friendfeed.com/a/share',$post);
        
        preg_match('#id"\:"(\w+?)"#',$r,$id);
       
       if(isset($id[1]))
            return '1';
       
       return '0';
   }
    
    public function logout()
    {
        $r = $this->c->get('http://friendfeed.com/account/logout?next=http%3A%2F%2Ffriendfeed.com%2F');     
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new FRIEND();
?>