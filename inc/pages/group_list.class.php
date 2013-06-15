<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

if( ! class_exists( 'WP_List_Table_SP_Ex' ) ) {
    require_once( SP_PDIR.'/inc/pages/WP_List_Table_SP_Ex.class.php' );
}

class SP_Groups_List_Table extends WP_List_Table_SP_Ex 
{
     var $qarg = array('del' => false, 'update' => false, 'error' => false);
 
    var $cols = array(
                'gid' => '`t1`.`id`',
                'gname' => '`t1`.`name`',
                'serv' => '`t1`.`sevice`',
                'acc' => '`t1`.`login`',
                'msname' => '`t2`.`name`',
                'user' => '`t1`.`user`',
                'blog' => '`t1`.`blog`'
            );

    function __construct()
    {
        parent::__construct( array(
                    'singular'  => __('Group','sp_text_domain'),     //singular name of the listed records
                    'plural'    => __('Groups','sp_text_domain'),    //plural name of the listed records
                    'ajax'      => true        //does this table support ajax?
                ) );
    }
    
    function get_columns(){
          $columns = array(
            'cb' => '<input type="checkbox" />',
            'gid' => __('ID', 'sp_text_domain'), 
            'gname' => __('Name','sp_text_domain'), 
            'msname' => __('Service', 'sp_text_domain'), 
            'acc' => __('Account', 'sp_text_domain'), 
        );
        
        

        if (is_super_admin()) {
            if (is_multisite())
                $columns['blog'] = __('In Blog', 'sp_text_domain');
                
            $columns['user'] = __('Added By', 'sp_text_domain');
        }
        
        return $columns;
    }
    
    function prepare_items() {
        global $wpdb, $blog_id, $current_user;
        
        $columns = $this->get_columns();
      
        $hidden = array();
      
        $sortable = $this->get_sortable_columns();
      
        $this->_column_headers = array($columns, $hidden, $sortable);
      
        $sql = ' FROM `' . $wpdb->base_prefix . 'sp_mail_groups`  AS `t1`
                INNER JOIN `' . $wpdb->base_prefix . 'sp_mail_services` AS `t2` ON `t1`.`service` = `t2`.`id` ';
 
        if (!is_super_admin())
            $sql .= ' WHERE `t1`.`blog` = "' . $blog_id . '" AND `t1`.`user` = "' . $current_user->ID . '" ';

        
        if (!empty($_GET['orderby']) && array_key_exists($_GET['orderby'], $this->cols)) 
        {    
            if (isset($_GET['order']) && $_GET['order'] == 'asc')
                $order = ' ORDER BY ' . $this->cols[$_GET['orderby']] . ' ASC ';
            else
                $order = ' ORDER BY ' . $this->cols[$_GET['orderby']] . ' DESC ';
        }
        
        if (isset($_GET['fby'], $_GET['fvar'])) 
        {   
            if (array_key_exists($_GET['fby'],$this->cols)) 
            {   
                if (is_super_admin())
                    $sql .= ' WHERE ';
                else
                    $sql .= ' AND ';

                $sql .= ' ' . $this->cols[$_GET['fby']] . ' = ' . $wpdb->prepare('%s', $_GET['fvar']) . ' ';
            }
        }
        
        $total = $wpdb->get_var('SELECT COUNT(*) ' . $sql);
        
        $per_page = 10;
        $pagenum = $this->get_pagenum();

                    
        $this->set_pagination_args( 
            array(
                'total_items' => $total,                  //WE have to calculate the total number of items
                'per_page'    => $per_page                     //WE have to determine how many items to show on a page
            ) 
        );

        $pages = $this->get_pagination_arg('total_pages');
        
        if($pagenum > $pages)
            $pagenum = $pages;
                
        $from = ($pagenum * $per_page) - $per_page;
            
        $limit = ' LIMIT ' . (int)$from . ',' . $per_page;
    
        $_wp_column_headers[$screen->id] = $columns;

        

        $this->items = $wpdb->get_results('SELECT `t1`.`id` AS `gid`,`t1`.`login` AS `acc`,
                                                    `t1`.`user`,`t1`.`blog`,`t1`.`service` AS `serv`,`t2`.`name` AS `msname`,
                                                    `t1`.`name` AS `gname` ' . $sql . $order . $limit, ARRAY_A);
        
    }
    
    function column_default( $item, $column_name ) 
    {
        $columns = $this->get_columns();
        
        if(array_key_exists($column_name,$columns))
        {
            $fby = $this->qarg + array('fby' => $column_name, 'fvar' => $item[ $column_name ]);
            
            if($column_name != 'gid')
                return '<a href="' . add_query_arg($fby) . '">' . esc_html($item[ $column_name ]) . '</a>';
            else
                return (int)$item[$column_name];
        }
        
        return false;
    }
    
   	function get_sortable_columns() {
   	    
        $columns = $this->get_columns();
        
        $sort = array();
        $not = array('blog','user','cb');
    
		foreach($columns as $key => $val)
        {
            if(!in_array($key,$not))
                $sort[$key] = array($key);
        }

        return $sort;
	}
    
    function column_gname($item) 
    {    
        $del_f = $this->qarg + array('fby' => false, 'fvar' => false);
        
        $del = $this->qarg + array('sp_del' => $item['gid']);
        $del = add_query_arg($del);
        
        $edit = $del_f + array('action' => 'gedit', 'gid' => $item['gid'], 'order' => false, 'orderby' => false);
        $edit = add_query_arg($edit);        
        
        $actions = array(
            'edit'      => '<a data-id="'.$item['gid'].'" class="sp_edit sp_mail_grp" href="#" TITLE="' . __('Edit','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/edit.png" /></a>',
            'delete'    => '<a data-id="'.$item['gid'].'" class="sp_del sp_mail_grp"  href="#" TITLE="' . __('Delete','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/del.png" /></a>',
        );
        
        $fby = $this->qarg + array('fby' => 'gname', 'fvar' => $item['gname']); 
        
        return sprintf('%1$s %2$s', '<a href="' . add_query_arg($fby) . '">' . esc_html($item['gname']) . '</a>', $this->row_actions($actions) );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="sp_grps[]" value="%s" />', $item['gid']
        );    
    }
    
    function column_user($item) 
    {
        $fby = $this->qarg + array('fby' => 'user', 'fvar' => $item['user']); 
        
        return '<a href="' . add_query_arg($fby) . '">' . esc_html(get_userdata($item['user'])->user_login) . '</a>';
    }
     
    function column_blog($item) {
        
        $fby = $this->qarg + array('fby' => 'blog', 'fvar' => $item['blog']); 
        
        return '<a href="' . add_query_arg($fby) . '">' . esc_html(get_blog_details($item['blog'])->blogname) . '</a>';
    }
    
    function extra_tablenav( $which ) 
    {
    	if ( $which == "top" ){
              $act = add_query_arg($this->qarg);
              $del_f = $this->qarg + array('fby' => false, 'fvar' => false);
              ?>
               <form id="form-accs-list" action="<?= $act ?>" method="post">
                      <div style=" padding-bottom: 5px; float:  left;">
                        <button id="sp_add_butt" class="sp_mail_grp"><?php _e('Add Group','sp_text_domain') ?></button>
                        <button id="sp_del_butt" class="sp_mail_grp"><?php _e('Delete Groups','sp_text_domain') ?></button>
                        <a href="<?=add_query_arg($del_f);?>"><button id="sp_del_fil_butt" ><?php _e('Cancel All Filters','sp_text_domain'); ?></button></a>
                          <br />
          	          </div>
              <script language="javascript">
              jQuery(document).ready(function() {
                  jQuery(function() {
                        jQuery( "#sp_add_butt" ).button({
                            icons: {
                                primary: "ui-icon-plus"
                            }
                        });                    
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
                   
              <?php
              wp_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
    	}
        
    	if ( $which == "bottom" ){
            echo '</form>'; 
    	}
    }
    
    function no_items() {
        _e( 'No groups found.','sp_text_domain' );
        
    }
}

?>