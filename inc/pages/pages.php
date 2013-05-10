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
     
     $qarg = array('del' => false, 'type' => false, 'update' => false, 'error' => false,'action' => false,'aid' => false,'sid' => false,'gid' => false);
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
    $cli = (file_exists(SP_PDIR.'/other/cli.txt')) ? file_get_contents(SP_PDIR.'/other/cli.txt') : '';
    ?>

    <p>
    <form action="" method="POST">
        <table style="width: 50%;">
            <tr>
                <td>
                    <strong><?php _e('Run method', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Using system command or htpp connection','sp_text_domain') ?>
                    </span>                    
                </td>
                <td>
                    <input <?php echo ($run_method == 1) ? 'checked="checked"' : '' ?>  value="1" style="margin-right: 5px;" type="radio" name="sp_run_method" />CLI / CGI
                    <input <?php echo ($run_method == 2) ? 'checked="checked"' : '' ?>  value="2" style="margin-right: 5px; margin-left: 5px;" type="radio" name="sp_run_method" />HTTP
                </td>                
            </tr>
            <tr id="sp_cli_path" style="display: <?=($run_method == 1) ? 'table-row' : 'none' ?>;">
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
            <tr id="sp_conn_method" style="display: <?=($run_method == 2) ? 'table-row' : 'none' ?>;">           
                <td>
                    <strong><?php _e('Connection method', 'sp_text_domain') ?>:</strong><br />
                    <span style="margin-left: 5px;" class="description">
                        <?php _e('Using localhos or external ip for connection','sp_text_domain') ?>
                    </span>                    
                </td>
                <td>
                    <input <?php echo ($conn_method == 0) ? 'checked="checked"' : '' ?>  value="0" style="margin-right: 5px;" type="radio" name="sp_conn_method" />Local 
                    <input <?php echo ($conn_method == 1) ? 'checked="checked"' : '' ?>  value="1" style="margin-right: 5px; margin-left: 5px;" type="radio" name="sp_conn_method" />External
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
                    <select name="sp_max_acc" style="width: 120px;">
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
                    <select name="sp_max_post_acc" style="width: 120px;">
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
                    <select name="sp_min_role" style="width: 120px;">
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
                    <select name="sp_max_conn" style="width: 120px;">
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
    <script>
        jQuery(document).ready(function(){
           jQuery('input[name="sp_run_method"]').change(function(){
                if(jQuery(this).val() == '1')
                {
                    jQuery('#sp_cli_path').show();
                    jQuery('#sp_conn_method').hide();
                } else {
                    jQuery('#sp_cli_path').hide();
                    jQuery('#sp_conn_method').show();                    
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
?>