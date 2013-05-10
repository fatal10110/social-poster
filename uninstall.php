<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */

global $wpdb;

require_once('inc/functions.php');

sp_del_opt('sp_max_acc');
sp_del_opt('sp_max_post_acc');
sp_del_opt('sp_min_role');
sp_del_opt('sp_max_conn');
sp_del_opt('sp_verssion');


$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_accs`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_logs`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_mail_groups`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "sp_mail_services`");

?>