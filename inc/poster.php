<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */
 require(SP_PDIR.'/inc/key.php');
 require(SP_PDIR.'/inc/crypt.php');
 require(SP_PDIR.'/inc/core/sp_files.class.php');
 
$acc_id = array();
$grp_id = array();

if(is_array($accs))
{
    foreach ($accs as $id)
        $acc_id[] = (int)$id;
}    

if(is_array($mgrps))
{
    foreach($mgrps as $id)
        $grp_id[] = (int)$id;
}

if($acc_id != array())
{
    $res = $wpdb->get_results('SELECT `post_on`,`pages`,`id`,`soc`,`login`,`pass`
                                FROM `' . $wpdb->base_prefix . 'sp_accs`
                                    WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" 
                                            AND `id` IN ("' . implode('","', $acc_id) . '")');
}

if($grp_id != array())
{
    $res2 = $wpdb->get_results('SELECT `t1`.`emails`,`t1`.`login`,`t1`.`pass`,`t2`.`smpt`,`t2`.`port`,`t2`.`ssl`,`t1`.`id`
                                FROM `' . $wpdb->base_prefix . 'sp_mail_groups` AS `t1`
                                INNER JOIN `' . $wpdb->base_prefix . 'sp_mail_services` AS `t2` ON `t1`.`service` = `t2`.`id`
                                    WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" 
                                            AND `t1`.`id` IN ("' . implode('","', $grp_id) . '")');

}
    
if($res || $res2)
{    
    $serv = (sp_get_opt('sp_conn_method',1) == 1) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';    
    
    $args = array(
            'url' => $url, 
            'title' => $title, 
            'text' => $text, 
            'image' => $image, 
            'guid' => $guid,
            'post' => $post_id,
            'uid' => $current_user->ID,
            'serv' => $serv,
            'path' => $path,
            'fpath' => ABSPATH,
    );
    
    $vals = array();
    $arr = array();
    
    if($res)
    {
        foreach($res as $acc)
        {   
            $a = $args;
            
            $a['soc'] = $acc->soc;
            $a['type'] = 'soc';
            $a['login'] = $acc->login;
            $a['pass'] = $acc->pass;
            $a['desc'] = '';
            
            if(isset($_POST['sp_soc']['sp_'.$acc->id.'-'.$acc->soc]) && is_array($_POST['sp_soc']['sp_'.$acc->id.'-'.$acc->soc]))
            {
                    $spec_soc = $_POST['sp_soc']['sp_'.$acc->id.'-'.$acc->soc];
                    
                    if(isset($spec_soc['text']))
                        $a['text'] = $spec_soc['text'];
                        
                    if(isset($spec_soc['desc']))
                        $a['desc'] = $spec_soc['desc'];
                                        
                    if(isset($spec_soc['title']))
                        $a['title'] = $spec_soc['title'];
                    
                    if(isset($spec_soc['image']))
                        $a['image'] = $spec_soc['image'];
            
                    if(!empty($spec_soc['page']))
                        $pages = explode("\r\n",trim($spec_soc['page']));

            } elseif(!empty($acc->pages)) 
                $pages = ($acc->post_on == 1 || $acc->soc == '13') ? unserialize($acc->pages) : '';
            
            if($acc->soc == 13 && empty($pages)) continue;

            $a['ftext'] = strip_tags($a['text']);
            $a['ftitle'] = $a['ftitle'];
            $a['text'] = soc_cut_text(strip_tags($a['text']),140).'...';
            $a['title'] = soc_cut_text($a['title'],75);
            
                        
            if(isset($pages) && !empty($pages))
            {
                $w  = '';                
                
                foreach($pages as $page)
                {
                    $a['page'] = $page;
                    
                    $vals[] = '("'. (int)$acc->id .'","3","'. (int)$post_id .'","'. (int)$current_user->ID .'",NOW(),' . $wpdb->prepare('%s',$page) . ',0)';
                    $arr[] = $a;
                }
                
            } else {
                $vals[] = '("'. (int)$acc->id .'","3","'. (int)$post_id .'","'. (int)$current_user->ID .'",NOW(),"",0)';

                $arr[] = $a;
            }
            
            unset($page,$pages);
        }
        
    }
     
    if($res2)
    {    
        foreach($res2 as $grp)
        {
            $a = $args;
            $a['type'] = 'mail';
            $a['login'] = $grp->login;
            $a['pass'] = $grp->pass;
            $a['smpt'] = $grp->smpt;
            $a['ssl'] = $grp->ssl;
            $a['port'] = $grp->port;
            $a['emails'] = $grp->emails;

            if(isset($_POST['sp_mail']['sp_'.$grp->id]) && is_array($_POST['sp_mail']['sp_'.$grp->id]))
            {
                    $spec_soc = $_POST['sp_mail']['sp_'.$grp->id];
                    
                    if(isset($spec_soc['text']))
                        $a['text'] = stripslashes($spec_soc['text']);
                    
                    if(isset($spec_soc['title']))
                        $a['title'] = stripslashes($spec_soc['title']);
            }
                      
            $arr[] = $a;
                    
            $vals[] = '("'. (int)$grp->id .'","3","'. (int)$post_id .'","'. (int)$current_user->ID .'",NOW(),"",1)';            
        }
    }
    
    if(!empty($vals))
    {   
        $vals = implode(",",$vals);
        $wpdb->query('INSERT INTO `' . $wpdb->base_prefix . 'sp_logs` (`acc`,`status`,`post`,`user`,`date`,`post_to`,`type`) VALUES '.$vals);
            
        $id = $wpdb->insert_id;
        
        $w = '';
        
        foreach($arr as $key => $val)
        {
            $val['log'] = $key + $id;
            $w .= base64_encode(serialize($val))."\r\n";   
        }
    
    }
    
    $queue = new SP_FILES(SP_PDIR . '/other/queue.txt');
    
    $w = $queue->ffread() . trim($w);
    $queue->ffwrite($w);
    
    $queue->ffunlock();
    
    
    
    if(sp_get_opt('sp_run_method') == '1' && file_exists(SP_PDIR.'/other/cli.txt'))
    {
        $cli = file_get_contents(SP_PDIR.'/other/cli.txt');
        $script = SP_PDIR.'/starter.php';
        sp_exec_nowait($cli.' '.$script.' sec='.$start_key.' cli=1');
    } else {
        $fp = fsockopen($serv, 80, $errno, $errstr, 5);
        
        if ($fp) {
            $out = "GET /" . $path . '/starter.php?sec=' . urlencode($start_key) . " HTTP/1.1\r\n";
            $out .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
            $out .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
        
        fwrite($fp, $out);
        fgets($fp, 128);
        sleep(2);
        fclose($fp);   ;
        }
    }
    
}
?>