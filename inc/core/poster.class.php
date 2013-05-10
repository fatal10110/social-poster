<?php

/**
 * @author @Fatal@ 
 * @copyright 2012
 */

abstract class poster
{   
    protected $login = '';
    protected $pass = '';

    protected $c = null;
    
    protected $url = '';
    protected $text = '';
    protected $title = '';
    protected $image = '';
    protected $guid = '';
    protected $desc = '';
    
    protected $args = array();
    
    public function set_params($args,$cook)
    {
        $this->args = $args;
        $this->url = $args['url'];
        $this->text = $args['text'];
        $this->title = $args['title'];
        $this->image = $args['image'];
        $this->guid = $args['guid'];
        $this->page = $args['page'];
        $this->desc = $args['desc'];
        
        $this->login = $args['login'];
        $this->pass = $args['pass'];
    
        $this->c = new cURL(true,$cook);
        
    }
    
    abstract public function login();
    abstract public function post();
    abstract public function logout();
}


?>