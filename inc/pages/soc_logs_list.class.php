<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

if( ! class_exists( 'WP_List_Table_SP_Ex' ) ) {
    require_once( SP_PDIR.'/inc/pages/WP_List_Table_SP_Ex.class.php' );
}

class SP_Soc_Logs_List_Table extends WP_List_Table_SP_Ex 
{
    var $social = array();
    var $qarg = array('del' => false, 'update' => false, 'error' => false);
 
    var $cols = array(
                'pid' => '`t1`.`post`',
                'social' => '`t2`.`soc`',
                'account' => '`t2`.`login`',
                'status' => '`t1`.`status`',
                'date' => '`t1`.`date`',
                'user' => '`t1`.`user`',
                'blog' => '`t2`.`blog`',
                'post_to' => '`t1`.`post_to`',
            );
            
    function __construct()
    {
        parent::__construct( array(
                    'singular'  => __('Soc Log','sp_text_domain'),     //singular name of the listed records
                    'plural'    => __('Soc Logs','sp_text_domain'),    //plural name of the listed records
                    'ajax'      => true        //does this table support ajax?
                ) );
        
        require (SP_PDIR.'/inc/soc.php');
        
        $this->social = $SP_SOCIALS;
    }    
    
    function get_columns(){
          $columns = array(
            'cb' => '<input type="checkbox" />',
            'pid' => __('Post', 'sp_text_domain'), 
            'social' => __('Social','sp_text_domain'), 
            'account' => __('Account', 'sp_text_domain'), 
            'post_to' => __('Post To', 'sp_text_domain'), 
            'status' => __('Status', 'sp_text_domain'),
            'date' => __('Date', 'sp_text_domain'),
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
      
        $sql = ' FROM `' . $wpdb->base_prefix . 'sp_logs` AS `t1` INNER JOIN `' . $wpdb->base_prefix . 'sp_accs` AS `t2` ON `t1`.`acc` = `t2`.`id` '; 

        $where = ' WHERE `t1`.`type` = "0" ';
        
        if (!is_super_admin())
            $where .= ' AND `t2`.`blog` = "' . $blog_id . '" AND `t1`.`user` = "' . $current_user->ID . '" ';
            
        $sql .= $where;
        
        $order = ' ORDER BY `t1`.`date` DESC ';
        
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
                $sql .= ' AND ' . $this->cols[$_GET['fby']] . ' = ' . $wpdb->prepare('%s', $_GET['fvar']) . ' ';
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

        $sql = 'SELECT  `t1`.`id`,`t2`.`login` AS `account`,`t2`.`soc` AS `social`,`t2`.`blog`
                ,`t1`.`id`,`t1`.`user`,`t1`.`post` AS `pid`,`t1`.`status` AS `status`
                ,`t1`.`date`,`t1`.`acc`,`t1`.`post_to` ' . $sql . $order . $limit;

        $this->items = $wpdb->get_results($sql, ARRAY_A);
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
        
        $sort = array('date' => array('date'));
        $not = array('blog','user','cb','date','pid','social','status','post_to');
    
		foreach($columns as $key => $val)
        {
            if(!in_array($key,$not))
                $sort[$key] = array($key);
        }

        return $sort;
	}
    
    function column_post_to($item)
    {
        if(empty($item['post_to'])) return __('Profile','sp_text_domain');
        elseif($item['social'] == '13') return esc_html($item['post_to']);
        else return '<a href="'.esc_url($item['post_to']).'" target="_blank">'.esc_html($item['post_to']).'</a>';
    }
    
    function column_pid($item) 
    {
        $only_post_link = $this->qarg + array('fby' => 'pid', 'fvar' => $item['pid']);
        $del = $this->qarg + array('sp_del' => $item['id']);
        
        $actions = array(
            'edit'      => '<a href="' . esc_url(get_edit_post_link($item['pid'])) . '" TITLE="' . __('Edit','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/edit.png" /></a>',
            'view'    => '<a href="' . esc_url(get_permalink($item['pid'])) . '" TITLE="' . __('View','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/view.png" /></a>',
            'ponly'    => '<a href="' . add_query_arg($only_post_link) . '" title="'.__('Only This Post','sp_text_domain').'"><img width="16" height="16" src="'.SP_PURL.'/img/filter.png" /></a>',
        );

        if(is_super_admin())
            $actions['delete'] = '<a id="sp_'.$item['id'].'" class="sp_del sp_logs"  href="#" TITLE="' . __('Delete','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/del.png" /></a>';

        
        $fby = $this->qarg + array('fby' => 'pid', 'fvar' => $item['pid']); 
        
        return sprintf('%1$s %2$s', '<a href="' .  add_query_arg($fby) . '">' . esc_html(get_the_title($item['pid'])) . '</a>', $this->row_actions($actions) );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="sp_logs[]" value="%s" />', $item['id']
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
    
    function column_social($item) 
    {
        $fby = $this->qarg + array('fby' => 'social', 'fvar' => $item['social']); 
        
        return '<a href="' . add_query_arg($fby) . '">' . esc_html($this->social[$item['social']]['name']) . '</a>';
    }
    
    function column_status($item)
    {
        $status = array(
            '<div style="color: red;">' . __('Fail','sp_text_domain') . '</div>',
            '<div style="color: green;">' . __('Posted','sp_text_domain') . '</div>',
            '<div style="color: blue;">' . __('Processing','sp_text_domain') . '</div>',
             __('In Queue','sp_text_domain'),
        );
        
        $fby = $this->qarg + array('fby' => 'status', 'fvar' => $item['status']); 
        
        return '<a href="' . add_query_arg($fby) . '">' . $status[$item['status']] . '</a>';
    }
    
    function extra_tablenav( $which ) 
    {
    	if ( $which == "top" ){
              $act = add_query_arg($this->qarg);
              $del_f = $this->qarg + array('fby' => false, 'fvar' => false);
              ?>
               <form id="form-accs-list" action="<?= $act ?>" method="post">
              <?php
              wp_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
    	}
        
    	if ( $which == "bottom" ){
            echo '</form>'; 
    	}
    }
    
    function no_items() {
        _e( 'No logs found.' );
    }
}

?>