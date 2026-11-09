<?php

$srcPath = __DIR__ . '/../public/assets/images/eslogo.png';
$dir = __DIR__ . '/../public/assets/images';

$img = @imagecreatefrompng($srcPath);
if (!$img) {
    fwrite(STDERR, "Failed to load eslogo.png\n");
    exit(1);
}

$w = imagesx($img);
$h = imagesy($img);
echo "source={$w}x{$h}\n";

imagealphablending($img, false);
imagesavealpha($img, true);

// Make near-black transparent
for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgba = imagecolorat($img, $x, $y);
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;
        if ($r < 28 && $g < 28 && $b < 28) {
            imagesetpixel($img, $x, $y, imagecolorallocatealpha($img, 0, 0, 0, 127));
        }
    }
}

// Crop to content bounding box
$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;
for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgba = imagecolorat($img, $x, $y);
        $a = ($rgba & 0x7F000000) >> 24;
        if ($a < 120) {
            if ($x < $minX) {
                $minX = $x;
            }
            if ($y < $minY) {
                $minY = $y;
            }
            if ($x > $maxX) {
                $maxX = $x;
            }
            if ($y > $maxY) {
                $maxY = $y;
            }
        }
    }
}

$cw = max(1, $maxX - $minX + 1);
$ch = max(1, $maxY - $minY + 1);
$side = max($cw, $ch);
$pad = (int) round($side * 0.08);
$canvasSide = $side + ($pad * 2);

$mark = imagecreatetruecolor($canvasSide, $canvasSide);
imagealphablending($mark, false);
imagesavealpha($mark, true);
$transparent = imagecolorallocatealpha($mark, 0, 0, 0, 127);
imagefilledrectangle($mark, 0, 0, $canvasSide, $canvasSide, $transparent);

$dx = (int) (($canvasSide - $cw) / 2);
$dy = (int) (($canvasSide - $ch) / 2);
imagecopy($mark, $img, $dx, $dy, $minX, $minY, $cw, $ch);
imagedestroy($img);

echo "cropped={$cw}x{$ch} mark={$canvasSide}x{$canvasSide}\n";

function save_resized($src, int $size, string $path): void
{
    $sw = imagesx($src);
    $sh = imagesy($src);
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, $sw, $sh);
    imagepng($dst, $path, 6);
    imagedestroy($dst);
    echo "wrote {$path}\n";
}

save_resized($mark, 64, "{$dir}/favicon.png");
save_resized($mark, 128, "{$dir}/logo-icon.png");
imagepng($mark, "{$dir}/easyschool-mark.png", 6);
echo "wrote {$dir}/easyschool-mark.png\n";

function make_wordmark($icon, array $textColor, string $path): void
{
    $iw = imagesx($icon);
    $ih = imagesy($icon);
    $iconSize = 96;
    $pad = 16;
    $text = 'EasySchool';
    $fontCandidates = [
        'C:/Windows/Fonts/segoeuib.ttf',
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/arial.ttf',
    ];
    $font = null;
    foreach ($fontCandidates as $f) {
        if (file_exists($f)) {
            $font = $f;
            break;
        }
    }
    if (!$font) {
        fwrite(STDERR, "No TrueType font found\n");
        exit(1);
    }

    $fontSize = 42;
    $bbox = imagettfbbox($fontSize, 0, $font, $text);
    $tw = abs($bbox[2] - $bbox[0]);
    $th = abs($bbox[7] - $bbox[1]);
    $width = $pad + $iconSize + 14 + $tw + $pad;
    $height = max($iconSize, $th) + $pad * 2;

    $canvas = imagecreatetruecolor($width, $height);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $fill = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $fill);

    imagealphablending($canvas, true);
    $ix = $pad;
    $iy = (int) (($height - $iconSize) / 2);
    imagecopyresampled($canvas, $icon, $ix, $iy, 0, 0, $iconSize, $iconSize, $iw, $ih);

    $col = imagecolorallocate($canvas, $textColor[0], $textColor[1], $textColor[2]);
    $tx = $pad + $iconSize + 14;
    $ty = (int) (($height + $th) / 2) - 2;
    imagettftext($canvas, $fontSize, 0, $tx, $ty, $col, $font, $text);

    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagepng($canvas, $path, 6);
    imagedestroy($canvas);
    echo "wrote {$path} ({$width}x{$height})\n";
}

make_wordmark($mark, [15, 23, 42], "{$dir}/logo.png");
make_wordmark($mark, [245, 243, 238], "{$dir}/logo-light.png");

imagedestroy($mark);
echo "done\n";
