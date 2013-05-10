<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class YA extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $post = 'login='.$login.'&passwd='.$pass.'&retpath=http%253A%252F%252Fmy.ya.ru%252F&timestamp='.time();
        
        $this->c->post('https://passport.yandex.ru/passport?mode=auth',$post);
    }
    
    public function post()
    {
        $url = urlencode($this->myUrlEncode($this->url));
        $image = sp_myUrlEncode($this->image);
        $title = urlencode($this->title);
        $im = '';
        
        if(!empty($image))
            $im = '<p><img src="'.$image.'" alt=""/></p> ';
            
        $b = urlencode($im.$this->text);
        
        $r = $this->c->get('http://my.ya.ru/posts_add_link.xml');
        

        preg_match('#<input[^>]+?name="sk"[^>]+?value="([^"]+?)"[^>]+?/>#im',$r,$sk);
            
        preg_match("#current_location:\{source:\{id:'(\d+?)',#mi",$r,$id);
        
        $post = 'access_type=public&replies=1&URL='.$url.'&title='.$title.'&body='.$b.'&=&=%20%2B%20&=%D0%9E%D1%82%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D1%82%D1%8C&=%D0%9E%D1%82%D0%BC%D0%B5%D0%BD%D0%B8%D1%82%D1%8C&tags=&tag=&submit_btn=%D0%9E%D1%82%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D1%82%D1%8C&sk='.$sk[1].'&type=link&feed_id='.$id[1];
        
        $this->c->get('http://mc.yandex.ru/clmap/723021?rn=255122&page-url=http%3A%2F%2Fmy.ya.ru%2Fposts_add_link.xml&pointer-click=x:44198:y:32767:t:4068:p:PAA3A%5D2b%5C%5BFA1');
        
        $r = $this->c->post('http://my.ya.ru/ajax/post_do_save.xml',$post);
        
        sleep(10);
        
        if(preg_match('#"status"\: "Success"#',$r)) return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $this->c->get('http://passport.yandex.ru/passport?mode=logout&retpath=http://www.yandex.ru');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}

$poster = new YA();
?>