<?php

function sp_x_crypt($data, $key)
{
    $strlen_key = strlen($key);
    $sbuf = '';

    for ($i= $n = 0, $strlen_data = strlen($data); $i < $strlen_data; $i++)
    {
        if ($n >= $strlen_key) $n = 0;
            $sbuf .= chr(ord($key[$n++]) ^ ord($data[$i]));
    }

    return $sbuf;
}

function sp_x_hex_decode($str)
{
    for ($i = 0, $sbuf = null, $strlen = strlen($str); $i < $strlen; $i= $i + 5)
        $sbuf .= chr(hexdec(substr($str, $i+3, 2)));
    return $sbuf;
}

function sp_x_hex_code($str)
{
    for ($i = 0, $sbuf = null, $strlen = strlen($str); $i < $strlen; $i++)
        $sbuf .= chr(rand(65,70)).rand(0,9).chr(rand(65,70)).sprintf('%X', ord(substr($str, $i, 1)));
    
    return $sbuf;
}

function sp_decodex($data,$key)
{
    return sp_x_hex_decode(sp_x_crypt(base64_decode(gzinflate($data)),$key));
}

function sp_codex($data,$key)
{
    return gzdeflate(base64_encode(sp_x_crypt(sp_x_hex_code($data),$key)),9);
}

?>
