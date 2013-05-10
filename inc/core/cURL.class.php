<?php

/**
 * @author @Fatal@ 
 * @copyright 2011
 */

class cURL {
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
     
    function cURL($cookies=TRUE, $cookie_file='cookies.txt', $compression='gzip', $proxy='') {
    	$this->headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    	$this->headers[] = 'Connection: Keep-Alive';
    	$this->headers[] = 'Content-type: application/x-www-form-urlencoded; charset=UTF-8';
    	$this->user_agent =  $_SERVER['HTTP_USER_AGENT'];
    	$this->compression=$compression;
    	$this->proxy=$proxy;
    	$this->cookies=$cookies;
    	if ($this->cookies == TRUE) $this->cookie_file = $cookie_file;
    }
     
    function get($url,$headers = false,$opt = false) {
        
        if(is_array($headers))
            $this->headers = array_merge($this->headers,$headers);
            
    	$process = curl_init($url);
    	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
    	curl_setopt($process, CURLOPT_HEADER, 0);
    	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        
        if ($this->cookies == TRUE)
        {
            curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        
    	curl_setopt($process,CURLOPT_ENCODING , $this->compression);
    	curl_setopt($process, CURLOPT_TIMEOUT, 30);
        
    	if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, $this->proxy);
    	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($process, CURLOPT_AUTOREFERER, true);
        
        if($opt && is_array($opt))
            curl_setopt_array($process, $opt);    
        
    	$return = $this->curl_exec_follow($process);
      
        curl_close($process);
    	return $return;
    }
     
    function post($url,$data,$headers = false,$opt = false) {
        
        if(is_array($headers))
            $this->headers = array_merge($this->headers,$headers);
            
    	$process = curl_init();
        curl_setopt($process, CURLOPT_URL, $url);        
    	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        
    	curl_setopt($process, CURLOPT_HEADER, 0);
    	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
    	
        if ($this->cookies == TRUE)
        {
            curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        
    	curl_setopt($process, CURLOPT_ENCODING , $this->compression);
    	curl_setopt($process, CURLOPT_TIMEOUT, 30);
        
    	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        
    	curl_setopt($process, CURLOPT_POSTFIELDS, $data);
    	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($process, CURLOPT_AUTOREFERER, true);
        
    	curl_setopt($process, CURLOPT_POST, 1);
        
        if($opt && is_array($opt))
            curl_setopt_array($process, $opt);    
        curl_setopt($process, CURLINFO_HEADER_OUT, true);
        
    	$return = $this->curl_exec_follow($process);
        
    	return $return;
    }

    function curl_exec_follow(/*resource*/ &$ch, /*int*/ $redirects = 20, /*bool*/ $curlopt_header = false) {
        if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            return curl_exec($ch);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, false);

        if ($this->cookies == TRUE)
        {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
            
            $url = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
            
            if(preg_match('#^(\w+?\://.+?)/#',$url,$host))
                $host = $host[1];
            else
                $host = $url;
                
            do {
                $data = curl_exec($ch);
               
                if (curl_errno($ch))
                    break;
                    
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if ($code != 301 && $code != 302)
                    break;
                    
                $header_start = strpos($data, "\r\n")+2;
                $headers = substr($data, $header_start, strpos($data, "\r\n\r\n", $header_start)+2-$header_start);
                
                if (!preg_match('/Location:(.*?)\n/', $headers, $matches))
                    break;
                    
                $newurl = trim(array_pop($matches));
                
		
                if(substr($newurl,0,1) == '/')
                    $newurl = $host.$newurl;
                    
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_URL, $newurl);
            
            } while (--$redirects);
            
            if (!$redirects)
                trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                
            if (!$curlopt_header)
                $data = substr($data, strpos($data, "\r\n\r\n")+4);
                
            return $data;
        }
    }
}

?>