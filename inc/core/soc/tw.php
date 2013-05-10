<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class TW extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('https://twitter.com');
        preg_match('#value="(.+?)"[^>]+?name="authenticity_token"#',$r,$tok);

        $r = $this->c->post('https://twitter.com/sessions?phx=1','session%5Busername_or_email%5D='.$login.'&session%5Bpassword%5D='.$pass.'&scribe_log=%5B%5D&redirect_after_login=%2F&remember_me=1&authenticity_token='.$tok[1]);        

        sleep(5);
    }
    
    public function post()
    {   
        $url = sp_myUrlEncode($this->url);
        $url2 = urlencode($url);
        
        //$title = parent::soc_cut_text($title,138 - strlen($url));
        $title = soc_cut_text($this->title,110);
        
        $status = urlencode("$title $url");
        /*$r = $this->c->get('http://share.yandex.ru/go.xml?service=twitter&url='.urlencode($url).'&title='.urlencode($title));
        file_put_contents('logs/tw_ya.html',"http://share.yandex.ru/go.xml?service=twitter&url='.$url.'&title='.$title<br><br>$r");
        
        preg_match('#<input[^>]+?name="authenticity_token"[^>]+?value="(.+?)"[^>]+?/>#imx',$r,$token);
        
        $post = "authenticity_token=".$token[1]."&original_referer=".urlencode('http://twitter.com/intent/session?original_referer='.$url2.'&return_to=/intent/tweet?status='.$status)."&status=".$status;

        $r = $this->c->post('http://twitter.com/intent/tweet/update',$post);
        file_put_contents('logs/tw_post.html',"$token[1]<br><bt>$r");
        */
        
        $r = $this->c->get('https://twitter.com');

        preg_match('#<input[^>]+?value="(\w+?)"[^>]+?name="authenticity_token"#i',$r,$token);

        sleep(10);
        
        
        $headers = array('Accept: application/json, text/javascript, */*; q=0.01');
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'X-PHX: true';
        
        $post = 'include_entities=true&status='.$status.'&post_authenticity_token='.$token[1];

        $r = $this->c->post('https://api.twitter.com/1/statuses/update.json',$post,$headers);

       sleep(10);
        
        if(preg_match('#"id_str"\:"\d+?"#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $r = $this->c->get('https://twitter.com');
        preg_match('#"postAuthenticityToken":"(.+?)",#imx',$r,$token);
        
        $post = 'authenticity_token='.$token[1].'&redirect=false&scribe_log=%5B%22%7B%5C%22event_name%5C%22%3A%5C%22web%3Ahome%3Ahome%3Atopnav%3Alogout%5C%22%2C%5C%22noob_level%5C%22%3A4%2C%5C%22internal_referer%5C%22%3Anull%2C%5C%22page%5C%22%3A%5C%22home%5C%22%2C%5C%22_category_%5C%22%3A%5C%22client_event%5C%22%2C%5C%22ts%5C%22%3A1326010327301%7D%22%5D';
        $r = $this->c->post('https://twitter.com/logout',$post);
     }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new TW();
?>