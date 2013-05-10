<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class LJ extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $this->c->post('https://www.livejournal.com/login.bml','user='.$login.'&password='.$pass.'&action%3Alogin=tema');
    }
    
    public function post()
    {
        $url = sp_myUrlEncode($this->url);
        $title = urlencode($this->title);
        $text = urlencode('<a href="'.$url.'"><img alt="" height="150" src="'.$this->image.'" style="border-width: 0pt; border-style: solid; float: left; margin: 5px;" width="150" /></a><a href="'.$url.'">'.nl2br($this->text).'</a>');

        
        $r = $this->c->get('http://www.livejournal.com/update.bml');
        sleep(1);
        
        preg_match('#<input type=\'hidden\' name="lj_form_auth" value="(.+?)" />#',$r,$form_auth);
        preg_match("#<input type='hidden' name='chal' id='login_chal' value='(.+?)' />#",$r,$chal);

        $post = 'lj_form_auth='.urlencode($form_auth[1]).'&chal='.urlencode($chal[1]).'&response=5514f7cdcd1ad5a9e9a0cd89eebbd491&timezone=200&custom_time=0&user=&password=&usejournal=tema357&date_ymd_mm='.date('m').'&date_ymd_dd='.date('d').'&date_ymd_yyyy='.date('Y').'&time='.urlencode(date('H').":".date('i')).'&date_diff=1&subject='.$title.'&event='.$text.'&switched_rte_on=1&prop_taglist=&prop_current_moodid=&prop_current_mood=&comment_settings=&prop_current_location=&prop_opt_screening=&prop_current_music=&prop_adult_content=&security=public&action%3Aupdate=tema357';

        $r = $this->c->post('http://www.livejournal.com/update.bml',$post);
        
        if(preg_match('#<a href="/editjournal\.bml\?journal=.+?&itemid=\d+?">#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $r = $this->c->get('http://www.livejournal.com/');
        
        preg_match('#<input[^>]+?name="user"[^>]+?value="(.+?)" />#',$r,$user);
        preg_match('#<input[^>]+?name="sessid"[^>]+?value="(.+?)" />#',$r,$sessid);
        
        $this->c->post('http://www.livejournal.com/logout.bml','user='.urlencode($user[1]).'&sessid='.$sessid[1].'&_submit=%D0%92%D1%8B%D1%85%D0%BE%D0%B4+');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new LJ();
?>