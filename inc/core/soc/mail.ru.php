<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class MAIL_RU extends poster
{
    public function login()
    {
        $login = $this->login;
        $l = explode('@',$login);
        
        $login = $l[0];
        $domain = $l[1];
        
        $login = urlencode($login);
        $pass = urlencode($this->pass);
        $domain = urlencode($domain);
        
        $this->c->post('http://win.mail.ru/cgi-bin/auth','Login='.$login.'&Domain='.$domain.'&Password='.$pass);
    }
    
    public function post()
    {
        $url = urlencode($this->url);
        $text = addslashes(urldecode($this->text));
        $desc = addslashes(urldecode($this->desc));
        $text = str_replace("\r",'',$text);
        $text = str_replace("\n",'%5Cn',$text);
        $title = addslashes(urldecode($this->title));
        $image = urlencode($this->image);
        
        $r = $this->c->get('http://my.mail.ru/');
        sleep(10);
        
        preg_match("#'mna': '(\d+?)',#",$r,$mna);
        preg_match("#'mnb': '-(\d+?)',#",$r,$mnb);

        $this->c->post('http://my.mail.ru/cgi-bin/connect/ajax','ajax_call=1&func_name=perl_fetch_connect_page&data='.$url.'&mna='.$mna[1].'&mnb=-'.$mnb[1].'&encoding=windows-1251');
        
        sleep(15);
        
        $enc = urlencode('["'.$text.'", {"type": "share", "desc": "'.$title.'", "title": "'.$desc.'", "url": "'.$url.'", "image": "'.$image.'", "height": 50, "width": 50}]');
        $r = $this->c->post('http://my.mail.ru/cgi-bin/my/ajax','ajax_call=1&func_name=micropost.send&data='.$enc.'&mna='.$mna[1].'&mnb=-'.$mnb[1].'&encoding=windows-1251');
        
        sleep(20);
        
        if(preg_match('#share\?shareid=\d+#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $r = $this->c->get('http://auth.mail.ru/cgi-bin/logout?');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new MAIL_RU();
?>