<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class VK extends poster
{
    private $hash = '';
    
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('http://vk.com/login.php');
        
	    
        
        preg_match("#ip_h: '(\w+?)'#i",$r,$ip);  

        
       $r = $this->c->post('https://login.vk.com/','act=login&to=&al_test=3&_origin='.urlencode('http://vk.com').'&ip_h='.$ip[1].'&email='.$login.'&pass='.$pass.'&expire=');    
    
    }
    
    public function post()
    {
        $url = urlencode($this->url);
        $image = urlencode($this->image);
        $title = urlencode($this->title);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        
        $e = $this->c->get('http://vk.com/feed');
        
        
	
        if(isset($this->page) && !empty($this->page))
        {
            $e = $this->c->get($this->page);
	       
            preg_match('#"wall_oid"\:(.+?),"#',$e,$to);
			
            $toid = $to[1];
	   } else {
	       preg_match('#href="(.+?)"[^>]+?id="myprofile"#',$e,$prof);
           
           $e = $this->c->get('http://vk.com'.$prof[1]);
	   }

        sleep(1);
      
        preg_match('#"post_hash":"(\w+?)"#',$e,$hash);
        
        //preg_match('#"url"\:"http\:\\\\/\\\\/cs(\d+?)\.vk\.com\\\\/upload\.php","hash"\:"(\w+?)","rhash"\:"(\w+?)"#',$e,$up);
        
        preg_match('#"hash"\:"(.+?)","rhash"\:"(.+?)","timehash":\"(.+?)"#',$e,$hashes);
        
        $lhash = $hashes[1];
        $rhash = $hashes[2];
        $time = $hashes[3];
        
        preg_match('#id: (\d+?),#',$e,$tid);
        
        $id = $tid[1];
        
        if(!isset($toid)) $toid = $id;
        
        $post = 'hash='.urlencode($time).'&index=1&url='.$url;
        $r = $this->c->post('http://vk.com/share.php?act=url_attachment',$post);
        
        $image1 = str_replace('/','\/',$this->image);
     
        if(!empty($image1) && !preg_match("#images\: \[.*?".preg_quote(urldecode($image1),'#').".*?\],#",$r))
        {
            preg_match("#images\: \['(.+?)'.*?\],#",$r,$im);
            
            if(!empty($im[1]))
                $image = urlencode($im[1]);
        }
        
        //$post = 'act=save_draft&al=1&hash='.$h[1].'&media1=share%7C&msg='.$url;
        //$r = $this->c->post('http://vk.com/al_wall.php',$post);
        
        $post = 'act=a_photo&url='.$url.'&index=2&image='.$image.'&extra=0';
        $r = $this->c->post('http://vk.com/share.php',$post);       

        preg_match('#parent\.onUploadDone\(\d*?, \{"user_id":(\d+?),"photo_id":(\d+?)\}\);#',$r,$ph);
             
        if(empty($ph[1]))
        {
            $attach = 'undefined_undefined';                    
        } else {   
            $attach = $ph[1].'_'.$ph[2];
            $id = $ph[1];
        }
        
        $this->hash = $hash[1];
     
        $post = 'act=post&al=1&attach1='.$attach.'&attach1_type=share&description='.$text.'&extra=0&extra_data=&facebook_export=&friends_only=&hash='.$hash[1].'&message=&note_title=&official=&photo_url='.$image.'&status_export=&title='.$title.'&to_id='.$toid.'&type=all&url='.$url;
        $r = $this->c->post('http://vk.com/al_wall.php',$post);
        sleep(5);
        
        if(preg_match('#class="reply_fakebox_wrap"#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $this->c->get('https://login.vk.com/?act=logout&hash='.$this->hash.'&from_host=vkontakte.ru&from_protocol=http');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new VK();
?>