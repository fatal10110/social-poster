<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class PINT extends poster
{
    private $pc = '';
    private $csrf = '';
    
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
       $this->pint_get('http://pinterest.com/');
        
        $r = $this->pint_get('https://pinterest.com/login/?next=%2F');
 
        preg_match("#name='csrfmiddlewaretoken'[^>]+?value='(.*?)'#m", $r, $csrf);
        preg_match("#name='_ch'[^>]+?value='(.*?)'#", $r, $ch);
        $this->csrf = $csrf[1];
        $headers = array('Referer: https://pinterest.com/login/?next=%2Flogin%2F');
 
       $r = $this->pint_post('https://pinterest.com/login/?next=%2Flogin%2F','email='.$login.'&password='.$pass.'&next=%2F&csrfmiddlewaretoken='.urlencode($csrf[1]).'&_ch='.urlencode($ch[1]),$headers);
       
       
    }
    
    public function post()
    {
        $url = $this->url;
        $image = $this->image;
        $text = $this->text;   
        
        if(!isset($this->page) || empty($this->page)) return '0';
       if(!isset($image) || empty($image)) return '0';
        
        $r = $this->pint_get('http://pinterest.com/');
        
        $this->pint_get('http://pinterest.com/pin/find/?url='.urlencode($url));
        
        $data = urlencode('{"options":{},"module":{"name":"PinCreate","options":{"action":"create","image_url":"'.$image.'","link":"'.$url.'","method":"scraped"},"append":false,"errorStrategy":0},"context":{"app_version":"5a2f6e7"}}');
        $module_path = 'App()>ImagesFeedPage(resource=FindPinImagesResource(url='.$url.'))>Grid()>GridItems()>Pinnable()>ShowModalButton(submodule=[object Object], primary_on_hover=true, color=primary, text=Pin it, class_name=repinSmall, tagName=button, show_text=false, has_icon=true, ga_category=pin_create)';
        
        $r = $this->pint_get('http://pinterest.com/resource/NoopResource/get/?data='.$data.'&source_url=/pin/find/?url='.urlencode($url).'&module_path='.urlencode($module_path));
        
        preg_match('#(<li[^>]+?data-id=\\\"(\d+?)\\\"[^>]+?>)(?:(?!</li>).)*?'.preg_quote('test-test').'[^<]+?</li>#', $r, $bid);
        
        $data = urlencode('{"options":{"board_id":"'.$bid[2].'","description":"'.str_replace('"','\"'.$text).'","link":"'.$url.'","share_facebook":false,"image_url":"'.$image.'","method":"scraped"},"context":{"app_version":"5a2f6e7"}}');
        
        $post = 'source_url='.urlencode('/pin/find/?url='.urlencode($url)).'&data='.$data.'&module_path='.urlencode($module_path.'#Modal(module=PinCreate())');
        

        sleep(1);
        
        $headers = array('X-CSRFToken: '.$this->csrf,'X-NEW-APP: 1','X-Requested-With: XMLHttpRequest');

        $r = $this->pint_post('http://pinterest.com/resource/PinResource/create/',$post, $headers);

        sleep(5);
        
        if(preg_match('#"error"\: null\}\}#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        return true;
    }
    
    private function pint_get($url,$headers = false)
    {
        $opt = array(CURLOPT_HEADER => true,CURLOPT_FOLLOWLOCATION => false);
        
        if(!empty($this->pc))
            $opt[CURLOPT_COOKIE] = "_pinterest_sess=".$this->pc;
        
        $res = $this->c->get($url,$headers,$opt);
        
        if(preg_match('#Set-Cookie\: _pinterest_sess="(.+?)";#',$res,$cc))
        {
            file_put_contents('logs/with_head.html',$res);
            $this->pc = $cc[1];
        }   
        if(preg_match('#Set-Cookie\: csrftoken="(.+?)";#',$res,$csrf))
            $this->csrf = $csrf[1];
        
        return $res;
    }
    
    private function pint_post($url,$post,$headers = false)
    {
        $opt = array(CURLOPT_HEADER => true,CURLOPT_FOLLOWLOCATION => false);
        
        if(!empty($this->pc))
            $opt[CURLOPT_COOKIE] = "_pinterest_sess=".$this->pc;
        
        $res = $this->c->post($url,$post,$headers,$opt);
        
        if(preg_match('#Set-Cookie\: _pinterest_sess="(.+?)";#',$res,$cc)) {
            $this->pc = $cc[1];
        file_put_contents('logs/with_head.html',$res);
        }
        if(preg_match('#Set-Cookie\: csrftoken="(.+?)";#',$res,$csrf))
            $this->csrf = $csrf[1];
            
        return $res;
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new PINT();
?>