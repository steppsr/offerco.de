<?php

function generateRandomString($length = 5) 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) 
    {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

function sanitize_user_input($data)
{
    $data = trim($data);
    // $data = (get_magic_quotes_gpc()) ? stripslashes($data) : $data;  // Not needed with PHP 7.0 and up.
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // $data = filter_var($data, FILTER_SANITIZE_STRING);               // Not needed with PHP 7.0 and up.
    return $data;
}

function base58_encode($string)
{
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $base = strlen($alphabet);

    $bytes = array_values(unpack('C*', $string));
    $decimal = $bytes[0];

    for ($i = 1, $l = count($bytes); $i < $l; $i++) {
        $decimal = bcmul($decimal, 256);
        $decimal = bcadd($decimal, $bytes[$i]);
    }

    $output = '';
    while ($decimal >= $base) {
        $div = bcdiv($decimal, $base, 0);
        $mod = bcmod($decimal, $base);
        $output .= $alphabet[$mod];
        $decimal = $div;
    }

    if ($decimal > 0) {
        $output .= $alphabet[$decimal];
    }

    $output = strrev($output);

    foreach ($bytes as $byte) {
        if ($byte === 0) {
            $output = $alphabet[0] . $output;
            continue;
        }
        break;
    }

    return $output;
}


?>