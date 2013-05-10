<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class PINT extends poster
{
    private $pc = '';
    
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
       $this->pint_get('http://pinterest.com/');
        
        $r = $this->pint_get('https://pinterest.com/login/?next=%2F');
 
        preg_match("#name='csrfmiddlewaretoken'[^>]+?value='(.*?)'#", $r, $csrf);
        preg_match("#name='_ch'[^>]+?value='(.*?)'#", $r, $ch);
 
        $headers = array('Referer: https://pinterest.com/login/?next=%2Flogin%2F');
 
       $r = $this->pint_post('https://pinterest.com/login/?next=%2Flogin%2F','email='.$login.'&password='.$pass.'&next=%2F&csrfmiddlewaretoken='.urlencode($csrf[1]).'&_ch='.urlencode($ch[1]),$headers);
         
    }
    
    public function post()
    {
        $url = $this->url;
        $image = $this->image;
        $text = $this->text;   
        
        
       if(!isset($image) || empty($image)) return '0';
        
        $r = $this->pint_get('http://pinterest.com/');
        
        //$this->pint_get('http://pinterest.com/pin/create/find_images/?url='.urlencode($url));
        
        if(!isset($this->page) || empty($this->page))
            $this->page = 'Products I Love';
             
        if(preg_match('#<li[^>]+?data="(\d+?)">\s+?<span>'.preg_quote($this->page).'</span>#si',$r,$b))
                $board = $b[1];
        
        preg_match("#name='csrfmiddlewaretoken'[^>]+?value='(.*?)'#", $r, $csrf);
        
        $post = array(
            'csrfmiddlewaretoken' => $csrf[1],
            'board' => $board,
            'details' => $text,
            'link' => $url,
            'img_url' => $image,
            'tags' => '',
            'replies' => '',
            'buyable' => '',
        );
        
        $post = http_build_query($post);
        
        sleep(1);
        
        $headers = array('Referer: http://pinterest.com/');
 
        $r = $this->pint_post('http://pinterest.com/pin/create/',$post, $headers);

        sleep(5);
        
        if(preg_match('#"status"\: "success"#',$r))
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
            $this->pc = $cc[1];
        
        return $res;
    }
    
    private function pint_post($url,$post,$headers = false)
    {
        $opt = array(CURLOPT_HEADER => true,CURLOPT_FOLLOWLOCATION => false);
        
        if(!empty($this->pc))
            $opt[CURLOPT_COOKIE] = "_pinterest_sess=".$this->pc;
        
        $res = $this->c->post($url,$post,$headers,$opt);
        
        if(preg_match('#Set-Cookie\: _pinterest_sess="(.+?)";#',$res,$cc))
            $this->pc = $cc[1];
        
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