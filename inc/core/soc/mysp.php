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

        $post = 'Email='.$login.'&Password='.$pass.'';
        $this->c->post('https://www.myspace.com/auth/login',$post);
    }
    
    public function post()
    {
        $url = urlencode($this->url);
        $image = urlencode($this->image);
        $title = urlencode($this->title);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        

        $r = $this->c->get('http://www.myspace.com/home');
        preg_match('#name="hash"[^>]+?value="([^"]+?)"#',$r,$hash);
        
        $r = $this->c->get('http://scraper.myspace.com/Modules/SuperShare/Services/Scraper.ashx?jsonp=jQuery1520005306529935637294_1337426662223&url='.$url.'&_=1337426696555');

        
        if(!empty($image) && !preg_match('#"images"\:\[.*?"'.preg_quote(urldecode($image),'#').'".*?\],#',$r))
        {
            preg_match('#"images"\:\["(.+?)".*?\],#',$r,$im);
            
            
            $image = urlencode($im[1]);
        }
        
        $headers = array('hash' => urlencode($hash[1]),'X-Requested-With' => 'XMLHttpRequest');
        $post = 'url='.$url.'&title='.$title.'&domain=&description='.$text.'&image='.$image.'&favicon=null&mediaType=BookMark&mediaUrl=null&mediaHeight=0&mediaWidth=0&contentType=null&actionText=&token=&status='.$desc.'&isLinkedStatus=false&hash='.urlencode($hash[1]).'&theme=List&showToolbarButtons=true&streamMiniProfileImageSize=Notification&showTimeZoneInToolbar=true';
        $r = $this->c->post('http://www.myspace.com/Modules/PageEditor/Handlers/Common/SaveStatus.ashx',$post,$headers);
        
        preg_match('#data-item=\\\\"(\d+?_\d+?)_ShareItem\\\\"#',$r,$up);
        
        sleep(5);
        
        
        if(isset($up['1']))
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