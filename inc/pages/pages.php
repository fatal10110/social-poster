<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */


/**
 * sp_main_page()
 * 
 * Main plugin page
 * 
 * @uses is_super_admin - WP
 * @uses screen_icon - WP
 * @uses esc_url - WP
 * @uses esc_html - WP
 */
function sp_main_page()
{ 
    $tabs = array();
     $tabs[] = array('qw' => 'accounts', 'name' => __('Accounts','wnm_text_domain'));
     $tabs[] = array('qw' =>'m_grp', 'name' => __('Mail groups','wnm_text_domain'));
     $tabs[] = array('qw' =>'m_srvc', 'name' => __('Mail services','wnm_text_domain'));
     $tabs[] = array('qw' =>'logs', 'name' => __('Logs','wnm_text_domain'));
     
     if(is_super_admin())
        $tabs[] = array('qw' =>'settings', 'name' => __('Settings','wnm_text_domain'));
     
     $qarg = array('del' => false, 'type' => false, 'update' => false, 'error' => false,'action' => false,'aid' => false,'sid' => false,'gid' => false, 'paged' => false);
?>
    <div id="wpbody">

<div id="wpbody-content">
    <br class="clear" />
    <div class="wrap">
    <?php screen_icon('post'); ?>
    <h2><?php _e('Social Poster','sp_text_domain') ?></h2>
    <h3 class="nav-tab-wrapper">

    
    <?php
     
    foreach ($tabs as $tab_id => $tab) {
        $qarg['tab'] = $tab['qw'];
        $class = ($_GET['tab'] == $tab['qw'] || (!isset($_GET['tab']) && $tab['qw'] == 'accounts')) ? ' nav-tab-active' : '';
        
        echo '<a href="' . esc_url(add_query_arg($qarg)) . '" class="nav-tab' . esc_html($class) . '">' . esc_html($tab['name']) . '</a>';
    }
?>
</h3>
<?php
    switch ($_GET['tab']) {
        case 'm_srvc':
                sp_svc_page();
            break;
        case 'm_grp':
                sp_glist_page();
            break;
        case 'logs':
                sp_logs_page();
            break;
        case 'settings':
            if(is_super_admin())
                sp_settings_page();
            break;
        default:
                sp_acc_list_page();
            break;
    }

    echo '</div>';
?>

        <div class="clear"></div></div><!-- wpbody-content -->
        <div class="clear"></div></div><!-- wpbody -->
<?php
}

/**
 * sp_logs_page()
 * 
 * Displaying the logs of posting system
 * 
 */
function sp_logs_page()
{
    $status = array('<div style="color: red;">Fail</div>','<div style="color: green;">Posted</div>','<div style="color: blue;">Processing</div>');

    $qarg = array('del' => false, 'update' => false, 'error' => false);

     $del_f = $qarg + array('fby' => false, 'fvar' => false);
    ?>
                          
                      <button id="sp_del_butt" class="sp_logs"><?php _e('Delete Logs','sp_text_domain') ?></button>
                        <a href="<?=add_query_arg($del_f);?>"><button id="sp_del_fil_butt" ><?php _e('Cancel All Filters','sp_text_domain'); ?></button></a>
          	          
              <script language="javascript">
              jQuery(document).ready(function() {
                  jQuery(function() {
                         jQuery( "#sp_del_fil_butt" ).button({
                            icons: {
                                primary: "ui-icon-cancel"
                            }
                        });                       
                    });
                        jQuery( "#sp_del_butt" ).button({
                            icons: {
                                primary: "ui-icon-trash"
                            }
                        });                       
                });
              </script> 
    <?
    $qarg1 = array('del' => false, 'update' => false, 'error' => false,'action' => false,'aid' => false,'sid' => false,'gid' => false, 'type' => 0);
    $qarg2 = array('del' => false, 'update' => false, 'error' => false,'action' => false,'aid' => false,'sid' => false,'gid' => false, 'type' => 1);
    $class1 = ($_GET['type'] == 0 || !isset($_GET['type'])) ? ' nav-tab-active' : '';
    $class2 = ($_GET['type'] == 1) ? ' nav-tab-active' : '';
    
    echo '<h3 class="nav-tab-wrapper" style="text-align: center;">';
    echo '<a href="' . esc_url(add_query_arg($qarg1)) . '" class="nav-tab' . $class1 . '">' . __('Social Logs','sp_text_domain'). '</a>';
    echo '<a href="' . esc_url(add_query_arg($qarg2)) . '" class="nav-tab' . $class2 . '">' .  __('Email Logs','sp_text_domain') . '</a>';
    echo "</h3>";    
    
    if($_GET['type'] == 1)
    {
        require_once(SP_PDIR.'/inc/pages/mail_logs_list.class.php');
        $myListTable = new SP_Mail_Logs_List_Table();
    } else {
        require_once(SP_PDIR.'/inc/pages/soc_logs_list.class.php');
        $myListTable = new SP_Soc_Logs_List_Table();
    }
    
    $myListTable->prepare_items(); 
    $myListTable->display(); 


}

/**
 * sp_acc_list_page()
 * 
 * Listing and adding accounts page
 * 
 */
function sp_acc_list_page()
{
    require_once(SP_PDIR.'/inc/pages/acc_list.class.php');
    
    $myListTable = new SP_Acc_List_Table();
    $myListTable->prepare_items(); 
    echo '<div id="sp_table">';
    $myListTable->display();          
}


/**
 * sp_settings_page()
 * 
 * Displaying the settings page
 * 
 * @uses is_super_admin - WP
 * @uses screen_icon - WP
 * @uses sp_get_opt - Social Poster(Declared in inc/functions.php)
 * @uses sp_upd_opt - WP
 * @uses esc_html - WP
 * 
 * @global $wp_roles - WP Roles Object
 */
function sp_settings_page()
{
    if (!is_super_admin())
        return;

    $sp_roles = array(
        'edit_dashboard' => __('Administrator','sp_text_domain'),
        'edit_others_posts' => __('Editor','sp_text_domain'),
        'publish_posts' => __('Author','sp_text_domain'),
    );
    
    if(is_multisite())
        $sp_roles = array('manage_network' => 'Network Administrator' ) + $sp_roles;

    if (isset($_POST['sp_save'])) 
    {
        if (is_numeric($_POST['sp_max_acc']) && $_POST['sp_max_acc'] <= 20 && $_POST['sp_max_acc'] > 0)
            sp_upd_opt('sp_max_acc', $_POST['sp_max_acc']);

        if (is_numeric($_POST['sp_max_post_acc']) && $_POST['sp_max_post_acc'] <= 10 && $_POST['sp_max_post_acc'] > 0)
            sp_upd_opt('sp_max_post_acc', $_POST['sp_max_post_acc']);

        if (is_numeric($_POST['sp_max_conn']) && $_POST['sp_max_conn'] <= 20 && $_POST['sp_max_conn'] > 0)
            sp_upd_opt('sp_max_conn', $_POST['sp_max_conn']);
        
        if (is_numeric($_POST['sp_conn_method']) && isset($_POST['sp_conn_method']))
            sp_upd_opt('sp_conn_method', $_POST['sp_conn_method']);

        if (isset($sp_roles[$_POST['sp_min_role']]))
            sp_upd_opt('sp_min_role', $_POST['sp_min_role']);
        
        if(isset($_POST['sp_run_method']) && is_numeric($_POST['sp_run_method']))
            sp_upd_opt('sp_run_method',$_POST['sp_run_method']);
            
        if(sp_get_opt('sp_run_method') == 1)
        {
            $path = sp_strip($_POST['sp_php_cli']);
            $path = trim($path);
            
            if(empty($path))
                $error = __('Path can not be empty whe CLI / CGI method selected','sp_text_domain');
            if(!sp_is_filepath($path))
                $error = __('Wrong PHP CLI path','sp_text_domain');
            elseif(!file_exists($path))
                $error = __('File does not exist','sp_text_domain');
            else {
                file_put_contents(SP_PDIR.'/other/cli.txt',$path);
            }
        } else {
            if (is_numeric($_POST['sp_background_mode']) && isset($_POST['sp_background_mode'])) 
                sp_upd_opt('sp_background_mode',$_POST['sp_background_mode']);
        }
        
        if(!isset($error))  
            echo '<div class="updated" align="center"><p>' . __('Settings updated','sp_text_domain') . '</p></div>';
        else
            echo '<div class="error" align="center"><p>' . $error . '</p></div>';
    }
    

    $max_acc = sp_get_opt('sp_max_acc', '10');
    $max_post_acc = sp_get_opt('sp_max_post_acc', '5');
    $min_role = sp_get_opt('sp_min_role');
    $max_conn = sp_get_opt('sp_max_conn');
    $conn_method = sp_get_opt('sp_conn_method',1);
    $run_method = sp_get_opt('sp_run_method',2);
    $background = sp_get_opt('sp_background_mode',1);
    $cli = (file_exists(SP_PDIR.'/other/cli.txt')) ? file_get_contents(SP_PDIR.'/other/cli.txt') : '';
    ?>

    <p>
    <form action="" method="POST">
        <table class="sp-settings-table">
            <tr>
                <td>
                    <strong><?php _e('Run method', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Using system command or htpp connection','sp_text_domain') ?>
                    </span>                    
                </td>
                <td>
                    <div id="sp_run_method">
                        <input <?php echo ($run_method == 1) ? 'checked="checked"' : '' ?>  value="1" id="sp_run_method1" type="radio" name="sp_run_method" /><label for="sp_run_method1">CLI</label> 
                        <input <?php echo ($run_method == 2) ? 'checked="checked"' : '' ?>  value="2" id="sp_run_method2" type="radio" name="sp_run_method" /><label for="sp_run_method2">HTTP</label>
                    </div>
                </td>                
            </tr>
            <tr class="sp_run_method1" style="display: <?=($run_method == 1) ? 'table-row' : 'none' ?>;">
                <td>
                    <strong><?php _e('Path to PHP CLI', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Patch to PHP CLI/CGI file','sp_text_domain') ?>
                    </span>                    
                </td>
                <td>
                    <input type="text" name="sp_php_cli" value="<?=$cli?>"/>
                </td>                
            </tr>            
            <tr class="sp_run_method2" style="display: <?=($run_method == 2) ? 'table-row' : 'none' ?>;">           
                <td>
                    <strong><?php _e('Connection method', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Using localhos or external ip for connection','sp_text_domain') ?>
                    </span>                    
                </td>
                <td>
                    <div id="sp_conn_method">
                        <input <?php echo ($conn_method == 0) ? 'checked="checked"' : '' ?> id="sp_conn_method1"  value="0" type="radio" name="sp_conn_method" /><label for="sp_conn_method1">Local</label> 
                        <input <?php echo ($conn_method == 1) ? 'checked="checked"' : '' ?> id="sp_conn_method2" value="1" type="radio" name="sp_conn_method" /><label for="sp_conn_method2">External</label>
                    </div>
                </td>
            </tr>
            <tr class="sp_run_method2" style="display: <?=($run_method == 2) ? 'table-row' : 'none' ?>;">
                <td>
                    <strong><?php _e('Run in Background', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Use ignore_user_abort() to run the poster in background','sp_text_domain') ?>
                    </span>
                </td>
                <td>
                    <div id="sp_background_mode">
                        <input <?php echo ($background == 0) ? 'checked="checked"' : '' ?>  value="0" id="sp_background_mode1" name="sp_background_mode" type="radio"/><label for="sp_background_mode1">Disabled</label> 
                        <input <?php echo ($background == 1) ? 'checked="checked"' : '' ?>  value="1" id="sp_background_mode2" name="sp_background_mode" type="radio"/><label for="sp_background_mode2">Enabled</label>              
                    </div>
                </td>
            </tr>      
            <tr>
                <td>
                    <strong><?php _e('Maximum accounts', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Maximum number of accounts for user','sp_text_domain') ?>
                    </span>
                </td>
                <td>
                    <select name="sp_max_acc" >
                    <?php
                        for ($i = 1; $i <= 20; $i++):
                            if ($i == $max_acc):
                    ?>
                        <option selected="selected" value="<?= $i ?>"><?= $i ?></option>
                        <?php else: ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php
                            endif;
                        endfor;
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php _e('Maximum accounts for posting', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('The maximum number of selected accounts for posting','sp_text_domain') ?>
                    </span>
                </td>
                <td>
                    <select name="sp_max_post_acc" >
                    <?php
                        for ($i = 1; $i <= 10; $i++):
                            if ($i == $max_post_acc):
                    ?>
                        <option selected="selected" value="<?= $i ?>"><?= $i ?></option>
                    <?php else: ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php
                            endif;
                        endfor;
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php _e('Minimal role', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Minimal role for using the social poster plugin','sp_text_domain') ?>
                    </span>
                </td>
                <td>
                    <select name="sp_min_role">
                    <?php
                        foreach ($sp_roles as $key => $name):   
                            if ($min_role == $key):
                    ?>
                                <option selected="selected" value="<?=$key ?>"><?=$name?></option>
                            <?php else: ?>
                                <option value="<?=$key?>"><?=$name?></option>
                    <?php
                            endif;
                        endforeach;
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?php _e('Maximum connections', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Disable the posting feature when too much posts are in process','sp_text_domain') ?>
                    </span>
                </td>
                <td>
                    <select name="sp_max_conn">
                    <?php
                        for ($i = 1; $i <= 20; $i++):
                            if ($i == $max_conn):
                    ?>
                        <option selected="selected" value="<?= $i ?>"><?= $i ?></option>
                    <?php else: ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php
                            endif;
                        endfor;
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><p>
                    <input type="submit" name="sp_save" class="button-primary" value="<?php _e('Save','sp_text_domain'); ?>" />
                </p></td>
            </tr>
        </table>
    </form>
    </p>
    
    <span class="sp-tip"><? _e('Tip', 'sp_text_domain'); ?>: </span>
    <div id="sp_demo_radio">
        <input type="radio" id="sp_demo_radio1" checked="checked" /><label for="sp_demo_radio1"><? _e('Checked', 'sp_text_domain'); ?></label>
        <input type="radio" id="sp_demo_radio2"/><label for="sp_demo_radio2"><? _e('Not Checked', 'sp_text_domain'); ?></label>
    </div>
    <script>
        jQuery(document).ready(function($) {
           $('#sp_background_mode, #sp_conn_method, #sp_run_method').buttonset();
           $('#sp_demo_radio').buttonset({disabled: true});
           
           $('input[name="sp_run_method"]').change(function(){
                if($(this).val() == '1')
                {
                    $('.sp_run_method2').fadeOut("fast", function() {
                        $('.sp_run_method1').fadeIn("fast");
                    });
                    
                    
                } else {
                    $('.sp_run_method1').fadeOut("fast", function() {
                        $('.sp_run_method2').fadeIn("fast");
                    });                  
                }
                    
           }); 
        });
    </script>
<?php
}

/**
 * sp_glist_page()
 * 
 * Listing mail groups page
 * 
 */
function sp_glist_page()
{    
    require_once('group_list.class.php');
    
    $myListTable = new SP_Groups_List_Table();
    $myListTable->prepare_items(); 
    $myListTable->display(); 
}

/**
 * sp_glist_page()
 * 
 * Listing mail services page
 * 
 */
function sp_svc_page()
{    
    require_once('services_list.class.php');
    
    $myListTable = new SP_Services_List_Table();
    $myListTable->prepare_items(); 
    $myListTable->display(); 
}

function _social_value_form($post, $soc = array(), $name = '', $params = array() ,$image = false,$ajax = false)
{
    if(empty($params)){
        $params = array(
            'title' => $post->post_title,
            'text' => $post->post_content
        );
    }
    
    extract($params);
    
    $text = strip_shortcodes($text);
    $text = str_replace('&nbsp;','',$text);
    $text = strip_tags($text);
    
    $images  = array();
                
    if(($th = wp_get_attachment_url(get_post_thumbnail_id($post->ID))))
    {
        if($image === false) $image = $th;
        $images[] = $th;
    }
                
    preg_match_all ('|<img .*?src=[\'"](.*?)[\'"].*?/>|i', $post->post_content, $im);
    
    if(is_array($im[1]))
    {
        
        foreach($im[1] as $url)
        {
            if(!in_array($url,$images))
            {
                if($image === false) $image = $url;
                $images[] = $url;
            }
        }
    }            
                      
    $arrImages = get_children( array( 
                                    'post_parent' => (int)$post->ID, 
                                    'post_type' => 'attachment', 
                                    'post_mime_type' => 'image', 
                                    'orderby' => 'menu_order', 
                                    'order' => 'ASC', 
                                    'numberposts' => 999 ) 
    );
                
    $arrKeys = array_keys($arrImages); 
                
    foreach($arrKeys as $att)
    {
        $url = wp_get_attachment_url($att);
                    
        if(!in_array($url,$images))
        {
            if($image === false) $image = $url;
            $images[] = $url;
        }
    }    
    
    
    
    $wrapper_class = '';
    
    if($ajax) $wrapper_class = ($soc['content']['page'] == 1 || $soc['content']['image'] == 1) ? 'sp-big' : 'sp-small';
    
    if($image === '' || empty($images)) $none_style = 'block';
    else $none_style = 'none';
            
    ob_start();
    ?>
    <ul class="sp_im">
        <li style="display: <?=$none_style;?>;" > 
            <div class="sp_noim"><?php _e('No Image','sp_text_domain'); ?></div>
        </li>  
        <?php if(!empty($images)) : ?>
            <?php foreach($images as $key => $im) : ?>
                <?php             
                    if($none_style == 'none' && ((!isset($th) && !$image) || (!empty($image) && $image == $im)))
                    {
                        $style = 'block';
                        $th = $im;
                    } else $style = 'none';
                ?>                        
                <li class="sp_im" style="background-image: url('<?=$im?>'); display: <?=$style;?>;"> </li>                
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
        <div class="sp_im_nav">
            <a href="#" class="sp_im_butt sp_im_prev">&#8249;</a>
            <a href="#" class="sp_im_butt sp_im_next">&#8250;</a>          
        </div>
    <?php
    $ims = ob_get_clean();    
    ?>
    <div class="sp-post-popup-wrapper <?=$wrapper_class?>">
        <div class="sp-im-wrapper">
            <?=($soc['content']['image'] == 1 || !$ajax) ? $ims : '' ?>
        </div>
        <div class="sp-text-wrapp">
        <?php if($name === 'sp_mail' || $soc['content']['title'] == 1 || !$ajax) : ?>  
            <div><input name="sp[title]" type="text" class="sp_style sp-post-title" value="<?=esc_html($title)?>" /></div>
        <?php endif; ?>
        <?php if($name === 'sp_mail' || $soc['content']['text'] == 1 || !$ajax) : ?>  
            <div><textarea name="sp[text]"  class="sp_style sp-post-text"><?=esc_html($text)?></textarea></div>
        <?php endif; ?>
        </div>
        <?php if($soc['name'] != 'Pinterest') : ?>
        <div style="clear: both;"></div>  
        <?php endif; ?>
        <?php if($ajax && ($soc['content']['page'] == 1 || $soc['name'] == 'Pinterest')): ?>
        
        	<div  style="margin-top: 10px;">
                <?php if($soc['name'] != 'Pinterest') : ?>
                <div style="float: left; width: 200px;">
         			<div  style="float: left;"><strong><?php _e('Post On','sp_text_area'); ?>:</strong></div> 
                    <div id="sp_post_on_butt">
                        <input type="radio" value="1" id="sp_post_on_butt1" <?php echo ($post_on == '1') ? 'checked="checked"' : '' ?> name="sp[post_on]" /><label for="sp_post_on_butt1"><?php _e('Pages','sp_text_domain') ?></label>
                        <input type="radio" value="0" id="sp_post_on_butt2" <?php echo ($post_on != '1') ? 'checked="checked"' : '' ?> name="sp[post_on]" /><label for="sp_post_on_butt2"><?php _e('Profile','sp_text_domain') ?></label>
                    </div>
                </div>
                <?php endif; ?>
        		<div style="margin-left: 10px; float: left;">
                    <div  style="float: left;">
                        <strong>
                			<?php 
                                if ($soc['name'] == 'Pinterest') _e('Board Names:','sp_text_domain');
                                elseif($ajax) _e('Page URL:','sp_text_domain');
                            ?>
                        </strong>
                    </div>
           			<textarea class="<?=($soc['name'] == 'Pinterest') ? 'sp_boards' : 'sp_pages'?> sp-post-pages" name="sp[page]" class="sp_style" ><?=esc_html($pages)?></textarea>
                </div>
                <div style="clear: both;"></div>
        	</div>
         <?php endif; ?>                               
         <?php if($name !== 'sp_mail' && $soc['content']['desc'] == 1): ?>
         <div style="float: left;"><strong><?php _e('Link Description','sp_text_area'); ?>:</strong></div> 
         <textarea class="sp_style sp-post-desc" name="sp[desc]"><?=esc_html($desc)?></textarea>
         <?php endif; ?>  
         <input type="hidden" name="sp[image]" value="<?=esc_html($th)?>" />      
    </div>
    
    <?php
}
?>