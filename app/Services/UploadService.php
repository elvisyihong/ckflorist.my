<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Env;

final class UploadService
{
    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    public function store(array $file, int $userId, string $altText): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('The image upload did not complete.');
        }
        $max = (int) Env::get('UPLOAD_MAX_BYTES', '5242880');
        if (($file['size'] ?? 0) < 1 || $file['size'] > $max) {
            throw new \InvalidArgumentException('The image exceeds the upload size limit.');
        }
        $temporary = (string) ($file['tmp_name'] ?? '');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporary);
        if (!is_string($mime) || !in_array($mime, self::ALLOWED, true)) {
            throw new \InvalidArgumentException('Upload a JPG, PNG, WebP, or supported AVIF image.');
        }
        $details = @getimagesize($temporary);
        if (!$details || $details[0] < 1 || $details[1] < 1) {
            throw new \InvalidArgumentException('The uploaded file is not a valid image.');
        }
        $binary = file_get_contents($temporary);
        $image = $binary === false ? false : @imagecreatefromstring($binary);
        if (!$image) {
            throw new \InvalidArgumentException('This server cannot safely decode the image.');
        }
        $name = bin2hex(random_bytes(20));
        $directory = BASE_PATH . '/public/uploads/' . gmdate('Y/m');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Could not create upload directory.');
        }
        $path = $directory . '/' . $name . '.webp';
        $thumbPath = $directory . '/' . $name . '-480.webp';
        if (!imagewebp($image, $path, 84)) {
            imagedestroy($image);
            throw new \RuntimeException('Could not encode image.');
        }
        $thumb = $this->resize($image, 480);
        imagewebp($thumb, $thumbPath, 80);
        imagedestroy($thumb);
        imagedestroy($image);
        chmod($path, 0644);
        chmod($thumbPath, 0644);

        $relative = ltrim(str_replace(BASE_PATH, '', $path), '/');
        $thumbRelative = ltrim(str_replace(BASE_PATH, '', $thumbPath), '/');
        $statement = Database::connection()->prepare('INSERT INTO media (path, thumbnail_path, responsive_paths, mime_type, width, height, size_bytes, alt_text, uploaded_by) VALUES (:path, :thumb, :responsive, :mime, :width, :height, :size, :alt, :user)');
        $statement->execute([
            'path' => $relative, 'thumb' => $thumbRelative,
            'responsive' => json_encode(['480' => $thumbRelative, 'original' => $relative], JSON_THROW_ON_ERROR),
            'mime' => 'image/webp', 'width' => $details[0], 'height' => $details[1],
            'size' => filesize($path), 'alt' => mb_substr(trim($altText), 0, 255), 'user' => $userId,
        ]);
        return ['id' => (int) Database::connection()->lastInsertId(), 'path' => '/' . $relative, 'thumbnail' => '/' . $thumbRelative];
    }

    private function resize(\GdImage $source, int $maxWidth): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = min($maxWidth, $width);
        $targetHeight = (int) round($height * ($targetWidth / $width));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        return $target;
    }
}

