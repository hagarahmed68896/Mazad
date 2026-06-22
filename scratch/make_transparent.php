<?php

function makeTransparent($sourcePath, $destPath) {
    if (!file_exists($sourcePath)) {
        echo "File not found: $sourcePath\n";
        return false;
    }
    
    $data = file_get_contents($sourcePath);
    $img = imagecreatefromstring($data);
    if (!$img) {
        echo "Failed to load image from string: $sourcePath\n";
        return false;
    }
    
    $width = imagesx($img);
    $height = imagesy($img);
    
    $newImg = imagecreatetruecolor($width, $height);
    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
    
    $trans = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
    imagefill($newImg, 0, 0, $trans);
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $colorIndex = imagecolorat($img, $x, $y);
            $colors = imagecolorsforindex($img, $colorIndex);
            
            $r = $colors['red'];
            $g = $colors['green'];
            $b = $colors['blue'];
            $a = $colors['alpha'];
            
            // If the pixel is very close to white (background), make it fully transparent
            if ($r > 248 && $g > 248 && $b > 248) {
                imagesetpixel($newImg, $x, $y, $trans);
            } else {
                $newColor = imagecolorallocatealpha($newImg, $r, $g, $b, $a);
                imagesetpixel($newImg, $x, $y, $newColor);
            }
        }
    }
    
    imagepng($newImg, $destPath);
    imagedestroy($img);
    imagedestroy($newImg);
    echo "Successfully made transparent: $destPath\n";
    return true;
}

$baseDir = "c:/Users/eng.Hagar Ahmed/Mazad/public/images";
makeTransparent("$baseDir/car_inspection.png", "$baseDir/car_inspection.png");
makeTransparent("$baseDir/car_shipping.png", "$baseDir/car_shipping.png");
