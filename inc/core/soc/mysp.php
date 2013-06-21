<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class MYSP extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        $r = $this->c->get('https://myspace.com/signin');
        
        preg_match('#"pageId":"([^"]+?)"#',$r, $pid);
        preg_match('#"hashMashter":"([^"]+?)"#',$r, $hash);
        
        
        $headers = array('X-Requested-Wit: XMLHttpRequest','Hash: '.$hash[1]);
        
        $post = 'email='.$login.'&password='.$pass.'&pageId='.$pid[1];
        $this->c->post('https://myspace.com/ajax/account/signin',$post, $headers);
    }
    
    public function post()
    {
        $url = urlencode($this->url);
        $image = urlencode($this->image);
        $title = urlencode($this->title);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        

        $r = $this->c->get('http://www.myspace.com/home');
        preg_match('#"hashMashter":"([^"]+?)"#',$r, $hash);         
        $headers = array('X-Requested-Wit: XMLHttpRequest','Hash: '.$hash[1]);
        $post = 'url='.urlencode($this->url);
        
        $r = $this->c->post('https://myspace.com/ajax/stream/scrape',$post, $headers);

        
       /* if(!empty($image) && !preg_match('#"images"\:\[.*?"'.preg_quote(urldecode($image),'#').'".*?\],#',$r))
        {
            preg_match('#"images"\:\["(.+?)".*?\],#',$r,$im);
            
            
            $image = urlencode($im[1]);
        }
        */
        
        $headers = array('hash' => $hash[1],'X-Requested-With' => 'XMLHttpRequest');
        $post = 'comment='.$desc.'&locationentitykey&postlink=true&linkurl='.$url.'&linkdescription='.$text.'&linkthumbnail='.$image.'&linkcontenttype=text%2Fhtml&linkmediatype=Website';
        
        $r = $this->c->post('https://myspace.com/ajax/stream/superpost',$post,$headers);

        if(preg_match('#\{"success"\:true#',$r))
            return '1';
        
        return '0';
   }
    
    public function logout()
    {
        $r = $this->c->get('http://www.myspace.com/home');
        preg_match("#<a class='signout' href=\"(/signout[^\"]+?)\"#",$r,$out);
        $r = $this->c->get('http://www.myspace.com'.$out[1]);        
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new MYSP();
?>