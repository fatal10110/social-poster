<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */
 
 if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_List_Table_SP_Ex extends WP_List_Table
{
    function __construct($arr = array())
    {
        parent::__construct( $arr);
    }
    
    function parse_global($arr,&$glob)
    {
        foreach($arr as $key => $val)
        {
            if(is_array($glob[$key])) $this->parse_global($val,$glob[$key]);
            else $glob[$key] = $val;
        }
    }
    
    function set_url($url)
    {
        $url = urldecode($url);
        
        $purl = parse_url($url);
        
        $_SERVER['HTTP_HOST'] = $purl['host'];
        $_SERVER['REQUEST_URI'] = $purl['path'].'?'.$purl['query'];
        
        parse_str($purl['query'],$out);
        
        $this->parse_global($out,$_REQUEST);
        $this->parse_global($out,$_GET);
    }
    
    function ajax_user_can()
    {
        if(sp_user_rights())
            return true;
        
        return false;
    }

	function ajax_response() {
		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();

		$rows = ob_get_clean();
        
		$response = array( 'rows' => $rows );

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
            
            ob_start();
            $this->display_tablenav('top');
            $response['nav_top'] = ob_get_clean();
            
            ob_start();            
            $this->display_tablenav('bottom');
            $response['nav_bottom'] = ob_get_clean();
		}

		die( json_encode( $response ) );
	}
}

?>