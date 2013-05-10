<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */

add_action('wp_ajax_sp_field_list', 'sp_field_list');
add_action('wp_ajax_sp_acc_form', 'sp_acc_form');
add_action('wp_ajax_sp_acc', 'sp_acc');
add_action('wp_ajax_sp_acc_del', 'sp_acc_del');
add_action('wp_ajax_sp_get_form', 'sp_get_form');
add_action('wp_ajax_sp_grp_del','sp_grp_del');
add_action('wp_ajax_sp_mail_grp_form','sp_mail_grp_form');
add_action('wp_ajax_sp_mail_grp', 'sp_mail_grp');
add_action('wp_ajax_sp_mail_svc_form','sp_mail_svc_form');
add_action('wp_ajax_sp_mail_svc', 'sp_mail_svc');
add_action('wp_ajax_sp_svc_del', 'sp_svc_del');
add_action('wp_ajax_sp_log_del', 'sp_log_del');


function sp_grp_del()
{
    global $wpdb, $blog_id, $current_user;
    
    if (isset($_POST['id']) && (is_numeric($_POST['id']) || is_array($_POST['id']))) 
    {//Deleting group
        $q = 'DELETE  `t1`,`t2` FROM `' . $wpdb->base_prefix . 'sp_mail_groups` as `t1`
                LEFT JOIN `' . $wpdb->base_prefix . 'sp_logs` AS `t2` ON `t1`.`id` = `t2`.`acc` AND `t2`.`type` = 1 
                    WHERE';
    
        if (is_numeric($_POST['id']))//Single delete
        {
            $q .= '`t1`.id = "' . (int)$_POST['id'] . '" ';
        } else {//Multi delete
                        
            $ids = array();

            foreach ($_POST['id'] as $acc)
                $ids[] = '"' . (int)$acc . '"';

            $q .= ' `t1`.id IN(' . implode(',', $ids) . ')';
        }

        if (isset($q)) 
        {
            if (!is_super_admin())
                $q .= ' AND `t1`.`user` = "' . $current_user->ID . '"';

            $wpdb->query($q);
            
            if ($wpdb->rows_affected == 0)//Not deleted
                $error = __('Error occurred', 'sp_text_domain');

        } else
            $error = __('Error occurred', 'sp_text_domain');
            
    } else 
        $error = __('Error occurred', 'sp_text_domain');
        
    if(isset($error))
        die($error);
            
    die();
}

function sp_field_list()
{
    global $current_screen, $wp_list_table;

    $list_class = $_POST['list_args']['class'];
    check_ajax_referer("fetch-list-$list_class", '_ajax_fetch_list_nonce');

    //$current_screen = convert_to_screen($_GET['list_args']['screen']['id']);

    define('WP_NETWORK_ADMIN', $current_screen->is_network);
    define('WP_USER_ADMIN', $current_screen->is_user);

   $wp_list_table = sp_get_list_table($list_class);
   $wp_list_table->set_url($_POST['url']);
 
    if (!$wp_list_table)
        wp_die(0);

    if (!$wp_list_table->ajax_user_can())
        wp_die(-1);

    $wp_list_table->ajax_response();

    wp_die(0);
}

function sp_get_list_table($class)
{
    $core_classes = array( //Site Admin
        'SP_Acc_List_Table' => 'acc',
        'SP_Services_List_Table' => 'services',
        'SP_Groups_List_Table' => 'group',
        'SP_Soc_Logs_List_Table' => 'soc_logs',
        'SP_Mail_Logs_List_Table' => 'mail_logs',
        
         );

    if (isset($core_classes[$class])) {
        foreach ((array )$core_classes[$class] as $required)
            require_once (SP_PDIR.'/inc/pages/'.$required.'_list.class.php');
        return new $class;
    }

    return false;
}

function sp_mail_grp()
{
    global $wpdb, $current_user, $blog_id;

    require (SP_PDIR.'/inc/key.php');
    require (SP_PDIR.'/inc/crypt.php');
    
    if(isset($_POST['aid']) && is_numeric($_POST['aid']))
    {
        if (empty($_POST['sp_gname']))
        {
            $error = __('Enter the group name field please', 'sp_text_domain');
            
        } elseif (empty($_POST['sp_login']))
        {
            $error = __('Fill the account field please', 'sp_text_domain');
            
        } elseif ($_POST['sp_pass'] !== $_POST['sp_repass'] && !empty($_POST['sp_pass']))
        {
            $error = __('Passwords do not match', 'sp_text_domain');
            
        } elseif (empty($_POST['sp_emails']))
        {
            $error = __('Please add emails to group.', 'sp_text_domain');
        } else {

            $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_groups` 
                                    WHERE `name` = ' . $wpdb->prepare('%s', $_POST['sp_gname']) .
                                            ' AND `blog` = "' . $blog_id .
                                            '" AND `user` = "' . $current_user->ID . '" AND `id` != "' . (int)$_POST['aid'] . '"');
            
            $svc = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_services` 
                                    WHERE `id` = "' . (int)$_POST['sp_svc'] .'"');

            if ($ex == 0 && $svc == 1) 
            {
                $data = array(
                    'login' => $_POST['sp_login'], 
                    'name' => $_POST['sp_gname'],
                    'auto' => (bool)$_POST['sp_auto'],
                    'service' => (int)$_POST['sp_svc'],
                    'emails' => serialize(explode("\r\n",trim($_POST['sp_emails']))),
                );
                
                $data_format = array('%s', '%s','%d', '%d', '%s');
                
                if (!empty($_POST['sp_pass'])) {
                    $data['pass'] = sp_codex($_POST['sp_pass'], $crypt_key);
                    $data_format[] = '%s';
                }
    
                $where = array('id' => (int)$_POST['aid']);
                $where_format = array('%d');
    
                if (!is_super_admin()) {
                    $where['user'] = $current_user->ID;
                    $where_format[] = '%d';
                }
            
                $wpdb->update($wpdb->base_prefix . 'sp_mail_groups', $data, $where, $data_format, $where_format);

                die('OK:'.__('Group has been updated', 'sp_text_domain'));

            } elseif($ex > 0) 
                $error = __('Group with the same name already exists', 'sp_text_domain');
              elseif($svc == 0)
                $error = __('Mail service do not exists', 'sp_text_domain');
        }        
    } else {
        
        if (empty($_POST['sp_gname']))
        {
            $error = __('Enter the group name field please', 'sp_text_domain');
            
        } elseif (empty($_POST['sp_login']))
        {
            $error = __('Fill the account field please', 'sp_text_domain');
            
        } elseif (empty($_POST['sp_emails']))
        {
            $error = __('Please add emails to group.', 'sp_text_domain');
            
        } elseif (empty($_POST['sp_pass']))
        {
            $error = __("Please enter the account's password", 'sp_text_domain');
            
        } elseif ($_POST['sp_pass'] !== $_POST['sp_repass']) {
            
            $error = __('Passwords do not match', 'sp_text_domain');
            
        } elseif (!isset($_POST['sp_svc'])) {
            
            $error = __('Please choose a mail service.', 'sp_text_domain');
        } else 
        {          
            $serv = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_services`
                                        WHERE `id` = "'.(int)$_POST['sp_svc'].'"');   
            if($serv == 1)
            {
            
                //Checking for existing group
                $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_groups` 
                                        WHERE `name` = ' . $wpdb->prepare('%s',$_POST['sp_name']) . ' 
                                                 AND `blog` = "' . $blog_id .
                                                    '" AND `user` = "' . $current_user->ID . '"');            
                if ($ex == 0) 
                {
                    $wpdb->insert(
                                $wpdb->base_prefix . 'sp_mail_groups', 
                                array(
                                    'login' => $_POST['sp_login'],
                                    'pass' => sp_codex($_POST['sp_pass'], $crypt_key), 
                                    'service' => (int)$_POST['sp_svc'],
                                    'user' => $current_user->ID, 
                                    'blog' => $blog_id, 
                                    'auto' => (bool)$_POST['sp_auto'],
                                    'name' => $_POST['sp_gname'],
                                    'emails' => serialize(explode("\r\n",trim($_POST['sp_emails']))),
                                    ), 
                                
                                array('%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s')
                    );

                    die('OK:'.__('Group has been created', 'sp_text_domain'));
                } else
                $error = __('Group with the same name already exists', 'sp_text_domain');
            } else 
                $error = __('Mail service do not exists', 'sp_text_domain');
        } 
               
    }
    
    die('FAIL:'.$error);
}

function sp_mail_grp_form()
{
    global $wpdb, $current_user, $blog_id;
    
    require (SP_PDIR.'/inc/key.php');
    require (SP_PDIR.'/inc/crypt.php');
    
    if(isset($_POST['aid']) && is_numeric($_POST['aid']))
    {
        $sql = 'SELECT `login`,`name`,`auto`,`service`,`emails` FROM `' . $wpdb->base_prefix . 'sp_mail_groups` WHERE `id` = "' . (int)$_POST['aid'] . '" ';

        if (!is_super_admin())
            $sql .= ' AND `user` = "' . $current_user->ID . '"';

        $grp = $wpdb->get_row($sql,ARRAY_A);
    }
    
    $emails = '';
    
    if (is_array($grp) && !empty($grp['emails'])) 
    {
        $emails = implode("\r\n",unserialize($grp['emails']));
    } else {
        $grp = array(
            'login' => '',
            'name' => '',
            'auto' => 0,
            'service' => '',
            
        );
    }  
    
    $svc = $wpdb->get_results('SELECT `id`,`name` FROM `' . $wpdb->base_prefix . 'sp_mail_services` WHERE 1');
    
    ?>
    
        <div class="error" id="sp_error" style="display: none;"></div>
        <form method="POST" id="sp_form">
        <ul style="list-style: none;" id="sp_grp_form_ul">
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Service account', 'sp_text_domain'); ?>:</strong></div>
                <div><input style="font-weight: 400; font-size: 16px;" class="sp_style sp_style_input"  type="text" value="<?= esc_html($grp['login']) ?>" name="sp_login"/></div>
            </li>
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Password', 'sp_text_domain'); ?>:</strong></div>
                <div><input style="font-weight: bold; font-size: 18px; width: 150px;" class="sp_style sp_style_input" type="password" name="sp_pass"/></div>
            </li>
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Confirm Password', 'sp_text_domain'); ?>:</strong></div>
                    <div><input style="font-weight: bold; font-size: 18px; width: 150px;" class="sp_style sp_style_input" type="password" name="sp_repass"/></div>
            </li>
            <li style="margin-bottom: 10px;">
                    <div class="sp_cont_left"><strong><?php _e('Group name', 'sp_text_domain'); ?>:</strong></div>
                    <div><input style="font-weight: 400; font-size: 16px;" class="sp_style sp_style_input"  type="text" name="sp_gname" value="<?= esc_html($grp['name']) ?>"/></div>
            </li>
            <li style="margin-bottom: 5px; ">
                    <div  class="sp_cont_left"><strong><?php _e('Automatization', 'sp_text_domain'); ?>:</strong> <img  title='<?php _e('if set to "Enabled" all your posts will be posted to the account automatically' , 'sp_text_domain');?>' src="<?=SP_PURL.'img/help.png'?>"/> </div>

                    <div id="sp_auto">
                         <input type="radio" <?=($grp['auto'] == 1) ? 'checked="checked"' : ''?> value="1" id="sp_auto1" name="sp_auto" /><label for="sp_auto1">Enabled</label>
                         <input type="radio" <?=($grp['auto'] == 0) ? 'checked="checked"' : ''?> value="0" id="sp_auto2" name="sp_auto" /><label for="sp_auto2">Disabled</label>
                    </div>

            </li>
            <li style="margin-bottom: 5px; ">
                <div  class="sp_cont_left"><strong><?php _e('Service','sp_text_domain'); ?></strong></div>
                    <div><select name="sp_svc">
                        <?php
                            foreach($svc as $service)
                            {
                                if($service->id == $grp['service'])
                                    echo '<option selected="selected" value="' . (int)$service->id . '">' . esc_html($service->name) . '</option>'; 
                                else
                                    echo '<option value="' . (int)$service->id . '">' . esc_html($service->name) . '</option>';     
                            }
                        ?>
                </select></div>       
            </li>
            <li>
                <div  class="sp_cont_left"><strong><?php _e('Emails','sp_text_domain') ?></strong><br />
                        <span style="margin-left: 5px;" class="description">
                            <?php _e('Each email from new line','sp_text_domain') ?>
                        </span>
                </div>
                <textarea class="sp_style" rows="4" cols="100" style="width: 60%;" name="sp_emails"><?=esc_html($emails)?></textarea>
            </li>
        </ul>
        </form>
        <script>
        jQuery('#sp_auto').buttonset();
        </script>
        
    <?php
    
    die();
}

function sp_acc_form()
{
   global $wpdb, $current_user, $blog_id;
    
   require_once (SP_PDIR.'/inc/soc.php');
    $max_acc = sp_get_opt('sp_max_acc', '10');
    
    if ($total < $max_acc || is_super_admin() || isset($_POST['aid'])):
        require_once (SP_PDIR.'/inc/key.php');
        require_once (SP_PDIR.'/inc/crypt.php');
        
        if(isset($_POST['aid']) && is_numeric($_POST['aid']))
        {
            $sql = 'SELECT `login`,`soc`,`auto`,`post_on`,`pages` FROM `' . $wpdb->base_prefix . 'sp_accs` WHERE `id` = "' . (int)$_POST['aid'] . '" ';
    
            if (!is_super_admin())
                $sql .= ' AND `user` = "' . $current_user->ID . '"';
    
            $user = $wpdb->get_row($sql,ARRAY_A);
        
            if (!empty($user)) 
            {
                $user['pages'] = !empty($user['pages']) ? unserialize($user['pages']) : '';
                $user['pages'] = is_array($user['pages']) ? esc_html(implode("\r\n",$user['pages'])) : '';
            } 
         }
         
         if(!is_array($user)) 
         {
            $user = array(
                    'login' => '',
                    'soc' => 0,
                    'auto' => 0,
                    'post_on' => 0,
                    'pages' => '',
                );
         }
    ?>
        
        <div class="error" id="sp_error" style="display: none;"></div>
        <form  method="POST" id="sp_form">
        <input type="hidden" name="sp_social" value="" id="sp_soc_selected" />
            <ul style="list-style: none;" id="sp_acc_form_ul">
                <li style="margin-bottom: 10px;">
                    <div class="sp_cont_left"><strong><?php _e('Login', 'sp_text_domain'); ?>:</strong></div>
                    <div><input style="font-weight: 400; font-size: 16px;" class="sp_style sp_style_input" value="<?=esc_html($user['login'])?>" type="text" name="sp_login"/></div>
                </li>
                <li>
                    <div class="sp_cont_left"><strong><?php _e('Password', 'sp_text_domain'); ?>:</strong></div>
                    <div><input style="font-weight: bold; font-size: 18px; width: 150px;" class="sp_style sp_style_input"  type="password" name="sp_pass"/></div>
                </li>
                <li style="margin-bottom: 10px;">
                    <div class="sp_cont_left"><strong><?php _e('Confirm Password', 'sp_text_domain'); ?>:</strong></div>
                    <div><input style="font-weight: bold; font-size: 18px; width: 150px;" class="sp_style sp_style_input" type="password" name="sp_repass"/></div>
                </li>
               <li style="margin-bottom: 5px;">     
                    <div class="sp_cont_left"><strong><?php _e('Social', 'sp_text_domain'); ?>:</strong></div>
                    <div style=" float: left;">
                        <select id="sp_socials"  name="sp_social">
                        <?php
                            
                            $js_arr = array();
                            
                            foreach ($SP_SOCIALS as $key => $social)
                            {
                                 
                                if($social['content']['page'] == 1)
                                    $js_arr[] = '1';
                                elseif($social['prefix'] === 'pint')
                                    $js_arr[] = '2';
                                else
                                    $js_arr[]  = '0'; 
                                
                                if(file_exists(SP_PDIR.'/img/'.$social['prefix'].'.png'))
                                    $im = SP_PURL.'img/'.$social['prefix'].'.png';
                                else
                                    $im = '';
                                    
                                if($user['soc'] == $key)
                                    $selected = 'selected="selected"';
                                else
                                    $selected = '';
                                
                                echo '<option '.$selected.' data-description="Social Network" data-imagesrc="'.$im.'" class="'.$class.'" value="' . (int)$key . '">' . esc_html($social['name']) . '</option>';
                            }
                        ?>
                        </select>
                    </div><div style="clear: left"></div>
                </li>
                
                <li style="margin-bottom: 5px; ">
                    <div  class="sp_cont_left"><strong><?php _e('Automatization', 'sp_text_domain'); ?>:</strong> <img  title='<?php _e('if set to "Enabled" all your posts will be posted to the account automatically' , 'sp_text_domain');?>' src="<?=SP_PURL.'img/help.png'?>"/> </div>

                    <div id="sp_auto">
                         <input type="radio" <?=($user['auto'] == 1) ? 'checked="checked"' : ''?> value="1" id="sp_auto1" name="sp_auto" /><label for="sp_auto1">Enabled</label>
                         <input type="radio" <?=($user['auto'] != 1) ? 'checked="checked"' : ''?> value="0" id="sp_auto2" name="sp_auto" /><label for="sp_auto2">Disabled</label>
                    </div>

                </li>
                
                    <li class="sp_pages_box" id="sp_post_on" style="margin-bottom: 10px; display: <?=($SP_SOCIALS[0]['content']['page'] == 1 && $SP_SOCIALS[0]['prefix'] !== 'pint') ? 'block' : 'none';?>;"> 
                        <div class="sp_cont_left"><strong><?php _e('Post on'); ?>:</strong></div>
                        <div id="sp_post_on_butt">
                         <input type="radio" value="1" <?=($user['post_on'] == 1) ? 'checked="checked"' : ''?> id="sp_post_on_butt1" name="sp_post_on" /><label for="sp_post_on_butt1"><?php _e('Pages','sp_text_domain') ?></label>
                         <input type="radio" value="0" <?=($user['post_on'] != 1) ? 'checked="checked"' : ''?> id="sp_post_on_butt2" name="sp_post_on" /><label for="sp_post_on_butt2"><?php _e('Profile','sp_text_domain') ?></label>
                        </div>
                    </li>

                    <li class="sp_pages_box" id="sp_pages_field" style="display: <?=($SP_SOCIALS[$user['soc']]['content']['page'] == 1 || $SP_SOCIALS[$user['soc']]['prefix'] === 'pint') ? 'block' : 'none';?>;">
                        <div class="sp_cont_left"><strong><?php _e('Page urls / Board names') ?>:</strong><br />
                            <span style="margin-left: 5px;" class="description">
                                <?php _e('Each page from new line','sp_text_domain') ?>
                            </span>
                        </div>
                        <textarea class="sp_style" rows="4" cols="100" style="width: 60%;" name="sp_pages"><?=esc_html($user['pages'])?></textarea>
                    </li>
            </ul>        
        </form>
        <?php $js_arr = implode(',',$js_arr);?>
        <script language="javascript">
            var sp_soc_arr = [<?=$js_arr?>];
            
            jQuery('#sp_socials').ddslick({height: 170, onSelected: sp_soc_checked});
            jQuery('#sp_post_on_butt').buttonset();										
            jQuery('#sp_auto').buttonset();
            
        </script>
    <?php else: ?>
        <p><?php _e('You exceeded the limit of maximum number of accounts','sp_text_domain') ?></p>
    <?php
    endif; 
    die();
}


function sp_acc()
{
    global $wpdb, $blog_id, $current_user;

    require_once (SP_PDIR . '/inc/soc.php');
    require_once (SP_PDIR . '/inc/key.php');
    require_once (SP_PDIR . '/inc/crypt.php');

    $pages = trim($_POST['sp_pages']);
    
    if(isset($_POST['aid']) && is_numeric($_POST['aid']))
    {
        if (empty($_POST['sp_login']))
        {
            $error = __('Fill the login field please', 'sp_text_domain');
            
        } elseif ($_POST['sp_pass'] !== $_POST['sp_repass'] && !empty($_POST['sp_pass']))
        {
            $error = __('Passwords do not match', 'sp_text_domain');
            
        } elseif (!isset($SP_SOCIALS[$_POST['sp_social']]))
        {
            $error = __('Please choose a social network.', 'sp_text_domain');
            
        } elseif ($SP_SOCIALS[$_POST['sp_social']]['prefix'] == 'pint' && empty($pages)) 
        {
            $error = __('Please fill the board names', 'sp_text_domain');
        } else {

            $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_accs` 
                                    WHERE `login` = ' . $wpdb->prepare('%s', $_POST['sp_login']) .
                                            ' AND `soc` = "' . (int)$_POST['sp_social'] . '"  AND `blog` = "' . $blog_id .
                                            '" AND `user` = "' . $current_user->ID . '" AND `id` != "' . (int)$_POST['aid'] . '"');
            
            if ($ex == 0) 
            {
                $soc = $SP_SOCIALS[$_POST['sp_social']];
                
                $_POST['sp_pages'] = trim($_POST['sp_pages']);
                $data = array(
                    'login' => $_POST['sp_login'], 
                    'soc' => (int)$_POST['sp_social'],
                    'auto' => (bool)$_POST['sp_auto'],
                );
                
                $data_format = array('%s', '%d','%d');
                
                
                
                if($soc['content']['page'] == 1 && !empty($pages))
                {
                    $data['pages'] = serialize(explode("\r\n",$pages));
                    $data['post_on'] = (int)$_POST['sp_post_on'];

                } elseif($soc['prefix'] === 'pint')
                {
                    $data['pages'] = serialize(explode("\r\n",$pages));
                    $data['post_on'] = 0;                    
                } else {
                    $data['pages'] = '';
                    $data['post_on'] = 0;
                }
                
                $data_format[] = '%s';
                $data_format[] = '%d';
                
                if (!empty($_POST['sp_pass'])) {
                    $data['pass'] = sp_codex($_POST['sp_pass'], $crypt_key);
                    $data_format[] = '%s';
                }
    
                $where = array('id' => (int)$_POST['aid']);
                $where_format = array('%d');
    
                if (!is_super_admin()) {
                    $where['user'] = $current_user->ID;
                    $where_format[] = '%d';
                }
            
                $wpdb->update($wpdb->base_prefix . 'sp_accs', $data, $where, $data_format, $where_format);

            } else
                $error = __('Account already exists', 'sp_text_domain');
        }
        
        if(empty($error))
            die('OK:'.__('Account has been successfully modified', 'sp_text_domain'));
    } else {
        if (empty($_POST['sp_login']) || empty($_POST['sp_pass']) || empty($_POST['sp_repass']) ||
            ($SP_SOCIALS[$_POST['sp_social']]['prefix'] == 'pint' && empty($_POST['sp_pages']))) {
            $error = __('All fields are required', 'sp_text_domain');
    
        } elseif ($_POST['sp_pass'] !== $_POST['sp_repass']) {
    
            $error = __('Passwords do not match', 'sp_text_domain');
    
        } elseif (!isset($SP_SOCIALS[$_POST['sp_social']])) {
    
            $error = __('Please choose a social network.', 'sp_text_domain');
    
        } elseif ($SP_SOCIALS[$_POST['sp_social']['prefix']] === 'pint' && empty($pages)) {
    
            $error = __('Please fill the board names', 'sp_text_domain');
        } else {
            /** Checking for account number limit **/
            $max_acc = sp_get_opt('sp_max_acc', '10');
    
            $total = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix .
                'sp_accs` 
                                            WHERE `blog` = "' . $blog_id .
                '" AND `user` = "' . $current_user->ID . '"');
            /****/
    
            if ($total < $max_acc || is_super_admin()) { //Can add new account
    
                //Checking for existing account
                $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_accs` 
                                            WHERE `login` = ' . $wpdb->prepare('%s',
                    $_POST['sp_login']) . ' 
                                                    AND `soc` = "' . (int)$_POST['sp_social'] .
                    '" AND `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '"');
                if ($ex == 0) {
                    $_POST['sp_pages'] = trim($_POST['sp_pages']);
    
                    $data = array('login' => $_POST['sp_login'], 'pass' => sp_codex($_POST['sp_pass'],
                        $crypt_key), 'soc' => (int)$_POST['sp_social'], 'user' => $current_user->ID,
                        'blog' => (isset($_POST['sp_blog']) && $_POST['sp_blog'] == 1) ? '0' : $blog_id,
                        'auto' => (bool)$_POST['sp_auto'], );
    
                    $data_format = array('%s', '%s', '%d', '%d', '%d', '%d');
    
                    $soc = $SP_SOCIALS[$_POST['sp_social']];
    
                    if($soc['content']['page'] == 1 && !empty($pages))
                    {
                        $data['pages'] = serialize(explode("\r\n",$pages));
                        $data['post_on'] = (int)$_POST['sp_post_on'];
                    } elseif($soc['prefix'] === 'pint')
                    {
                        $data['pages'] = serialize(explode("\r\n",$pages));
                        $data['post_on'] = 0;                    
                    } else {
                        $data['pages'] = '';
                        $data['post_on'] = 0;
                    }
    
                    $data_format[] = '%s';
                    $data_format[] = '%d';
    
                    $wpdb->insert($wpdb->base_prefix . 'sp_accs', $data, $data_format);
                } else
                    $error = 'Account already exists';
            } else
                $error = __('You exceeded the limit of maximum number of accounts','sp_text_domain');
        }
        
        if(empty($error))
            die('OK:'.__('Account has been added', 'sp_text_domain'));
    }
    
    die('FAIL:'.$error);
}

function sp_acc_del()
{
    global $wpdb, $blog_id, $current_user;
    
    if (!is_array($_POST['id']) && is_numeric($_POST['id']))//Single delete
    {
        $q = 'DELETE `t1`,`t2`
                FROM `' . $wpdb->base_prefix . 'sp_accs` AS `t1`
                LEFT JOIN `' . $wpdb->base_prefix . 'sp_logs` AS `t2` ON `t1`.`id` = `t2`.`acc` AND `t2`.`type` = 0 
                WHERE `t1`.`id` = "' . (int)$_POST['id'] . '" ';
            
    } elseif (is_array($_POST['id']) && !empty($_POST['id'])) 
    {//Multi delete
      
        $q = 'DELETE `t1`,`t2`
                FROM `' . $wpdb->base_prefix . 'sp_accs` AS `t1`
                LEFT JOIN `' . $wpdb->base_prefix . 'sp_logs` AS `t2` ON `t1`.`id` = `t2`.`acc`
                WHERE ';
                        
        $ids = array();

        foreach ($_POST['id'] as $acc)
            $ids[] = '"' . (int)$acc . '"';

            $q .= ' `t1`.`id` IN(' . implode(',', $ids) . ')';
        }

    if (isset($q)) 
    {
        if (!is_super_admin())
            $q .= ' AND `t1`.`user` = "' . $current_user->ID . '"';

        $wpdb->query($q);
           
        if ($wpdb->rows_affected == 0)//Not deleted
            $error = __('Error occurred1', 'sp_text_domain');

    } else
        $error = __('Error occurred2', 'sp_text_domain');
    
    if(isset($error))
        die($error);
        
    die();
}

/**
 * sp_get_form()
 * 
 * Getting the Social Form by AJAX
 * 
 * @uses get_post - WP
 * @uses wp_get_attachment_url - WP
 * @uses get_post_thumbnail_id - WP
 * @uses get_children - WP
 * @uses plugins_url - WP
 * 
 */
function sp_get_form()
{
    global $wpdb,$blog_id,$current_user;
    
    $_POST = array_map('sp_strip', $_POST);
    
    $text = '';
    $image = '';
    $title = '';
    $page = '';
    $id = '';
    $desc = '';
    
    if(isset($_POST['id'],$_POST['pid'],$_POST['type']) && is_numeric($_POST['pid']))
    {
        
        $soc = array();
        
        if($_POST['type'] == 'sp_mail')
        {
            $name = 'sp_mail';
            $id = (int)$_POST['id'];
        } else {
            require_once (SP_PDIR.'/inc/soc.php');
            
            $name = 'sp_soc';
            $id = explode('-',$_POST['id']);
            
            $key = (int)$id[1];
            $acc = (int)str_ireplace('sp_','',$id[0]);
            
            $soc = (isset($SP_SOCIALS[$key])) ? $SP_SOCIALS[$key] : array();
        }
        
        $arr = $name.'['.$_POST['id'].']';
        
        
        if(!empty($soc) || $name === 'sp_mail')
        {          
            $the_post = get_post( $_POST['pid']);
            
            $text = $the_post->post_content;
            $title = $the_post->post_title;
            
            if(isset($_POST['json']) && is_array($_POST['json'])) 
            {
                foreach($_POST['json'] as $key)
                {
                    if($key['name'] == $arr.'[text]')
                        $text = $key['value'];
                    if($key['name'] == $arr.'[image]')
                        $image = $key['value'];
                    if($key['name'] == $arr.'[title]')
                        $title = $key['value'];
                    if($key['name'] == $arr.'[desc]')
                        $desc = $key['value'];
                        
                    if($key['name'] == $arr.'[page]' && $name === 'sp_soc')
                        $page = $key['value'];

                    if($key['name'] == $arr.'[sp_post_on]' && $name === 'sp_soc')
                        $post_on = $key['value'];
                }

            } else {
                $text = ($name !== 'sp_mail') ? strip_tags($text) : $text;
                
                if(isset($soc['content']['content']))
                    $text = str_replace('{TITLE}',$title,$soc['content']['content']);
                
                if($name === 'sp_soc')
                {
                    $data = $wpdb->get_row('SELECT `pages`,`post_on` FROM  `' . $wpdb->base_prefix . 'sp_accs` 
                                        WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" AND  `id` = "' . $acc . '"');
                    if(!empty($data->pages))
                    {
                        $pages = unserialize($data->pages);
                        $page = is_array($pages) ? esc_html(implode("\r\n",$pages)) : '';
                    } else 
                        $page = '';
                    
                    $post_on = $data->post_on;
                }
                
            }
            
            $text = strip_shortcodes($text);
            $text = str_replace('&nbsp;','',$text);
            
            if($soc['content']['image'] == 1)
            {
            
                $images  = array();
                
                if(($th = wp_get_attachment_url(get_post_thumbnail_id($_POST['pid']))))
                    $images['th'] = $th;
                
                preg_match_all ('|<img .*?src=[\'"](.*?)[\'"].*?/>|i', $the_post->post_content, $im);
    
                if(is_array($im[1]))
                {
                    foreach($im[1] as $url)
                    {
                        if(!in_array($url,$images))
                            $images[] = $url;
                    }
                }            
                      
                $arrImages = get_children( array( 'post_parent' => (int)$_POST['pid'], 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
                $arrKeys = array_keys($arrImages); 
                
                foreach($arrKeys as $att)
                {
                    $url = wp_get_attachment_url($att);
                    
                    if(!in_array($url,$images))
                        $images[] = $url;
                }
            }

            if((isset($_POST['json']) && empty($image)) || empty($images))
            {
                $none_style = 'block';
            } else
                $none_style = 'none';
            ?>
            <form id="frm" >
            <input type="hidden" name="pre" value="<?=esc_html($_POST['id'])?>"/>
            <div style="width: <?=($soc['content']['page'] == 1 || $soc['content']['image'] == 1) ? '715px' : '500px'; ?>">
            <div style="float: left; width: 200px;  margin-right: 10px;">
            <?php if($soc['content']['image'] == 1): ?>
                <ul id="sp_im" style="list-style-type: none; padding: 0px; margin: 0px;">
                    <li style="display: <?=$none_style;?>;" > 
                        <div id="sp_noim"><?php _e('No Image','sp_text_domain'); ?></div>
                    </li>
                <?php
                if(!empty($images)):
                        foreach($images as $key => $im):
                        
                            if(($none_style == 'none' && empty($image) && empty($th)) || (!empty($image) && $im == $image))
                            {
                                $style = 'block';
                                $th = $im;
                            } else 
                                $style = 'none';
                                
                            
                    ?>                        
                            <li class="sp_im" style="background: white url('<?=SP_PURL?>timthumb.php?src=<?=urlencode($im)?>&w=200&h=200') center no-repeat; width: 202px; height: 202px; display: <?=$style;?>;"> </li>
                <?php 
                        endforeach; 
                endif;
                ?>
                </ul>   
                <div style="text-align: center; height: 10px; width: 200px; margin: 10px 0px 20px 0px;">
                    <a href="#" class="sp_im_butt sp_im_prev" id="sp_im_prev">&#8249;</a>
                    <a href="#" class="sp_im_butt sp_im_next" id="sp_im_next">&#8250;</a>          
                </div>
            <?php endif; ?>
            <?php if($soc['content']['page'] == 1): ?>
      		    <div style="height: 50px;">
            			<div class="sp_cont_left"><strong><?php _e('Post On','sp_text_area'); ?>:</strong></div> 
                        <div id="sp_post_on_butt">
                         <input type="radio" value="1" id="sp_post_on_butt1" <?php echo ($post_on == '1') ? 'checked="checked"' : '' ?> name="sp_post_on" /><label for="sp_post_on_butt1"><?php _e('Pages','sp_text_domain') ?></label>
                         <input type="radio" value="0" id="sp_post_on_butt2" <?php echo ($post_on != '1') ? 'checked="checked"' : '' ?> name="sp_post_on" /><label for="sp_post_on_butt2"><?php _e('Profile','sp_text_domain') ?></label>
                        </div>
          		</div>
            <?php endif; ?> 
            </div>
                        
        	<div>
                <?php if($name === 'sp_mail' || $soc['content']['title'] == 1): ?>  
        		<input type="text" name="title" class="sp_style" value="<?=esc_html($title)?>" style="font-weight: 600; margin-bottom: 10px; width: 500px; height: 30px;" /><br />
                <?php endif; ?>
                <?php if($name === 'sp_mail' || $soc['content']['text'] == 1): ?>  
        		<textarea name="text"  class="sp_style" style="width: 500px; height: 160px"><?=esc_html($text)?></textarea>
                <?php endif; ?>
            </div>
            <?php if($soc['content']['page'] == 1 || $soc['name'] == 'Pinterest'): ?>
        	<div  style="margin-top: 10px;">
        		<div>
                <div class="sp_cont_left"><strong>
        			<?php 
                        if ($soc['name'] == 'Pinterest') _e('Board Names:','sp_text_domain');
                        else _e('Page URL:','sp_text_domain');
                    ?>
                </strong></div>
                    <br />
        			<textarea id="sp_pages" name="page" class="sp_style" style="width: 500px; height: 70px;"><?=esc_html($page)?></textarea>
                </div>
        	</div>
            <?php endif; ?>            
            <div style="clear: both;" />
            
            <?php if($name !== 'sp_mail' && $soc['content']['desc'] == 1): ?>
            <div class="sp_cont_left"><strong><?php _e('Link Description','sp_text_area'); ?>:</strong></div> 
            <textarea class="sp_style" name="desc" style="width: 100%; max-width: 710px; height: 50px"><?=esc_html($desc)?></textarea>
            <?php endif; ?>        
        </div>
        <input type="hidden" name="image" value="<?=esc_html($th)?>" />
        </form>
        <script>
          
            jQuery('#sp_post_on_butt').click(function(ev) {return true;});
				jQuery('#sp_post_on_butt').buttonset();				
        </script>
        <?php
        }
    }
    
    die();
}


function sp_mail_svc_form()
{
    global $wpdb;
    
    if(isset($_POST['aid']) && is_numeric($_POST['aid']))
    {
        $sql = 'SELECT `name`,`port`,`ssl`,`smpt` FROM `' . $wpdb->base_prefix . 'sp_mail_services` WHERE `id` = "' . (int)$_POST['aid'] . '" ';

        $svc = $wpdb->get_row($sql,ARRAY_A);
    }
    
    if (!is_array($svc)) 
    {
        $svc = array(
            'smpt' => '',
            'name' => '',
            'port' => '',
            'ssl' => 0,
        );
    }
?>
    <div class="error" id="sp_error" style="display: none;"></div>
    <form  method="POST" id="sp_form">
        <ul style="list-style: none;" id="sp_acc_form_ul">
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Service name', 'sp_text_domain'); ?>:</strong></div>
                <div>
                    <input style="font-weight: 400; font-size: 16px;" class="sp_style sp_style_input"  type="text" value="<?= esc_html($svc['name'])?>" name="sp_name"/>
                </div>
            </li>
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Smpt', 'sp_text_domain'); ?>:</strong></div>
                <div>
                    <input style="font-weight: 400; font-size: 16px;" class="sp_style sp_style_input"  type="text" value="<?= esc_html($svc['smpt'])?>" name="sp_smpt"/>
                </div>
            </li>
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('Port', 'sp_text_domain'); ?>:</strong></div>
                <div>
                    <input style="font-weight: 400; width: 50px; font-size: 16px;" class="sp_style sp_style_input"  type="text" value="<?= esc_html($svc['port'])?>" name="sp_port"/>
                </div>
            </li>
            <li style="margin-bottom: 10px;">
                <div class="sp_cont_left"><strong><?php _e('SSL','sp_text_domain'); ?>:</strong></div>
                <div id="sp_ssl">
                    <input <?=($svc['ssl'] != 0) ? 'checked="checked"' : ''?> value="1" style="margin-right: 5px;" type="radio" id="sp_ssl_butt1" name="sp_ssl" /><label for="sp_ssl_butt1"><?php _e('Yes','sp_text_domain') ?></label>
                    <input value="0" style="margin-right: 5px; margin-left: 5px;" type="radio" id="sp_ssl_butt2" name="sp_ssl" <?=($svc['ssl'] == 0) ? 'checked="checked"' : ''?> /><label for="sp_ssl_butt2"><?php _e('No','sp_text_domain') ?></label>
                </div>
            </li>
        </ul>
    </form>
    <script language="javascript">
        jQuery('#sp_ssl').buttonset();
    </script>
                
<?php
    die();
}


function sp_mail_svc()
{
    global $wpdb;
    
    if(isset($_POST['aid']) && is_numeric($_POST['aid']))
    {
        if (empty($_POST['sp_name']) || empty($_POST['sp_port']) || empty($_POST['sp_smpt']))
        {
            $error = __('All fields are required', 'sp_text_domain');
            
        } else 
        { 

            //Checking for existing service
            $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_services` 
                                        WHERE (`name` = ' . $wpdb->prepare('%s',$_POST['sp_name']) . ' 
                                                 OR `smpt` = "' . $wpdb->prepare('%s',$_POST['sp_smpt']) . '") 
                                                 AND `id` != "' . (int)$_POST['aid'] . '"');            
            if ($ex == 0) 
            {
                $data = array(
                        'name' => $_POST['sp_name'],
                        'port' => (int)$_POST['sp_port'], 
                        'ssl' => (int)$_POST['sp_ssl'],
                        'smpt' => $_POST['sp_smpt'], 
                );
                
                $data_format = array('%s', '%d','%d', '%s');
                
    
                $where = array('id' => (int)$_POST['aid']);
                $where_format = array('%d');
    
            
                $wpdb->update($wpdb->base_prefix . 'sp_mail_services', $data, $where, $data_format, $where_format);

                die('OK:'.__('Group has been updated', 'sp_text_domain'));

            } else 
                $error = __('Mail service already exists','sp_text_domain');

        }

    } else {
        
        if (empty($_POST['sp_name']) || empty($_POST['sp_port']) || empty($_POST['sp_smpt']))
        {
            $error = __('All fields are required', 'sp_text_domain');
            
        } else 
        { 
            //Checking for existing service
            $ex = $wpdb->get_var('SELECT COUNT(*) FROM `' . $wpdb->base_prefix . 'sp_mail_services` 
                                        WHERE `name` = ' . $wpdb->prepare('%s',$_POST['sp_name']) . ' 
                                                 OR `smpt` = ' . $wpdb->prepare('%s',$_POST['sp_smpt'])); 
                                                 
            if ($ex == 0) 
            {
                $wpdb->insert(
                    $wpdb->base_prefix . 'sp_mail_services', 
                                array(
                                    'name' => $_POST['sp_name'],
                                    'port' => (int)$_POST['sp_port'], 
                                    'ssl' => (int)$_POST['sp_ssl'],
                                    'smpt' => $_POST['sp_smpt'],
                                    ), 
                                
                                array('%s', '%d', '%d', '%s')
                    );

                die('OK:'.__('Service has been added', 'sp_text_domain'));
            } else
                $error = __('Mail service already exists','sp_text_domain');
        }        
    }
    
    die('FAIL:'.$error);
}

function sp_svc_del()
{
    global $wpdb, $blog_id, $current_user;

    if (isset($_POST['id']) && (is_numeric($_POST['id']) || is_array($_POST['id']))) 
    {//Deleting group
   
        $q = 'DELETE  `t1`,`t2`,`t3` FROM `' . $wpdb->base_prefix . 'sp_mail_services` as `t1`
                LEFT JOIN `' . $wpdb->base_prefix . 'sp_mail_groups` as `t2` ON `t1`.`id` = `t2`.`service`
                LEFT JOIN `' . $wpdb->base_prefix . 'sp_logs` AS `t3` ON `t2`.`id` = `t3`.`acc` AND `t3`.`type` = 1 
                WHERE ';
    
        if (is_numeric($_POST['id']))//Single delete
            $q .= '`t1`.`id` = "' . (int)$_POST['id'] . '" ';
            
        elseif (is_array($_POST['id']) && !empty($_POST['id'])) 
        {//Multi delete
                        
            $ids = array();

            foreach ($_POST['id'] as $acc)
                $ids[] = '"' . (int)$acc . '"';

            $q .= ' `t1`.`id` IN(' . implode(',', $ids) . ')';
        }
        
        if (isset($q)) 
        {
            $wpdb->query($q);
            
            if ($wpdb->rows_affected == 0)//Not deleted
                $error = __('Error occurred', 'sp_text_domain');

        } else
            $error = __('Error occurred', 'sp_text_domain');
    }
    
    die($error);
}

function sp_log_del()
{
    global $wpdb, $blog_id, $current_user;
    
    if (isset($_POST['id']) && is_super_admin() && (is_numeric($_POST['id']) || is_array($_POST['id']))) 
    { //Deleting log
        if (is_numeric($_POST['id']))
        {
            $q = 'DELETE FROM `' . $wpdb->base_prefix . 'sp_logs` WHERE `id` = "' . (int)$_POST['id'] . '"';
            
        } elseif (is_array($_POST['id']) && !empty($_POST['id'])) 
        {
            $q = 'DELETE FROM `' . $wpdb->base_prefix . 'sp_logs` WHERE';
            $ids = array();

            foreach ($_POST['id'] as $acc)
                $ids[] = '"' . (int)$acc . '"';

            $q .= ' `id` IN(' . implode(',', $ids) . ')';
        }

        if (isset($q)) 
        {
            $wpdb->query($q);

            if ($wpdb->rows_affected == 0)
                $error = __('Error occurred', 'sp_text_domain');

        } else
            $error = __('Error occurred', 'sp_text_domain');
    }    
    
    die($error);
}
?>