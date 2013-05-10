<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

if( ! class_exists( 'WP_List_Table_SP_Ex' ) ) {
    require_once( SP_PDIR.'/inc/pages/WP_List_Table_SP_Ex.class.php' );
}

class SP_Acc_List_Table extends WP_List_Table_SP_Ex 
{
    var $social = array();
    var $qarg = array('del' => false, 'update' => false, 'error' => false);
    var $cols = array(
                'aid' => '`id`',
                'account' => '`login`',
                'social' => '`soc`',
                'user' => '`user`',
                'blog' => '`blog`'
            );
    var $url = '';
                
    function __construct()
    {
        parent::__construct( array(
                    'singular'  => __('Account','sp_text_domain'),     //singular name of the listed records
                    'plural'    => __('Accounts','sp_text_domain'),    //plural name of the listed records
                    'ajax'      => true        //does this table support ajax?
                ) );
        
        require (SP_PDIR.'/inc/soc.php');
        
        $this->social = $SP_SOCIALS;
        //$this->url =  ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    function get_columns(){
          $columns = array(
            'cb' => '<input type="checkbox" />',
            'aid' => __('ID', 'sp_text_domain'),
            'account' => __('Account', 'sp_text_domain'),
            'social'      => __('Social', 'sp_text_domain'),
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
      
        $sql = ' FROM `' . $wpdb->base_prefix . 'sp_accs` ';
 
        if (!is_super_admin())
            $sql .= ' WHERE `blog` = "' . $blog_id . '" AND `user` = "' . $current_user->ID . '" ';

        
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
        
        $per_page = 5;

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

        $this->items = $wpdb->get_results('SELECT `id` AS `aid`,`login` AS `account`,`user`,`blog`,`soc` AS `social` ' . $sql . $order . $limit, ARRAY_A);
    }
    
    function column_default( $item, $column_name ) 
    {
        $columns = $this->get_columns();
        
        if(array_key_exists($column_name,$columns))
        {
            $fby = $this->qarg + array('fby' => $column_name, 'fvar' => $item[ $column_name ]);
            
            if($column_name != 'aid')
                return '<a href="' . add_query_arg($fby) . '" title="'.__('Click to activate the filter').'">' . esc_html($item[ $column_name ]) . '</a>';
            else
                return (int)$item[$column_name];
        }
        
        return false;
    }
    
   	function get_sortable_columns() {
   	    
        $columns = $this->get_columns();
        
        $sort = array();
        $not = array('social','blog','user','cb');
    
		foreach($columns as $key => $val)
        {
            if($key == 'cb')
                $key = substr($key,3);
            
            if(!in_array($key,$not))
                $sort[$key] = array($key);
        }

        return $sort;
	}
    
    function column_account($item) 
    {    
        $del_f = $this->qarg + array('fby' => false, 'fvar' => false);
        
        $del = $this->qarg + array('sp_del' => $item['aid']);
        $del = add_query_arg($del);
        
        $edit = $del_f + array('action' => 'aedit', 'aid' => $item['aid'], 'order' => false, 'orderby' => false);
        $edit = add_query_arg($edit);        
        
        $actions = array(
            'edit'      => '<a id="sp_'.$item['aid'].'" class="sp_edit sp_acc" href="#" TITLE="' . __('Edit Account','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/edit.png" /></a>',
            'delete'    => '<a id="sp_'.$item['aid'].'" class="sp_del sp_acc"  href="#" TITLE="' . __('Delete','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/del.png" /></a>',
        );
        
        $fby = $this->qarg + array('fby' => 'account', 'fvar' => $item['account']); 
        
        return sprintf('%1$s %2$s', '<a href="' . add_query_arg($fby) . '" title="'.__('Click to activate the filter','sp_text_domain').'">' . esc_html($item['account']) . '</a>', $this->row_actions($actions) );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="sp_accs[]" value="%s" />', $item['aid']
        );    
    }
    
    function column_user($item) 
    {
        $fby = $this->qarg + array('fby' => 'user', 'fvar' => $item['user']); 
        
        return '<a href="' . add_query_arg($fby) . '" title="'.__('Click to activate the filter','sp_text_domain').'">' . esc_html(get_userdata($item['user'])->user_login) . '</a>';
    }
    
        function column_social($item) 
    {
        $fby = $this->qarg + array('fby' => 'social', 'fvar' => $item['social']); 
        
        return '<a href="' . add_query_arg($fby) . '" title="'.__('Click to activate the filter','sp_text_domain').'">' . esc_html($this->social[$item['social']]['name']) . '</a>';
    }
    
    function column_blog($item) 
    {    
        $fby = $this->qarg + array('fby' => 'blog', 'fvar' => $item['blog']); 
        
        return '<a href="' . add_query_arg($fby) . '" title="'.__('Click to activate the filter','sp_text_domain').'">' . esc_html(get_blog_details($item['blog'])->blogname) . '</a>';
    }
    
    function extra_tablenav( $which ) 
    {
    	if ( $which == "top" ){
              $act = add_query_arg($this->qarg);
              $del_f = $this->qarg + array('fby' => false, 'fvar' => false);
              ?>

               <form id="form-accs-list" action="<?= $act ?>" method="post">
                      <div style=" padding-bottom: 5px; float:  left;">
                          <button id="sp_add_butt" class="sp_acc"><?php _e('Add Account','sp_text_domain') ?></button>
                          <button id="sp_del_butt" class="sp_acc"><?php _e('Delete Accounts','sp_text_domain') ?></button>
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
                        jQuery( "#sp_del_butt" ).button({
                            icons: {
                                primary: "ui-icon-trash"
                            }
                        });
                         jQuery( "#sp_del_fil_butt" ).button({
                            icons: {
                                primary: "ui-icon-cancel"
                            }
                        });                       
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
        _e( 'No accounts found.' , 'sp_text_domain');
    }
    
}

?>