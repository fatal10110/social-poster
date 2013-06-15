<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */

if( ! class_exists( 'WP_List_Table_SP_Ex' ) ) {
    require_once( SP_PDIR.'/inc/pages/WP_List_Table_SP_Ex.class.php' );
}
class SP_Services_List_Table extends WP_List_Table_SP_Ex 
{
    var $social = array();
    var $qarg = array('del' => false, 'update' => false, 'error' => false);
 
    var $cols = array(
                'sname' => '`sname`',
                'smpt' => '`smpt`',
                'port' => '`port`',
            );
            
    function __construct()
    {
        parent::__construct( array(
                    'singular'  => __('Service','sp_text_domain'),     //singular name of the listed records
                    'plural'    => __('Services','sp_text_domain'),    //plural name of the listed records
                    'ajax'      => true        //does this table support ajax?
                ) );
        
        require (SP_PDIR.'/inc/soc.php');
        
        $this->social = $SP_SOCIALS;
    }    
    
    function get_columns(){
          $columns = array(
            'cb' => '<input type="checkbox" />', 
            'sname' => __('Name','sp_text_domain'), 
            'smpt' => __('Smpt', 'sp_text_domain'), 
            'port' => __('Port', 'sp_text_domain'),
            'ssl' => __('SSL', 'sp_text_domain'),
          );
        
        return $columns;
    }
    
    function prepare_items() {
        global $wpdb, $blog_id, $current_user;
        
        $columns = $this->get_columns();
      
        $hidden = array();
      
        $sortable = $this->get_sortable_columns();
      
        $this->_column_headers = array($columns, $hidden, $sortable);
      
        $sql = ' FROM `' . $wpdb->base_prefix . 'sp_mail_services` WHERE 1 '; 
        
        $order = '';
        
        if (!empty($_GET['orderby']) && array_key_exists($_GET['orderby'], $this->cols)) 
        {    
            if (isset($_GET['order']) && $_GET['order'] == 'asc')
                $order = ' ORDER BY ' . $this->cols[$_GET['orderby']] . ' ASC ';
            else
                $order = ' ORDER BY ' . $this->cols[$_GET['orderby']] . ' DESC ';
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

        $sql = 'SELECT  `id`,`name` AS `sname`,`port`,`ssl`,`smpt` ' . $sql . $order . $limit;

        $this->items = $wpdb->get_results($sql, ARRAY_A);
    }
    
    function column_default( $item, $column_name ) 
    {
        $columns = $this->get_columns();
        
        if(array_key_exists($column_name,$columns))
        {
            return esc_html($item[$column_name]);
        }
        
        return false;
    }
    
   	function get_sortable_columns() {
   	    
        $columns = $this->get_columns();
        
        $sort = array();
        $not = array('ssl','port');
    
		foreach($columns as $key => $val)
        {
            if(!in_array($key,$not))
                $sort[$key] = array($key);
        }

        return $sort;
	}
    
    function column_sname($item) 
    {
        $del = $this->qarg + array('sp_del' => $item['id']);
        $edit = $this->qarg + array('action' => 'sedit', 'sid' => $item['id'], 'order' => false, 'orderby' => false);;
        
        $actions = array(
            'edit'      => '<a data-id="'.$item['id'].'" class="sp_edit sp_mail_svc" href="#" TITLE="' . __('Edit','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/edit.png" /></a>',
            'delete'    => '<a data-id="'.$item['id'].'" class="sp_del sp_mail_svc"  href="#" TITLE="' . __('Delete','sp_text_domain') . '"><img width="13" height="13" src="'.SP_PURL.'/img/del.png" /></a>',

        );
        
        return sprintf('%1$s %2$s', esc_html($item['sname']), $this->row_actions($actions) );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="sp_srvc[]" value="%s" />', $item['id']
        );    
    }
    
    function column_ssl($item) 
    {
        if($item['ssl'] == '1') return __('Yes','sp_text_domain');
        else return __('No','sp_text_domain');
    }
    
    function extra_tablenav( $which ) 
    {
    	if ( $which == "top" ){
              $act = add_query_arg($this->qarg);
              ?>
               <form id="form-accs-list" action="<?= $act ?>" method="post">
                 <div style=" padding-bottom: 5px; float:  left;">
                        <button id="sp_add_butt" class="sp_mail_svc"><?php _e('Add service','sp_text_domain') ?></button>
                        <button id="sp_del_butt" class="sp_mail_svc"><?php _e('Delete Services','sp_text_domain') ?></button>
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
        _e( 'No services found.' );
    }
}

?>