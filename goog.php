<?php

/**
 * @author admin
 * @copyright 2012
 */
 
 require_once('inc/core/cURL.class.php');
 $cook = dirname(realpath(__FILE__)).'/other/goog_act.txt';

 $c = new cURL(true,$cook); 
 
 if(empty($_POST) && file_exists($cook)) unlink($cook);
 
if(isset($_POST['challengestate']))
{
    $post = http_build_query($_POST);

    $r = $c->post('https://accounts.google.com/LoginVerification',$post);
    
    if(preg_match('#<meta http-equiv="refresh" content="\d;url=https://plus.google.com"></meta>#',$r))
        exit('<center><h1>Activated! Now you can close this window.</h1></center>');
        
    echo str_ireplace('action="LoginVerification"','action=""',$r);

} elseif(isset($_POST['dsh'])) {
    $post = 'continue=https%3A%2F%2Fplus.google.com&service=oz&dsh='.$_POST['dsh'].'&hl=en&GALX='.$_POST['GALX'].'&pstMsg=1&dnConn=https%3A%2F%2Faccounts.youtube.com&checkConnection=&checkedDomains=&timeStmp=&secTok=&Email='.$_POST['Email'].'&Passwd='.$_POST['Passwd'].'&signIn=tema&PersistentCookie=yes&rmShown=1';
    $r = $c->post('https://accounts.google.com/ServiceLoginAuth',$post);
    
    if(preg_match('#<meta http-equiv="refresh" content="\d;url=https://plus.google.com"></meta>#',$r))
        exit('<center><h1>Activated! Now you can close this window.</h1></center>');
    
    echo str_ireplace('action="LoginVerification"','action=""',$r);
} else {
    
    $r = $c->get('https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com');

    echo str_ireplace('action="https://accounts.google.com/ServiceLoginAuth"','action=""',$r);
}
?>