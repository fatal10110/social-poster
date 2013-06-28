<?php
/*

Plugin Name: Social Posting

Plugin URI: 

Description: Share your posts on many social networks at the same time

Version: 2.0a

Author: @Fatal@

*/

add_action('admin_menu', 'sp_register_admin_menu');
add_action('init', 'sp_on_init');
add_action('add_meta_boxes', 'sp_add_post_box_setup', 10, 2);
add_action('save_post', 'soc_start_posting',10,2);
add_action('admin_enqueue_scripts', 'sp_plugin_admin_head');
add_action('plugins_loaded', 'sp_lang_init');


define('SP_RES_VER','2.0.0');
define('SP_PDIR',ABSPATH.PLUGINDIR.'/'.dirname( plugin_basename( __FILE__ ) ));
define('SP_PURL',plugins_url('/', __FILE__));

function sp_lang_init()
{
    if( file_exists( SP_PDIR . '/lang/' . get_locale() . '.mo' ) ) 
        load_textdomain('sp_text_domain', SP_PDIR . '/lang/' . get_locale() . '.mo');
        //load_plugin_textdomain( 'sp_text_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
        
}

require_once(SP_PDIR.'/inc/functions.php');
require_once(SP_PDIR.'/inc/ajax.php');

/*
if (get_magic_quotes_gpc())
{
    $_GET = array_map('sp_strip', $_GET);
    $_POST = array_map('sp_strip', $_POST);
}
*/

require_once(SP_PDIR.'/inc/pages/pages.php');

function sp_plugin_admin_head()
{
        echo "<script type='text/javascript'>
        var sp_url = '".SP_PURL."';
        var sp_lang = {
                sure: '". __('Are you sure you want to perform this action?') ."',
                add:    '". __('Add') ."',
                edit:    '". __('Edit') ."',
                alert:    '". __('Alert') ."',
                del:    '". __('Delete') ."',
                cancel:    '". __('Cancel') ."',
                error:    '". __('Error') ."',
                ok:    '". __('OK') ."',
                save:    '". __('Save') ."',
        };
        </script>";
        
    
    wp_enqueue_style('social_poster-admin-ui-css',
                        'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css',
                        false,
                        '1.6',
                        false);
                        
    wp_enqueue_style('sp_style', plugins_url('/css/style.css', __FILE__),SP_RES_VER);
    
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_script('sp_ddslick', plugins_url('/js/ddslick.min.js ', __FILE__), array('jquery'),SP_RES_VER);
    wp_enqueue_script('sp_script', plugins_url('/js/sp.js', __FILE__), array('jquery'),SP_RES_VER);        
}

/**
 * sp_on_init()
 * 
 * Adding user meta if needed.
 * Runs the installation function
 * 
 * @uses add_action - WP
 * @uses update_user_meta - WP
 * @uses sp_get_opt - Social Poster(Declared in inc/functions.php)
 * @uses is_super_admin - WP
 * @uses get_user_meta - WP
 * @uses user_can - WP
 * @uses sp_install - Social Poster(Declared in soc_posting.php)
 * 
 */
function sp_on_init()
{      
    if(is_super_admin())
    {
        add_action( 'admin_notices', 'sp_writeable_notice' );
        add_action( 'network_admin_notices', 'sp_writeable_notice' );
    }
       
    function sp_writeable_notice() {
        $error = array();
    
        $df = ini_get('disable_functions');
        $df = explode(',',$df);
        
        if(!is_array($df)) $df = array();
    
        if(!sp_writeable(SP_PDIR.'/inc/')) $error[] = __('The "inc" folder is not writeable');
        if(!sp_writeable(SP_PDIR.'/other/')) $error[] = __('The "other" folder is not writeable');
        if(!sp_writeable(SP_PDIR.'/other/cooks/')) $error[] = __('The "cooks" folder is not writeable');
        if(!sp_writeable(SP_PDIR.'/other/queue/')) $error[] = __('The "queue" folder is not writeable');
        if(!function_exists('curl_init')) $error[] = __('cURL is not enabled');
        if(in_array('set_time_limit',$df)) $error[] = __('set_time_limit() function is disabled');
        if(in_array('ignore_user_abort',$df)) $error[] = __('ignore_user_abort() function is disabled');
        
        if($error != array())
        {
            echo '<div class="error">
                    <h3><strong>Social Poster</strong></h3>
                        <p>ERROR: ' . implode('</p><p>ERROR: ',$error) . '</p>
                 </div>';
        }
    }
    //Installation function
    sp_install();

    $min = sp_get_opt('sp_min_role');

    if (is_super_admin()) 
    {//Need to add an user meta
    
        add_action('edit_user_profile', 'sp_can_use');
        add_action('edit_user_profile_update', 'sp_can_use_save');

        function sp_can_use_save( $user_id ) 
        { //Allow user to use the social poster plugin
            if (isset($_POST['sp-can']) && $_POST['sp-can'] == '1')
                update_user_meta($user_id, 'sp-can', 1);
            else
                update_user_meta($user_id, 'sp-can', 0);
        }

        function sp_can_use($user)
        {
            if(user_can($user->data->ID,'publish_posts'))
            {
                $sp = get_user_meta($user->data->ID, 'sp-can', true);
                ?>
               	<h3>Social Posterk</h3>
                
                <table class="form-table">
                    <tr>
         			    <th><label for="sp-can"><?php _e('Use the social poster','sp_text_domain'); ?>:</label></th>
                        <td>
                			<input type="checkbox" <?php if($sp) echo 'checked="checked"' ?> name="sp-can" id="sp-can" value="1" class="regular-text" /><br />
               				<span class="description"><?php _e('Allow user to use the Social Poster plugin.','sp_text_domain'); ?></span>
             			</td>
              		</tr>
                
               	</table>        
                <?php
            }
        }
    }
   
}

/**
 * sp_install()
 * 
 * Installing the Social Poster Plugin
 * 
 * @uses sp_get_opt - Social Poster(Declared in inc/functions.php)
 * @uses sp_add_opt - WP
 * @uses WPDB Class - WP
 * @uses sp_rnd - Social Poster(Declared in inc/functions.php)
 * 
 * @global $wpdb - WPDB Class
 * 
 * @return bool true
 */
function sp_install()
{
    global $wpdb;

    if (sp_get_opt('sp_verssion') === false) 
    {
        sp_add_opt('sp_max_acc', '10');
        sp_add_opt('sp_max_post_acc', '5');
        
        if(is_multisite())
            sp_add_opt('sp_min_role', 'manage_network');
        else
            sp_add_opt('sp_min_role', 'edit_dashboard');
            
        sp_add_opt('sp_max_conn', '20');
        sp_add_opt('sp_verssion', '2.0');
        sp_add_opt('sp_run_method', '2');
        sp_add_opt('sp_conn_method',1);
    

        $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_accs`");
                        $wpdb->query("CREATE TABLE `" . $wpdb->base_prefix . "sp_accs` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `login` varchar(50) COLLATE utf8_bin NOT NULL,
                          `pass` binary(255) NOT NULL,
                          `soc` int(11) NOT NULL,
                          `user` int(11) NOT NULL,
                          `blog` int(11) NOT NULL,
                          `auto` tinyint(1) NOT NULL DEFAULT '1',
                          `post_on` tinyint(1) NOT NULL DEFAULT '0',
                          `pages` text COLLATE utf8_bin NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
    
        $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_logs`");
                        $wpdb->query("CREATE TABLE `" . $wpdb->base_prefix . "sp_logs` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `acc` int(11) NOT NULL,
                          `post` int(11) NOT NULL,
                          `user` int(11) NOT NULL,
                          `post_to` varchar(300) COLLATE utf8_bin NOT NULL,
                          `status` tinyint(1) NOT NULL,
                          `type` tinyint(4) NOT NULL,
                          `date` datetime NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
                        
        $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_mail_groups` ");
                        $wpdb->query("CREATE TABLE `" . $wpdb->base_prefix . "sp_mail_groups` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `service` int(11) NOT NULL,
                          `login` varchar(100) COLLATE utf8_bin NOT NULL,
                          `pass` binary(255) NOT NULL,
                          `name` varchar(200) COLLATE utf8_bin NOT NULL,
                          `emails` text COLLATE utf8_bin NOT NULL,
                          `user` int(11) NOT NULL,
                          `auto` tinyint(1) NOT NULL DEFAULT '1',
                          `blog` int(11) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
                        
        $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_mail_services` ");
                        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "sp_mail_services` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `name` varchar(200) COLLATE utf8_bin NOT NULL,
                          `smpt` varchar(200) COLLATE utf8_bin NOT NULL,
                          `port` smallint(5) NOT NULL,
                          `ssl` tinyint(1) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=19 ");
                        
        $wpdb->query("INSERT INTO `" . $wpdb->base_prefix . "sp_mail_services` (`id`, `name`, `smpt`, `port`, `ssl`) VALUES
                        (11, 'AIM Mail', 'smtp.aim.com', 587, 1),
                        (8, 'Gmail', 'smtp.gmail.com', 465, 1),
                        (9, 'Zoho Mail', 'smtp.zoho.com', 465, 1),
                        (10, 'iCloud Mail', 'smtp.mail.me.com', 587, 1),
                        (12, 'Hotmail', 'smtp.live.com', 587, 1),
                        (14, 'Yahoo!', 'plus.smtp.mail.yahoo.com', 465, 1),
                        (15, 'FastMail', 'mail.messagingengine.com', 465, 1),
                        (16, 'Shortmail', 'smtp.shortmail.com', 465, 1);");
                            
        if (!file_exists(dirname(__file__) . '/inc/key.php')) {
            $key = sp_rnd(20,15);
            $starter = sp_rnd(20,15);
    
            file_put_contents(dirname(__file__) . '/inc/key.php', '<?php $crypt_key="' . $key .   '"; $start_key="' . $starter .   '"; ?>');
        }
    } elseif(sp_get_opt('sp_verssion') < 2)
    {
        sp_upd_opt('sp_verssion', '2.0');
        
        sp_add_opt('sp_run_method', '2');
    }
    
    
    if(!file_exists(dirname(__file__) . '/inc/key.php'))
    {
        $wpdb->query("DELETE FROM `" . $wpdb->base_prefix . "sp_accs` WHERE 1 ");
        $wpdb->query("DELETE FROM `" . $wpdb->base_prefix . "sp_logs` WHERE 1 ");
        $key = sp_rnd(20,15);
        $starter = sp_rnd(20,15);
    
        file_put_contents(dirname(__file__) . '/inc/key.php', '<?php $crypt_key="' . $key .   '"; $start_key="' . $starter .   '"; ?>'); 
    }

    return true;
}


/**
 * sp_register_admin_menu()
 * 
 * Adding Social Poster menu to Admin Panel
 * 
 * @uses add_submenu_page - WP
 * 
 */
function sp_register_admin_menu()
{
    if(sp_user_rights())
        $page =  add_submenu_page('tools.php', 'Social Poster', 'Social Poster', 'publish_posts', 'Social-Poster', 'sp_main_page');
}

/**
 * soc_start_posting()
 * 
 * Start posting on social networks
 * 
 * @uses get_permalink - WP
 * @uses wp_get_attachment_url - WP
 * @uses get_post_thumbnail_id - WP
 * @uses is_super_admin - WP
 * @uses sp_get_opt - Social Poster(Declared in inc/functions.php)
 * @uses WPDB Class - WP
 * 
 * @global $blog_id - Current blog id
 * @global $current_user - Current user object
 * @global $wpdb - WPDB Class
 * 
 * @param int $post_id - Current post id
 * @param obj $post - Current post object 
 * 
 * @return int - Post id
 * 
 */

function soc_start_posting($post_id,$post)
{
    global $wpdb,$blog_id,$current_user, $SP_SOCIALS;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
        
    if (sp_user_rights() && $post->post_status == 'publish')
    {

        $time = time() - 1200;
        
        if(!is_super_admin())
        {
            $in_process = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_logs` 
                                            WHERE UNIX_TIMESTAMP(date) > '.$time.' AND status = "2"');
        }
        
        /** Plugin occupation level **/
        $cooks = scandir(dirname(__file__) . '/other/cooks/');
        $cooks = count($cooks) - 2;
        /****************************/
        
        $max_conn = sp_get_opt('sp_max_conn', '20'); //Plugin occupation limit
    
        if ($max_conn > $cooks || is_super_admin()) 
        {
            require_once (SP_PDIR.'/inc/soc.php');
                
            $free = $max_conn - $cooks;
    
            $max_post_acc = sp_get_opt('sp_max_post_acc', '20');
    
            if ($free < $max_post_acc)
                $max_post_acc = $free;
            
            $max_post_acc -= $in_process;
            
            if($max_post_acc <= 0 && !is_super_admin())
            {
                echo '<div class="error" align="center"><p>' . __('You have exceeded the limit of maximum number of accounts for posting.', 'sp_text_domain') .'</p></div>';
                return $post_id;
            }
            
            if(isset($_POST['post_title']))
            {
                if(!is_array($_POST['soc_accs']) &&  !is_array($_POST['sp_mail_grps']))
                    return $post_id;
                
                    $accs = $_POST['soc_accs'];
                    $mgrps = $_POST['sp_mail_grps'];

                if(!is_super_admin() && $max_post_acc < count($accs))
                {
                    if(is_array($accs))
                        array_splice($accs,$max_post_acc);
                    if(is_array($mgrps))
                        array_splice($mgrps,$max_post_acc);
                } else {
                    if(is_array($accs))
                        array_splice($accs,15);
                    if(is_array($mgrps))
                        array_splice($mgrps,15);
                }
             } else {
                if(!is_super_admin() && $max_post_acc < count($accs))
                    $limit = $max_post_acc;
                else
                    $limit = 15;
                
                $accs = $wpdb->get_results('SELECT `post_on`,`pages`,`id`,`soc`,`login`,`pass`
                                FROM `' . $wpdb->base_prefix . 'sp_accs`
                                    WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" 
                                            AND `auto` = 1 LINIT '.$limit);

                $mgrps = $wpdb->get_results('SELECT `t1`.`emails`,`t1`.`login`,`t1`.`pass`,`t2`.`smpt`,`t2`.`port`,`t2`.`ssl`,`t1`.`id`
                                FROM `' . $wpdb->base_prefix . 'sp_mail_groups` AS `t1`
                                INNER JOIN `' . $wpdb->base_prefix . 'sp_mail_services` AS `t2` ON `t1`.`service` = `t2`.`id`
                                    WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" 
                                            AND `t1`.`auto` = 1');     
             }   
        
            $url = urldecode(get_permalink($post_id));
            $title = $_POST['sp']['text'];
            $text = $_POST['sp']['title'];
            $text = strip_shortcodes($text);
            $text = str_replace('&nbsp;','',$text);
            $image = $_POST['sp']['image'];
            /*
            if(!($image = wp_get_attachment_url(get_post_thumbnail_id($post_id))))
            {
                preg_match ('|<img .*?src=[\'"](.*?)[\'"].*?/>|i', $text, $im);
                
                if(isset($im[1]))
                {
                    $image = $im[1];
                } else {
                    $arrImages = get_children( array( 
                                                    'post_parent' => $post_id, 
                                                    'post_type' => 'attachment', 
                                                    'post_mime_type' => 'image', 
                                                    'orderby' => 'menu_order', 
                                                    'order' => 'ASC', 
                                                    'numberposts' => 999 )
                    );
                    $arrKeys = array_keys($arrImages); 
                    $image = wp_get_attachment_url($arrKeys[0]);
                }          
            }
*/
            $guid = urlencode($post->guid);
            
            
            include_once (SP_PDIR.'/inc/poster.php');

            return $post_id;
    
        }
        
        $_GET['sp_mess'] = __('The plugin is busy now, try again later.', 'sp_text_domain');
    }
    
    return $post_id;
    
}

/**
 * sp_add_post_box_setup()
 * 
 * Adding meta box to edit post page with social accounts list.
 * 
 * @uses add_meta_box() - WP
 * 
 * @return false if post_type = links
 */
function sp_add_post_box_setup($id, $post)
{
    if (isset($post->link_id) || !sp_user_rights())
        return;

    add_meta_box('sp_social_poster', __('Social Poster: Social values', 'sp_text_domain'),'sp_add_post_box', null, 'advanced', 'high');
}

/**
 * sp_add_post_box()
 * 
 * Printing the social list box
 * 
 * @uses wp_nonce_field - WP
 * @uses plugin_basename - WP
 * @uses is_super_admin - WP

 * @uses WPDB Class - WP
 * 
 * @global $wpdb - WPDB Class
 * @global $blog_id - Current site id
 */
function sp_add_post_box($post)
{
    global $wpdb, $blog_id, $current_user;

    wp_nonce_field(plugin_basename(__file__), 'sp_nonce');   

    /** Plugin occupation level **/
    $cooks = scandir(dirname(__file__) . '/other/cooks/');
    $cooks = count($cooks) - 2;
    /****************************/
    

    $max_conn = sp_get_opt('sp_max_conn', '20'); //Plugin busy limit

    if ($max_conn > $cooks || is_super_admin()) 
    {
        require_once (SP_PDIR.'/inc/soc.php');

        if (is_super_admin()) 
        {
            if ($max_conn <= $cooks)
                echo "<strong>".__('The plugin is busy and not recommended for use now.', 'sp_text_domain')."</strong>";
                
        } else {
            
            $free = $max_conn - $cooks;
            
            $max_post_acc = sp_get_opt('sp_max_post_acc', '20');
            
            if ($free < $max_post_acc)
                    $max_post_acc = $free;

            if(!is_super_admin())
            {
                $time = time() - 1200;
                $in_process = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_logs` 
                                                    WHERE UNIX_TIMESTAMP(date) > '.$time.' AND status = "2"');
            }
                         
            if(($max_post_acc - $in_process) <= 0)
            {
                echo "<strong>".__('You exceeded the limit of maximum number of accounts for posting','sp_text_domain')."</strong>";
                return;
            }   
            
            echo str_replace('%d%',"<strong>".($max_post_acc - $in_process)."</strong>",__('You can post maximum on %d% social accounts now','sp_text_domain'));
            

        }

        $sql = 'SELECT `auto`,`id`,`login`,`soc` FROM `' . $wpdb->base_prefix . 'sp_accs` WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '"';
        $sql2 = 'SELECT `t1`.`auto`,`t1`.`id`,`t1`.`name`,`t2`.`name` AS `sname` FROM `' . $wpdb->base_prefix . 'sp_mail_groups` AS `t1`
                    INNER JOIN `' . $wpdb->base_prefix . 'sp_mail_services` AS `t2` ON `t1`.`service` = `t2`.`id` WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '"';

        $accs = $wpdb->get_results($sql);
        $emails = $wpdb->get_results($sql2);
        
        _social_value_form($post)
?>
	<div id="sp-cat" class="categorydiv">
		<ul id="cat-tabs" class="category-tabs">
			<li class="tabs"><a href="#sp-social" tabindex="3"><?php _e('Socials','sp_text_domain') ?></a></li>
			<li class="hide-if-no-js"><a href="#sp-email" tabindex="3"><?php _e('Email Groups','sp_text_domain') ?></a></li>
		</ul>
        
		<div id="sp-email" class="tabs-panel" style="display: none;">
                <?php foreach ($emails as $mail): ?>
                            <label class="selectit">
                                <input <?php echo ($mail->auto == 1) ? 'checked="checked"' : '' ?> value="<?= $mail->id ?>" type="checkbox" name="sp_mail_grps[]"/> 
                                <strong><?= esc_html($mail->sname) ?></strong>::<?= esc_html($mail->name) ?>
                                <a href="#" title="<?= esc_html($mail->sname) ?>::<?= esc_html($mail->name) ?>" data-acc="<?=$mail->id?>"  class="sp_edit_post sp_mail"><img height="12" width="12" src="<?=SP_PURL?>img/edit.png" /></a>
                            </label> <br />
                  <?php endforeach; ?>    
		</div> 
                      
		<div id="sp-social" class="tabs-panel">			
                  <?php foreach ($accs as $acc): ?>
                            <label class="selectit">
                                <input <?php echo ($acc->auto == 1) ? 'checked="checked"' : '' ?> value="<?= $acc->id ?>" type="checkbox" name="soc_accs[]"/> 
                                <strong><?= $SP_SOCIALS[$acc->soc]['name'] ?></strong>::<?= esc_html($acc->login) ?>
                                <a href="#" title="<?= $SP_SOCIALS[$acc->soc]['name'] ?>::<?= esc_html($acc->login) ?>" data-acc="<?=$acc->id?>" class="sp_edit_post sp_soc"><img height="12" width="12" src="<?=SP_PURL?>img/edit.png" /></a>
                            </label> <br />
                  <?php endforeach; ?>
		</div>          
            
</div>
<div style="clear: both;"></div>
<div><span class="sp-tip"><? _e('Tip', 'sp_text_domain'); ?>:</span> 
<? _e('Choose the social view of the Post', 'sp_text_domain'); ?>   
</div>
<div><span class="sp-tip"><? _e('Tip', 'sp_text_domain'); ?>:</span> 
<? _e('Click on the edit icon to get access to more advansed setting sepecified to each social', 'sp_text_domain'); ?>    
</div>      
<?php
    } else
        echo "<strong>".__('The plugin is busy now, try again later.', 'sp_text_domain')."</strong>";
}
 
add_action('save_post', 'functionx'); // called before the redirect
add_action('admin_head', 'sp_add_plugin_notice'); // called after the redirect

function sp_add_plugin_notice() 
{
    wp_enqueue_script('jquery');   
        
  if (sp_get_opt('sp_display_my_admin_message')) { // check whether to display the message
    add_action('all_admin_notices' ,'sp_show_notice' );
    sp_upd_opt('sp_display_my_admin_message', 0); // turn off the message
  }
}

// essentially the same as your original functionx, except the message is stored and activated for display
function functionx () {
	if (!empty($_GET['sp_mess'])) {
	  sp_upd_opt('sp_my_admin_message', $_GET['sp_mess']);
	} else {
	  sp_upd_opt('sp_my_admin_message', '');
	}
    
	sp_upd_opt('sp_display_my_admin_message', 1); // turn on the message
}

function sp_show_notice()
{  
    $mess = sp_get_opt('sp_my_admin_message');
    
    if(!empty($mess))
    {
        ?>
        
        <script type="text/javascript">
        jQuery(document).ready(function() {jQuery('<div id="notice" class="error"><p><?=$mess?></p></div>').insertBefore('#post')});
        </script>
        
        <?php   
    }
}

?>