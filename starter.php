#!Z:\usr\local\php5\php-cgi.exe -q
<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */
 
/*
if ($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR'])
    exit();
*/

set_time_limit(0);
ignore_user_abort(true);

ob_start();//start buffer output

echo "start posting";
session_write_close();//close session file on server side to avoid blocking other requests
header("Content-Length: ".ob_get_length());//send length header
header("Connection: close");
ob_end_flush();flush();//really send content, can't change the order:1.ob buffer to normal buffer, 2.normal buffer to output
//continue do something on server side
ob_start();

require_once ('inc/functions.php');


function starter()
{    
    /** Plugin occupation level **/
    $cooks = scandir('other/cooks/');
    $cooks = count($cooks) - 2;
    /****************************/

    if ($cooks >= 5) die();

    if (get_magic_quotes_gpc())
        $_GET = array_map('sp_strip', $_GET);
    
    require ('inc/key.php');
    
    if ($start_key === $_GET['sec'] || is_sapi()) 
    {
        require_once ('inc/crypt.php');
        require_once ('inc/core/cURL.class.php');
        require_once ('inc/core/sp_files.class.php');
        require_once ('inc/core/status.class.php');
        require_once ('inc/core/starter.class.php');
    
        $starter = new starter();
        
        die();
    
    }
}

starter();


ob_end_clean();
?>