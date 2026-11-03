<?php

$dir = dirname(__DIR__).'/public/docs/manual';

foreach (glob($dir.'/*.png') as $png) {
    $src = imagecreatefrompng($png);
    if (! $src) {
        echo 'fail '.basename($png).PHP_EOL;
        continue;
    }

    $width = imagesx($src);
    $height = imagesy($src);
    $newWidth = 1100;
    $newHeight = (int) round($height * ($newWidth / $width));
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $jpg = preg_replace('/\.png$/', '.jpg', $png);
    imagejpeg($dst, $jpg, 82);
    imagedestroy($src);
    imagedestroy($dst);
    echo basename($jpg).' '.round(filesize($jpg) / 1024).'KB'.PHP_EOL;
}
