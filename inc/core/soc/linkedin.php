<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class LINKEDIN extends poster
{
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $r = $this->c->get('https://www.linkedin.com/uas/login?goback&trk=hb_signin');

        preg_match('#name="csrfToken"[^>]+?value="(ajax\:\d+?)"#',$r,$csrf);
        preg_match('#name="session_redirect"[^>]+?value="([^"]+?)"#',$r,$sess_re);
        preg_match('#name="sourceAlias"[^>]+?value="([^"]+?)"#',$r,$sal);
        
        $post = 'source_app=&session_key='.$login.'&session_password='.$pass.'&signin=Sign%20In&session_redirect='.urlencode($sess_re[1]).'&csrfToken='.urlencode($csrf[1]).'&sourceAlias='.urlencode($sal[1]);
        
        $headers = array('X-IsAJAXForm: 1','X-Requested-With: XMLHttpRequest');
        
        $this->c->post('https://www.linkedin.com/uas/login-submit',$post,$headers);

    }
    
    public function post()
    {
        $url = urlencode($this->url);
        $image = urlencode($this->image);
        $title = urlencode($this->title);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        
        $r = $this->c->get('http://www.linkedin.com/home');
        
        preg_match('#name="csrfToken"[^>]+?value="(ajax\:\d+?)"#',$r,$csrf);
        preg_match('#name="sourceAlias"[^>]+?value="([^"]+?)"#',$r,$sal);
        
        $headers = array('X-Requested-With: XMLHttpRequest');
        
        $r = $this->c->get('http://www.linkedin.com/share?getPreview=&url='.$url,$headers);
        
        if(preg_match('#<ticketStatusUrl>([^<]+?)</ticketStatusUrl>#',$r,$tik))
        {
        
            $u = html_entity_decode ($tik[1]);
            if(empty($u)) return;
        
            $r = $this->c->get($u);
        
            preg_match('#<forwardUrl>([^<]+?)</forwardUrl>#',$r,$fw);
        
            $u = html_entity_decode ($fw[1]);   
        
            if(empty($u)) return;     
        
            $r = $this->c->get($u);
        }
        
        preg_match('#data-entity-id="(\d+?)"#',$r,$eid);
        
        if(!isset($eid[1]))
            return '0';
        
        $post = 'ajax=true&contentImageCount=1&contentImageIndex=0&contentImage='.$image.'&contentEntityID='.$eid[1].'&contentUrl='.$url.'&postText='.$desc.'&contentTitle='.$title.'&contentSummary='.$text.'&contentImageIncluded=true&%23=&postVisibility=EVERYONE&submitPost=&tetherAccountID=&tweetThisOn=false&postToMFeedDefaultPublic=true&csrfToken='.urlencode($csrf[1]).'&sourceAlias='.urlencode($sal[1]);
        
        
        
        $r = $this->c->post('http://www.linkedin.com/share?submitPost=',$post);
        
        sleep(5);
        
        if(preg_match('#<responseInfo>SUCCESS</responseInfo>#',$r))
            return '1';
        
        return '0';
   }
    
    public function logout()
    {
        $r = $this->c->get('http://www.linkedin.com/home');
        preg_match('#name="csrfToken"[^>]+?value="(ajax\:\d+?)"#',$r,$csrf);
        $r = $this->c->get('https://www.linkedin.com/uas/logout?session_full_logout=&csrfToken='.urlencode($csrf[1]).'&trk=hb_signout');        
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new LINKEDIN();
?>