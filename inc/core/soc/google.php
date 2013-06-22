<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class GOOGLE extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com');

        preg_match('#name="dsh"[^>]+?value="(\d+?)"#',$r,$dsh);
        preg_match('#name="GALX"[^>]+?value="(\w+?)">#',$r,$glax);

        $post = 'continue=https%3A%2F%2Fplus.google.com%2F%3Fhl%3Dru%26gpsrc%3Dgplp0&service=oz&dsh='.$dsh[1].'&hl=ru&GALX='.$glax[1].'&pstMsg=1&dnConn=&checkConnection=youtube%3A124%3A1&checkedDomains=youtube&timeStmp=&secTok=&Email='.$login.'&Passwd='.$pass.'&signIn=%D0%92%D0%BE%D0%B9%D1%82%D0%B8&PersistentCookie=yes&rmShown=1';

        $r = $this->c->post('https://accounts.google.com/ServiceLoginAuth',$post);

    }
    
    public function post()
    {
        
        $url2 = sp_myUrlEncode($this->url);
        $url = urlencode($url2);
        $image = urlencode(sp_utf16_urlencode($this->image));
        $title = urlencode(sp_utf16_urlencode($this->title));
        $text = urlencode(sp_utf16_urlencode($this->text));
        $desc = sp_utf16_urlencode($this->desc);
        
        if(isset($this->page) && !empty($this->page))
        {
			if(substr($this->page,-1) == '/')
                $this->page = substr($this->page,0,strlen($this->page)-1);
            
            $u = $this->page;
            $r = $this->c->get($u.'/');
			
			preg_match('#(\d+?)/stream"#',$r,$oid);
			preg_match('#<base href="(.+?)"#',$r,$u);
			
			$u = $u[1];
			
            $u = substr($u,0,strlen($u)-1);
        } else {
            $u = 'https://plus.google.com';
            $r = $this->c->get($u);
            preg_match('#\"https://plus\.google\.com/(\d+?)\"#',$r,$oid);
        }                


        sleep(1);
        preg_match('#"https://csi\.gstatic\.com/csi","(.+?)"#',$r,$at);
        
        
        $r = $this->c->post($u.'/_/sharebox/linkpreview/?c='.$url2.'&t=1&slpf=0&ml=1&_reqid=1458350&rt=j','susp=false&at='.$at[1]);
        sleep(1);
                     
        $desc = str_replace('\\\\\\\\','\\',$desc);
        $enc = '%5B%22'.$desc.'%22%2C%22oz%3A'.$oid[1].'.'.sp_rnd(10,8).'.0%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5C%22%5Bnull%2Cnull%2Cnull%2C%5C%5C%5C%22'.$title.'%5C%5C%5C%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5C%5C%5C%22'.$text.'%5C%5C%5C%22%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22'.$url.'%5C%5C%5C%22%2Cnull%2C%5C%5C%5C%22text%2Fhtml%5C%5C%5C%22%2C%5C%5C%5C%22document%5C%5C%5C%22%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22%5C%5C%5C%22%2Cnull%2Cnull%5D%2C%5Bnull%2C%5C%5C%5C%22%5C%5C%5C%22%2Cnull%2Cnull%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fprovider%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%5D%5D%5C%22%2C%5C%22%5Bnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22'.$image.'%5C%5C%5C%22%5D%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22'.$url.'%5C%5C%5C%22%2Cnull%2C%5C%5C%5C%22image%2Fjpeg%5C%5C%5C%22%2C%5C%5C%5C%22photo%5C%5C%5C%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C392%2C154%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22'.$image.'%5C%5C%5C%22%2Cnull%2Cnull%5D%2C%5Bnull%2C%5C%5C%5C%22'.$image.'%5C%5C%5C%22%2Cnull%2Cnull%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22images%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fprovider%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%5D%5D%5C%22%5D%22%2Cnull%2C%22%7B%5C%22aclEntries%5C%22%3A%5B%7B%5C%22scope%5C%22%3A%7B%5C%22scopeType%5C%22%3A%5C%22anyone%5C%22%2C%5C%22name%5C%22%3A%5C%22%5C%5Cu0412%5C%5Cu0441%5C%5Cu0435%5C%22%2C%5C%22id%5C%22%3A%5C%22anyone%5C%22%2C%5C%22me%5C%22%3Atrue%2C%5C%22requiresKey%5C%22%3Afalse%7D%2C%5C%22role%5C%22%3A20%7D%2C%7B%5C%22scope%5C%22%3A%7B%5C%22scopeType%5C%22%3A%5C%22anyone%5C%22%2C%5C%22name%5C%22%3A%5C%22%5C%5Cu0412%5C%5Cu0441%5C%5Cu0435%5C%22%2C%5C%22id%5C%22%3A%5C%22anyone%5C%22%2C%5C%22me%5C%22%3Atrue%2C%5C%22requiresKey%5C%22%3Afalse%7D%2C%5C%22role%5C%22%3A60%7D%5D%7D%22%2Ctrue%2C%5B%5D%2Cfalse%2Cfalse%2Cnull%2C%5B%5D%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%5D';
                   
           
        $r = $this->c->post($u.'/_/sharebox/post/?spam=20&_reqid=1458350&rt=j','spar='.$enc.'&at='.$at[1].'&');

        sleep(5);
        
        if(preg_match('#\\\\"id\\\\":\\\\"\d+?\\\\"#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $this->c->get('https://accounts.google.com/Logout?service=profiles&continue=https://plus.google.com');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new GOOGLE();
?>