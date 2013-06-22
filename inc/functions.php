<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */
 
 
function sp_writeable($file)
{
    if(!file_exists($file)) return false;
        
    if(!is_writeable($file))
        if(!chmod($file,0777)) return false;
        
    return true;
}

function sp_get_opt($opt,$default = false)
{
    if(is_multisite())
        switch_to_blog(1);
        
        $opt = get_option($opt,$default);
        
    if(is_multisite())
        restore_current_blog();
        
    return $opt;
}

function sp_upd_opt($opt,$val)
{
    if(is_multisite())
        switch_to_blog(1);
        
    update_option($opt,$val);
        
    if(is_multisite())
        restore_current_blog();
}

function sp_add_opt($opt,$val)
{
    if(is_multisite())
        switch_to_blog(1);
        
        add_option($opt,$val);
        
    if(is_multisite())
        restore_current_blog();
}

function sp_del_opt($opt)
{
    if(is_multisite())
        switch_to_blog(1);
        
        delete_option($opt);
        
    if(is_multisite())
        restore_current_blog();
}

/**
 * sp_rnd()
 * 
 * Generating random values
 * 
 * @param integer $min - Minimum characters
 * @param integer $max - Maximum characters
 * 
 * @return str - Random values string
 */
function sp_rnd($max = 1,$min = 1)
{
    $len = mt_rand($min, $max);
    $str = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'X',
        'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');


    for ($i = 0; $i < $len; $i++)
        $cod .= $str[mt_rand(0, count($str) - 1)];

    return $cod;
}

/**
 * sp_safe_redirect()
 * 
 * Safe JS Redirect
 * 
 * @uses wp_sanitize_redirect() - WP
 * @uses wp_validate_redirect() - WP
 * @uses admin_url() - WP
 * @uses esc_url() - WP
 * 
 * @param Str $location - Where to redirect the user
 */
function sp_safe_redirect($location = false)
{
    if (!$location)
        $location = $_SERVER['REQUEST_URI'];

    $location = wp_sanitize_redirect($location);
    $location = wp_validate_redirect($location, admin_url());
?>
    
    <script type="text/javascript">
    <!--
        window.location= <?php echo "'" . $location . "'"; ?>;
    //-->
    </script>
    
    <?php
    exit;
}


/**
 * soc_cut_text()
 * 
 * Cutting the text to the required length
 * 
 * 
 * @param Str $str - The string
 * @param int $len - Maximal length
 * 
 * @return str - Cutted text
 */
function soc_cut_text($str, $len)
{
    if (empty($str))
        return false;
    if (mb_strlen($str) <= $len)
        return $str;

    $arr = array('.', ',', '!', ' ', ':', ';', "\n");
    $s = mb_substr($str, $len - 1, 1);


    if (in_array($s, $arr))
        return mb_substr($str, 0, $len);
    else {
        $s = mb_substr($str, 0, $len);
        $len = array();

        foreach ($arr as $v) {
            if (($pos = mb_strrpos($s, $v)))
                $len[] = $pos;
        }

        if ($len == array())
            return mb_substr($str, 0, $len);

        return mb_substr($s, 0, max($len));
    }
}

   function sp_utf16_urlencode ( $str ) 
    {
        # convert characters > 255 into HTML entities
        $convmap = array( 0x0000, 0xFFFF, 0, 0xFFFF );
        $str = mb_encode_numericentity( $str, $convmap, "UTF-8");

        $str = preg_replace_callback( '/&#([0-9a-fA-F]{2,5});/i', 
        create_function(
            // Использование одиночных кавычек в данном случае принципиально,
            // альтернатива - экранировать все символы '$'
            '$matches',
            '$dechex = dechex($matches[1]);
             if(($len = strlen($dechex)) < 4) return "\\\\\\\\\\\\\\\\u".str_repeat("0",4-$len).$dechex;
             else return "\\\\\\\\\\\\\\\\u".$dechex;'
        ), $str );
        return $str;
    }
    
    function sp_myUrlEncode($string) 
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        
        return str_replace($entities, $replacements, urlencode($string));
    }
    
function sp_strip(&$arg)
{
    if(is_array($arg))
        return array_map('sp_strip',$arg);
    else
        return trim(stripslashes($arg));
}

function sp_user_rights()
{
    global $current_user;
    
    $user_can = sp_get_opt('sp_min_role');   
    if((!is_multisite() && $user_can == 'manage_network') || $user_can == 'super_admin')
            $user_can = 'edit_dashboard';
    
    if(current_user_can($user_can))
        return true;
    
    if(get_user_meta($current_user->ID, 'sp-can', true))
        return true;
    
    return false;
}

function sp_is_filepath($path)
{
    $path = trim($path);
    if(preg_match('/^[^*?"<>|:]*$/',$path)) return true; // good to go

    if(!defined('WINDOWS_SERVER'))
    {
        $tmp = dirname(__FILE__);
        if (strpos($tmp, '/', 0)!==false) define('WINDOWS_SERVER', false);
        else define('WINDOWS_SERVER', true);
    }
    /*first, we need to check if the system is windows*/
    if(WINDOWS_SERVER)
    {
        if(strpos($path, ":") == 1 && preg_match('/[a-zA-Z]/', $path[0])) // check if it's something like C:\
        {
            $tmp = substr($path,2);
            $bool = preg_match('/^[^*?"<>|:]*$/',$tmp);
            return ($bool == 1); // so that it will return only true and false
        }
        return false;
    }
    //else // else is not needed
         return false; // that t
}

function is_sapi()
{
    $sapi = array('cli','cgi','cgi-fcgi');
    
    if(in_array(PHP_SAPI,$sapi))
        return true;
    
    return false;
}

function sp_exec_nowait($cmd) {
  //$cmd = escapeshellcmd($cmd);
  //echo $cmd;
  //exit();
  if (substr(php_uname(), 0, 7) == "Windows"){
    pclose(popen('start "" /B '.$cmd, "r")); 
  } else { //*nix
    exec($cmd . " > /dev/null &");  
  } 
}
?>