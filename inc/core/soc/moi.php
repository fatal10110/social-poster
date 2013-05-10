<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class MOY extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $this->c->get('http://moikrug.ru');
        
        preg_match('#title:"Мой Круг",register:""\}\'><input[^>]+?name="fingerprint"[^>]+?value="([^"]+?)"#im',$r,$f);
        
        $post = 'fingerprint='.urlencode($f[1]).'&login='.$login.'&passwd='.$pass.'&timestamp='.time();
        
        $this->c->post('https://passport.yandex.ru/passport?mode=auth&from=mk&retpath=http%3A%2F%2Fmoikrug.ru%2F',$post);
    }
    
    public function post()
    {
        $url = sp_myUrlEncode($this->url);
        $text = $this->text;
        $title = $this->title;

        $r = $this->c->get('http://moikrug.ru/share?ie=utf-8');
        
        preg_match('#<input[^>]+?name="fingerprint"[^>]+?value="([^"]+?)"#im',$r,$f); 
        
       $post = array(
            'fingerprint' => $f[1],
            'Widget[]' => 'FeedShare:feed_share',
            'request_token' => substr($f[1],0,stripos($f[1],'=')+1),
            'feed_share_title' => iconv('UTF-8','windows-1251',$title),
            'feed_share_url' => $url,
            'feed_share_description' => iconv('UTF-8','windows-1251',$text),
            'feed_share_submit' => 'go'
        );
        
        $q = http_build_query($post);
        $r = $this->c->post('http://moikrug.ru/share?ie=utf-8',$q,$header);
        
        sleep(10);
        
        return '1';
    }
    
    public function logout()
    {
        $r = $this->c->get('http://moikrug.ru');
        
        preg_match('#<td class="b-head-userinfo__exit"><a class="b-head-userinfo__link" href="(.+?)">#im',$r,$ex);
        
        $this->c->get($ex[1]);
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new MOY();
?>