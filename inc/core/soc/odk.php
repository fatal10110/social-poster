<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

class ODK extends poster
{
    private $gwt = '';
    private $id = '';
    
    public function login()
    {
        $login = urlencode($this->login);
        $pass = urlencode($this->pass);
        
        $this->c->get('http://www.odnoklassniki.ru');
        $r = $this->c->post('http://www.odnoklassniki.ru/dk?cmd=AnonymLogin&st.cmd=anonymLogin&tkn=503','st.redirect=&st.posted=set&st.email='.$login.'&st.password='.$pass.'&st.remember=on&st.fJS=enabled&st.st.screenSize=1366+x+768&st.st.browserSize=636&st.st.flashVer=10&button_go=%D0%92%D0%BE%D0%B9%D1%82%D0%B8');
    }
    
    public function post()
    {
        $image2 = $this->image;   
        $url = sp_myUrlEncode($this->url);
        $text = urlencode($this->text);
        $desc = urlencode($this->desc);
        $title = urlencode($this->title);
        $image = urlencode($this->image);

         $url2 = urlencode($url);
     
        if(substr($url,-1) == '/')
        $url = substr($url,0,strlen($url)-1);
        $url = urlencode($url);
 /*  
       $r = $this->c->get('http://www.odnoklassniki.ru');
        sleep(10);
        
        preg_match('#gwtHash:"(\w+?)"#',$r,$gwt);
        preg_match('#<input value="(.+?)" type="hidden" name="st\.status\.postpostForm">#',$r,$pf);
        preg_match('#/profile/(\d+?)\?#',$r,$id);;
        
        $this->gwt = $gwt[1];
        $this->id = $id[1];
        
        $post = 'originalValue='.$url2.'&st.status.postcontent_textarea='.$url2.'&st.status.postfldtab=1';

        $r = $this->c->post('http://www.odnoklassniki.ru/profile/'.$id[1].'?cmd=parseShareAttachCmd&gwt.requested='.$gwt[1].'&st.cmd=userMain&p_sId=0',$post);
        
        sleep(10);
        
        $image = '';
        $post = "pictureOn=0&gwt.requested=".$gwt[1]."&st.status.postpostForm=".$pf[1]."&st.status.postfldtab=1&st.status.postcontent_textarea=".$url."&st.status.postcomments=&st.status.post_thmb=".$image."&st.status.posts_t=".$title."&st.status.posts_descr=".$text."&st.status.posts_vp=1%3B1&st.status.posts_vi=&st.status.post_surl=".$url."&st.status.post_asurl=&st.status.posts_src=4000&urlPattern=(%3F%3A(%3F%3Ahttps%3F%7Cftp)%3A%5C%2F%5C%2F%7C(%3F%3Amailto%3A)%3F%5B-a-z0-9!%23%24%25%26'*%2B%2F%3D%3F%5E_%60%7B%7C%7D~%5D%5B-.a-z0-9!%23%24%25%26'*%2B%2F%3D%3F%5E_%60%7B%7C%7D~%5D*%40)%3F(%3F%3A(%3F%3A(%3F%3A(%3F%3A%5B%5Cw%D0%B0-%D1%8F%D0%90-%D0%AF-%5D%2B)%5C.)%7B1%2C5%7D)(%3F%3Acom%7Cnet%7Corg%7Cbiz%7Cinfo%7Cname%7Cpro%7Casia%7Caero%7Ccat%7Ccoop%7Ceco%7Cjobs%7Cmobi%7Cmuseum%7Cpost%7Ctel%7Ctravel%7Cxxx%7Cedu%7Cgov%7Cint%7Cmil%7C%D1%80%D1%84%7C%D0%B8%D1%81%D0%BF%D1%8B%D1%82%D0%B0%D0%BD%D0%B8%D0%B5%7Cxn--%5B%5Cw-%5D*%7C%5Ba-z%5D%7B2%7D)%7C(%3F%3A(%3F%3A25%5B0-5%5D%7C2%5B0-4%5D%5B0-9%5D%7C%5B01%5D%3F%5B0-9%5D%5B0-9%5D%3F)%5C.)%7B3%7D(%3F%3A25%5B0-5%5D%7C2%5B0-4%5D%5B0-9%5D%7C%5B01%5D%3F%5B0-9%5D%5B0-9%5D%3F))(%3F%3A%3A%5Cd%7B1%2C5%7D)%3F(%3F%3A%5C%2F%5B%3B%3A%3D%5C%25%23%5C%26%5C%3F%5Cw%5C%2F%D0%B0-%D1%8F%D0%90-%D0%AF%5C.%2B%5C-!_'*()%5D*)%3F&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postpreviewInput=&st.status.postphotoAlbumPolicy=2";
        $r = $this->c->post('http://www.odnoklassniki.ru/profile/'.$id[1].'?st.cmd=userMain&cmd=MiddleColumnTopCard_StatusPost&tkn=9989&p_sId=0',$post);

        sleep(20);*/
        
        $r = $this->c->get('http://share.yandex.ru/go.xml?service=odnoklassniki&url='.$url2.'&title='.$title);

        
        preg_match('#name="st\.posted"[^>]+?value="(.*?)"#i', $r, $posted);
           
        $i = 0;
        
        while(empty($posted[1]))
        {
            sleep(1);
            
            $r = $this->c->get('http://share.yandex.ru/go.xml?service=odnoklassniki&url='.$url2.'&title='.$title);
            preg_match('#name="st\.posted"[^>]+?value="(.*?)"#i', $r, $posted);
            
            if($i == 3)
                return '0';
            
            $i++;
        }
        
        sleep(1);
        
        //$post = 'st.posted='.$posted[1].'&st.comments='.$desc.'&button_submit=go&hook_form_button_click=button_submit&st._t='.$title.'&st._d='.$text.'&st._pv=1%3B1&st._vid=&st._surl='.$url.'&st._asurl=&st.s=0';
        $post = 'st.posted='.$posted[1].'&st.comments='.$desc.'&button_submit=go&hook_form_button_click=button_submit&st._t='.$title.'&st._d='.$text.'&st._pv=1%3B1&st._vid=&st._surl='.$url.'&st._asurl=&st.s=0';
        
        
        
        if(strripos($r, urldecode($image)) === false)
            $post .= '&pictureOn=0&st._thmb';
        else
            $post .= '&st._thmb='.$image;
            
        

        $r = $this->c->post('http://www.odnoklassniki.ru/dk?cmd=AddSharedResourceWB&st.cmd=addShare&tkn=2378',$post);
        
        sleep(1);
        
        if(preg_match('$<a class="lp" onclick="showLinks\(\);return false;" href="#">$',$r))
            return '1';
        
        return '0';
    }
    
    public function logout()
    {
        $this->c->post('http://odnoklassniki.ru/?cmd=PopLayer&st.cmd=userMain&st.layer.cmd=PopLayerLogoffUser&st._aid=MRGT_Logoff&gwt.requested='.$this->gwt.'&p_sId=1546628550','');
        $post = 'gwt.requested='.$this->gwt.'&st.layer.posted=set&button_logoff=clickOverGWT';
        $r = $this->c->post('http://odnoklassniki.ru/profile/'.$this->id[1].'?st.cmd=userMain&cmd=PopLayerLogoffUser&tkn=268&st.layer.cmd=PopLayerLogoffUser&p_sId=1546628550',$post);
    }
    
    public function __desctruct()
    {
        $this->c = null;
        unset($this->c);
    }
}


$poster = new ODK();
?>