<?php
// helpers/ImageUpload.php

function uploadOptimizedImage(array $file, string $uploadDir, int $maxWidth = 1600, int $jpegQuality = 78, ?string $prefix = null): string
{
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed.');
    }

    // Limit upload size (10 MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('Image is too large. Maximum size is 10 MB.');
    }

    // Create folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $info = getimagesize($file['tmp_name']);
    if ($info === false) {
        throw new Exception('Uploaded file is not a valid image.');
    }

    $mime   = $info['mime'];
    $width  = $info[0];
    $height = $info[1];

    // Create image resource
    switch ($mime) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $src = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            throw new Exception('Only JPG, PNG, and WEBP images are allowed.');
    }

    // Resize if necessary
    if ($width > $maxWidth) {
        $newWidth  = $maxWidth;
        $newHeight = (int) round(($height / $width) * $newWidth);
    } else {
        $newWidth  = $width;
        $newHeight = $height;
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // White background for PNG transparency
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    imagecopyresampled(
        $dst, $src,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $width, $height
    );

    // Derive a filename prefix automatically if one wasn't given —
    // e.g. .../uploads/gallery/ -> "gallery", .../uploads/ -> "img"
    if ($prefix === null) {
        $base = basename(rtrim($uploadDir, '/\\'));
        $prefix = ($base !== '' && strtolower($base) !== 'uploads') ? $base : 'img';
    }

    // Derive the relative web path from the absolute upload dir automatically,
    // e.g. C:\...\htdocs\nexgenschools\uploads\gallery\  ->  uploads/gallery
    $normalized  = str_replace('\\', '/', rtrim($uploadDir, '/\\'));
    $pos         = strripos($normalized, 'uploads');
    $relativeDir = $pos !== false ? substr($normalized, $pos) : 'uploads';

    $filename     = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.jpg';
    $relativePath = $relativeDir . '/' . $filename;
    $fullPath     = rtrim($uploadDir, '/') . '/' . $filename;

    imagejpeg($dst, $fullPath, $jpegQuality);

    imagedestroy($src);
    imagedestroy($dst);

    return $relativePath;
}