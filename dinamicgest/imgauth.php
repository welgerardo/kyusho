<?php
session_start();
unset($_SESSION['codigo']);
$_SESSION['codigo'] = rand(1000, 9999);
if(!empty($_SESSION['codigo']))
{
    if (!extension_loaded('gd'))
    {
        dl('php_gd2.dll');
    };

    $im = imagecreate(40, 25);
    $bg_color = imagecolorallocate($im, 255, 255, 255);
    $text_color = imagecolorallocate($im, 23,116,157);
    imagestring($im, 10, 2, 2, $_SESSION['codigo'], $text_color);
    header("Content-type: image/png");
    imagepng($im);
    imagedestroy($im);
}

?>