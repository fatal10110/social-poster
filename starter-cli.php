<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */


//ignore_user_abort(true);
set_time_limit(0);

require_once ('inc/functions.php');


function starter()
{    
    /** Plugin occupation level **/
    $cooks = scandir('other/cooks/');
    $cooks = count($cooks) - 2;
    /****************************/

    if ($cooks >= 5) die();
    
    require ('inc/key.php');
    
    /*if ($start_key === $_GET['sec']) 
   //{*/
        require_once ('inc/crypt.php');
        require_once ('inc/core/cURL.class.php');
        require_once ('inc/core/sp_files.class.php');
        require_once ('inc/core/status.class.php');
        require_once ('inc/core/starter.class.php');
    
        $starter = new starter();
        
        die();
    
    //}
}

starter();
?>