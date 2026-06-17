<?php

function removeWhiteBackground($sourcePath, $destPath) {
    echo "Processing $sourcePath -> $destPath\n";
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        echo "Error: Cannot get image size for $sourcePath\n";
        return false;
    }
    
    $mime = $imageInfo['mime'];
    if ($mime == 'image/png') {
        $src = imagecreatefrompng($sourcePath);
    } elseif ($mime == 'image/jpeg' || $mime == 'image/jpg') {
        $src = imagecreatefromjpeg($sourcePath);
    } else {
        echo "Error: Unsupported mime type $mime\n";
        return false;
    }
    
    if (!$src) {
        echo "Error: Failed to load image $sourcePath\n";
        return false;
    }
    
    $width = imagesx($src);
    $height = imagesy($src);
    
    $dst = imagecreatetruecolor($width, $height);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    
    $transparentColor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $transparentColor);
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($src, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            // Calculate average brightness
            $brightness = ($r + $g + $b) / 3;
            
            if ($brightness >= 248) {
                // Pure/Near white background -> fully transparent
                $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            } elseif ($brightness > 165) {
                // Smooth transition for shadows and floor reflections
                // Fade from 0 (opaque) at 165 to 127 (transparent) at 248
                $alpha = (int)(($brightness - 165) * (127 / (248 - 165)));
                $color = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
            } else {
                // Solid object pixel
                $color = imagecolorallocatealpha($dst, $r, $g, $b, 0);
            }
            imagesetpixel($dst, $x, $y, $color);
        }
    }
    
    imagepng($dst, $destPath);
    imagedestroy($src);
    imagedestroy($dst);
    echo "Saved transparent image to $destPath\n";
    return true;
}

$dir = "C:\\Users\\eng.Hagar Ahmed\\.gemini\\antigravity-ide\\brain\\cf5ff10a-f8e0-418b-a29b-bc23ef6cc8f0\\";

$images = [
    $dir . "banner_car_white_1781552252987.png" => "c:\\Users\\eng.Hagar Ahmed\\Mazad\\public\\images\\banner_car.png",
    $dir . "banner_villa_white_1781552273453.png" => "c:\\Users\\eng.Hagar Ahmed\\Mazad\\public\\images\\banner_villa.png",
    $dir . "banner_electronics_white_1781552288263.png" => "c:\\Users\\eng.Hagar Ahmed\\Mazad\\public\\images\\banner_electronics.png"
];

foreach ($images as $src => $dst) {
    removeWhiteBackground($src, $dst);
}
echo "All done!\n";
