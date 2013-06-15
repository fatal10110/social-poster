<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class FC extends poster
{   
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
         $this->c->get('https://www.facebook.com/');
        
       $r = $this->c->post('https://login.facebook.com/login.php?login_attempt=1','email='.$login.'&pass='.$pass,false,array(CURLOPT_COOKIE => 'reg_fb_ref=https%3A%2F%2Fwww.facebook.com%2Fcheckpoint%2F%3Fnext'));
 
        if(preg_match('#<form class="checkpoint"#',$r))
        {
            preg_match("/input type=\"hidden\" name=\"fb_dtsg\" value=\"(.*?)\"/", $r, $fbdt);
            preg_match('#name="nh" value="(.+?)"#',$r,$nh);
           
            $r = $this->c->post('https://www.facebook.com/checkpoint/?next','fb_dtsg='.urlencode($fbdt[1]).'&nh='.$nh[1].'&submit%5BContinue%5D=%D0%9F%D1%80%D0%BE%D0%B4%D0%BE%D0%BB%D0%B6%D0%B8%D1%82%D1%8C');   

            preg_match("/input type=\"hidden\" name=\"fb_dtsg\" value=\"(.*?)\"/", $r, $fbdt);
            preg_match('#name="nh" value="(.+?)"#',$r,$nh);
            
            $r = $this->c->post('https://www.facebook.com/checkpoint/?next','fb_dtsg='.urlencode($fbdt[1]).'&nh='.$nh[1].'&submit%5BThis+is+Okay%5D=%D0%AD%D1%82%D0%BE+%D0%BD%D0%BE%D1%80%D0%BC%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE');   

            preg_match("/input type=\"hidden\" name=\"fb_dtsg\" value=\"(.*?)\"/", $r, $fbdt);
            preg_match('#name="nh" value="(.+?)"#',$r,$nh);
            
            $r = $this->c->post('https://www.facebook.com/checkpoint/?next','fb_dtsg='.urlencode($fbdt[1]).'&nh='.$nh[1].'&name_action_selected=save_device&submit%5BContinue%5D=%D0%9F%D1%80%D0%BE%D0%B4%D0%BE%D0%BB%D0%B6%D0%B8%D1%82%D1%8C');   
            $r = $this->c->post('https://www.facebook.com/checkpoint/?next','fb_dtsg='.urlencode($fbdt[1]).'&nh='.$nh[1].'&name_action_selected=save_device&submit%5BContinue%5D=%D0%9F%D1%80%D0%BE%D0%B4%D0%BE%D0%BB%D0%B6%D0%B8%D1%82%D1%8C');   
        }
    
    }
    
    public function post()
    {
        
        $url = urlencode($this->url);
        $image = urlencode($this->image);
        $title = urlencode($this->title);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        
        $r = $this->c->get('https://www.facebook.com/profile.php?sk=wall');
       
        if(isset($this->page) && !empty($this->page))
        {
            $r = $this->c->get($this->page);

    		preg_match('#name="[^"]*?targetid"[^>]+?value="(\d+?)"#', $r, $id);   
            preg_match('#envFlush\(\{"user"\:"(\d+?)"#',$r,$id2);
        } else {
            preg_match('#envFlush\(\{"user"\:"(\d+?)"#',$r,$id);
            $id2 = $id;       
        }
        
        preg_match('#name="[^"]*?composerid"[^>]+?value="(.+?)"#i', $r, $compid);    
        preg_match("/input[^>]+?name=\"post_form_id\"[^>]+?value=\"(.*?)\"/", $r, $form_id);

        sleep(1);
        

        preg_match("/input type=\"hidden\" name=\"fb_dtsg\" value=\"(.*?)\"/", $r, $fbdt);

        $post = 'post_form_id='.$form_id[1].'&fb_dtsg='.$fbdt[1].'&xhpc_composerid='.urlencode($compid[1]).'&xhpc_targetid='.$id[1].'&xhpc_context=profile&xhpc_fbx=1&xhpc_timeline=1&xhpc_ismeta=1&xhpc_message_text='.$desc.'&xhpc_message='.$desc.'&aktion=post&app_id=2309869772&UIThumbPager_Input=0&attachment[params][metaTagMap][0][http-equiv]=content-type&attachment[params][metaTagMap][0][content]&attachment[params][medium]=101&attachment[params][urlInfo][canonical]&attachment[params][urlInfo][final]&attachment[params][urlInfo][user]='.$url.'&attachment[params][favicon]=&attachment[params][title]='.$title.'&attachment[params][fragment_title]=&attachment[params][external_author]=&attachment[params][summary]='.$text.'&attachment[params][url]=http%3A%2F%2Fa4.sphotos.ak.fbcdn.net&attachment[params][ttl]=0&attachment[params][error]=1&attachment[params][og_info][guesses][0][0]=og%3Aurl&attachment[params][og_info][guesses][0][1]=http%3A%2F%2Fa4.sphotos.ak.fbcdn.net&attachment[params][og_info][guesses][1][0]=og%3Atitle&attachment[params][og_info][guesses][1][1]=test2&attachment[params][responseCode]=206&attachment[params][images][0]='.$image.'&attachment[type]=100&composertags_place=&composertags_place_name=&composer_predicted_city=&composer_session_id=1321537758&is_explicit_place=&audience[0][value]=80&composertags_city=&disable_location_sharing=false&nctr[_mod]=pagelet_wall&lsd&post_form_id_source=AsyncRequest&__user='.$id2[1].'&__a=1&phstamp=165816645987298977519';

        $r = $this->c->post('https://www.facebook.com/ajax/profile/composer.php',$post);
        sleep(5);
		
        if(preg_match('#for \(;;\);\{"__ar":1,"payload"#',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $this->c->get('https://www.facebook.com/logout.php');
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new FC();
?>