<?php

/**
 * @author @Fatal@
 * @copyright 2012
 */
 
class starter
{
    private $queue_file = 'other/queue.txt';
    private $status = 0;
    private $st_id = 0;
    private $path = '';
    private $line = '';
    
    protected $args = array();
    
    public function __construct()
    { 
        if(!$this->decode_args())
            return false;
        
        if(!isset($_GET['connected'])) $this->start_new();
        
        $this->status = new status($this->args['fpath']);
        
        if($this->args['type'] === 'soc')
            $this->start_soc();
        else
            $this->start_mail(); 
    }
    
    private function start_new()
    {
        $queue = new SP_FILES($this->queue_file);
        $size = $queue->ffget_size();
        
        if($size > 0)
        {
            /** Plugin occupation level **/
            $cooks = scandir($this->path . '/other/cooks/');
            $cooks = count($cooks) - 2;
            /****************************/

            if($cooks < 5)
            {
                if(is_sapi())
                {
                    $cli = file_get_contents('other/cli.txt');
                    $script = 'starter.php';
                    file_put_contents('aa.txt',$cli.' '.$script);
                    sp_exec_nowait($cli.' '.$script);                    
                } else {
                    $fp = fsockopen($this->args['serv'], 80, $errno, $errstr, 5);
            
                    if ($fp) {
                        $out = "GET /" . $this->args['ur_path'] . '/starter.php?sec=' . urlencode($_GET['sec']) . " HTTP/1.1\r\n";
                        $out .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
                        $out .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                    
                        fwrite($fp, $out);
                    
                        sleep(1);
                        fclose($fp);
                    }
                }
            }
        }
        
        $queue->ffunlock();
        
    }
    
    private function decode_args($file = false)
    {
        require('inc/key.php');
        
        if(!$this->get_data($file)) return false;
        
        if(empty($this->line)) return false;
        
        $this->args = unserialize(base64_decode($this->line));
        
        if(!is_array($this->args)) return false;

        if(!isset($this->args['log']) || !is_numeric($this->args['log']))
            return false;
        
        $this->st_id = $this->args['log'];
        $this->path = $this->args['path'];

        $this->args['pass'] = sp_decodex($this->args['pass'],$crypt_key);
           
        return true;
    }
    
    private function get_data($file)
    {
        if($file)
            $this->queue = new SP_FILES($file);
        else
            $this->queue = new SP_FILES($this->queue_file);
        
        $data = explode("\r\n",trim($this->queue->ffread()));
        
        if(empty($data) || !is_array($data)) 
        {
            $this->queue->ffunlock();
           return false; 
        }
        
        $this->line = array_shift($data);
        
        if($file)
        {  
            if(empty($data))
            {
                $this->queue->ffwrite('');
                $this->queue->ffunlock();
                
                return false;
                
            } else
                $this->line = $data[0]; 
                
        }
        
        if(!empty($data))
            $data = implode("\r\n",$data);
        else
            $data = '';
        
        $this->queue->ffwrite($data);
        
        $this->queue->ffunlock();
        
        return true;
    }
    
    private function start_mail()
    {       
        
        if(empty($this->args['emails']))
        {
            $this->status->set_status('0',$this->st_id);   
            return false;
        }
        
        $emails = unserialize($this->args['emails']);
        
        if(!is_array($emails))
        {
            $this->status->set_status('0',$this->st_id);   
            return false;            
        }
        
        $accque = $this->path.'/other/queue/'.base64_encode($this->args['login']).'_'.base64_encode($this->args['smpt']).'_spmail';
        
        $acc_queue = new SP_FILES($accque);
        
        $data = $acc_queue->ffread();
        
        $acc_queue->ffwrite($data.$this->line);
        
        if(empty($data))
        {   
            require_once('class.phpmailer.php');
            
            while(1)
            {
                $acc_queue->ffunlock();
                
                $mail = new PHPMailer();
                $mail->IsSMTP();       
                
                   foreach($emails as $email)
                   {
                        $mail->AddAddress($email);
                        $mail->SetFrom($email);
                        $mail->AddReplyTo($email);
  
                   }
                $mail->SMTPAuth   = true;                  // enable SMTP authentication
       
                //if($this->args['ssl'] == 1)
                   $mail->SMTPSecure = "ssl";
                   
                 $mail->Host       = $this->args['smpt'];      // sets GMAIL as the SMTP server
                $mail->Port       = $this->args['port'];      
                $mail->Username   = $this->args['login'];  // GMAIL username
                $mail->Password   = $this->args['pass'];               

                
                
                $mail->MsgHTML($this->args['text']);
                
                 $mail->Subject = $this->args['title'];  
            
            
                $this->status->set_status('3',$this->st_id);   
                  
                if(!$mail->Send()) {
                  $this->status->set_status('0',$this->st_id);
                } else {
                  $this->status->set_status('1',$this->st_id);
                }
                if(!$this->decode_args($accque))
                {
                    sleep(3);
                    break;
                } else sleep(5);            
            } 
        } else
            $acc_queue->ffunlock();
        
        $this->queue = new SP_FILES($this->queue_file);
        
        $data = $this->queue->ffread();

        if(!empty($data))
        {
            $this->queue->ffunlock();
            
            if(!isset($_GET['connected'])) $this->start_new(); 
            elseif(!$this->decode_args()) $this->start_mail();
        }
        
        die();      
    }
    
    private function start_soc()
    {
        require_once ('inc/soc.php');
        
        if(isset($this->args['page']))
            $this->page = $this->args['page'];
        
        if(!isset($SP_SOCIALS[$this->args['soc']]))
        {
            $this->status->set_status('0',$this->st_id);
            return false;
        }
        
        $soc = $SP_SOCIALS[$this->args['soc']];
        
        $accque = $this->path.'/other/queue/'.base64_encode($this->args['login']).'_'.$soc['prefix'].'.txt';
        
        $acc_queue = new SP_FILES($accque);
        
        $data = $acc_queue->ffread();
        
        $acc_queue->ffwrite($data.$this->line);
        
        
        if(empty($data))
        { 
            require_once('poster.class.php');
            require_once('soc/'.$soc['file']);
            
            $cook = $this->path.'/other/cooks/'.base64_encode($this->args['login']).sp_rnd('5').'_'.$soc['prefix'].'.txt';
            
            $poster->set_params($this->args,$cook);
            
            $poster->login();
            
            while(1)
            {
                $acc_queue->ffunlock();
                 
                $poster->set_params($this->args,$cook);
                
                $this->status->set_status('2',$this->st_id);
                $this->status->set_status($poster->post(),$this->st_id);

                if(!$this->decode_args($accque))
                {
                    $poster->logout();
                    
                    sleep(3);
                    
                    unlink($cook);
                    
                    break;
                } else sleep(5);
            } 
        } else 
            $acc_queue->ffunlock();
        
        $this->queue = new SP_FILES($this->queue_file);
        
        $data = $this->queue->ffread();

        if(!empty($data))
        {
            $this->queue->ffunlock();
            
            if(!isset($_GET['connected'])) $this->start_new(); 
            elseif(!$this->decode_args()) $this->start_soc();
        }
        
        die();
    }                   
}

?>