<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class TMBLR extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('https://www.tumblr.com/login');
        
        preg_match('#name="hk"[^>]+?value="(.*?)"#', $r, $hk);
            
        preg_match('#name="recaptcha_public_key"[^>]+?value="(.*?)"#', $r, $re);
        preg_match('#name="form_key"[^>]+?value="(.*?)"#', $r, $form);
		$r = $this->c->post('https://www.tumblr.com/svc/log/capture/logged_out_dashboard','form_key='.urlencode($form[1]).'&log%5Bcontext%5D%5Bid%5D='.urlencode($form[1]).'&log%5Bcontext%5D%5Blanguage%5D=en-us&log%5Bcontext%5D%5Bpath%5D=%2F&log%5Bsignup_button%5D%5Blogin%5D=1&log%5Bsearch%5D=&log%5Bexplore%5D=&log%5Bexplore_link%5D=0&log%5Bspotlight%5D=&log%5Bspotlight_link%5D=0&log%5Bkeycommands%5D=0&log%5Bauto_pager%5D=1');
		$post = 'user%5Bemail%5D='.$login.'&user%5Bpassword%5D='.$pass.'&tumblelog%5Bname%5D=&user%5Bage%5D=&recaptcha_public_key='.urlencode($re[1]).'&recaptcha_response_field=&hk='.urlencode($hk[1]).'&form_key='.urlencode($form[1]);
        $r = $this->c->post('https://www.tumblr.com/login',$post);
		
    }
    
    public function post()
    {
        $url = $this->url;
        $image = $this->image;
        $title = str_replace('"','\"',$this->title);
        $text = str_replace('"','\"',$this->text);
        
        $text = str_replace("\r",'',$text);
        $text = str_replace("\n",'\r\n',$text);
		
        $r = $this->c->get('https://www.tumblr.com/dashboard'); 	
        
            
        preg_match('#name="form_key"[^>]+?value="(.*?)"#', $r, $fkey);
        preg_match('#name="t"[^>]+?value="(.*?)"#', $r, $ch);
        
        $headers = array('X-Requested-With: XMLHttpRequest');
        
        if(empty($image))
            $post = '{"form_key":"'.$fkey[1].'","post":{},"context_id":"","context_page":"dashboard","editor_type":"rich","is_rich_text[one]":"0","is_rich_text[two]":"1","is_rich_text[three]":"0","channel_id":"'.$ch[1].'","post[slug]":"","post[source_url]":"'.$url.'","post[date]":"","post[type]":"regular","post[one]":"'.$title.'","post[two]":"<p>'.$text.'</p>","post[tags]":"","post[publish_on]":"","post[state]":"0"}';
        else
            $post = '{"form_key":"'.$fkey[1].'","context_id":"","context_page":"dashboard","editor_type":"rich","is_rich_text[one]":"0","is_rich_text[two]":"1","is_rich_text[three]":"0","channel_id":"'.$ch[1].'","post[slug]":"","post[source_url]":"'.$url.'","post[date]":"","post[three]":"","MAX_FILE_SIZE":"10485760","post[type]":"photo","post[two]":"<a href=\"'.$url.'\">'.$title.'</a></p>\r\n<p></p>\r\n<p>'.$text.'</p>","post[tags]":"","post[publish_on]":"","post[state]":"0","post[photoset_layout]":"1","post[photoset_order]":"o1","images[o1]":"","photo_src[]":"'.$image.'"}';
	    
        $r = $this->c->post('http://www.tumblr.com/svc/post/update',$post,$headers);

        
        
        if(preg_match('#"errors":false#',$r))
            return '1';
        
        //return '0';
    }
    
    public function logout()
    {
        $this->c->get('http://www.tumblr.com/logout');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new TMBLR();
?>