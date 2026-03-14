<?php
namespace NanoCDN;

class ImageConverter
{
    public static function generateVariants(int $fileId, string $sourcePath, string $tenantUuid, string $fileUuid, string $safeName, string $origExt): void
    {
        $cfg = config();
        $sizes = $cfg['conversion']['sizes'] ?? [];
        $formats = $cfg['conversion']['formats'] ?? ['png', 'webp', 'avif'];
        $driver = self::detectDriver();

        if (!$driver) {
            return;
        }

        $baseDir = storage_path($tenantUuid . '/' . $fileUuid);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        foreach ($sizes as $size) {
            $w = (int) ($size['w'] ?? 0);
            $h = (int) ($size['h'] ?? 0);
            $sizeKey = $w . 'x' . $h;
            foreach ($formats as $format) {
                if (!self::formatSupported($format, $driver)) {
                    continue;
                }
                $outPath = $baseDir . '/' . $safeName . '-' . $sizeKey . '.' . $format;
                $relativePath = $tenantUuid . '/' . $fileUuid . '/' . $safeName . '-' . $sizeKey . '.' . $format;
                $ok = self::resizeAndConvert($sourcePath, $outPath, $w, $h, $format, $driver);
                if ($ok && is_file($outPath)) {
                    Database::run(
                        'INSERT INTO file_variants (file_id, size_key, format, path, size_bytes) VALUES (?, ?, ?, ?, ?)',
                        [$fileId, $sizeKey, $format, $relativePath, filesize($outPath)]
                    );
                }
            }
        }
    }

    private static function detectDriver(): ?string
    {
        if (extension_loaded('imagick')) {
            return 'imagick';
        }
        if (extension_loaded('gd')) {
            return 'gd';
        }
        return null;
    }

    private static function formatSupported(string $format, string $driver): bool
    {
        if ($driver === 'imagick') {
            return in_array($format, ['png', 'webp', 'avif', 'jpeg', 'gif'], true);
        }
        if ($driver === 'gd') {
            return in_array($format, ['png', 'webp', 'jpeg', 'gif'], true); // AVIF só em PHP 8.1+ com GD
        }
        return false;
    }

    private static function resizeAndConvert(string $src, string $dest, int $w, int $h, string $format, string $driver): bool
    {
        if ($driver === 'imagick') {
            return self::resizeImagick($src, $dest, $w, $h, $format);
        }
        return self::resizeGd($src, $dest, $w, $h, $format);
    }

    private static function resizeImagick(string $src, string $dest, int $w, int $h, string $format): bool
    {
        try {
            $img = new \Imagick($src);
            $img->resizeImage($w, $h, \Imagick::FILTER_LANCZOS, 1);
            $img->setImageFormat($format);
            if ($format === 'webp') {
                $img->setImageCompressionQuality(85);
            }
            if ($format === 'avif') {
                $img->setImageCompressionQuality(80);
            }
            $ok = $img->writeImage($dest);
            $img->destroy();
            return $ok;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function resizeGd(string $src, string $dest, int $w, int $h, string $format): bool
    {
        $info = @getimagesize($src);
        if (!$info) {
            return false;
        }
        $srcImg = null;
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $srcImg = @imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $srcImg = @imagecreatefrompng($src);
                break;
            case IMAGETYPE_GIF:
                $srcImg = @imagecreatefromgif($src);
                break;
            case IMAGETYPE_WEBP:
                $srcImg = @imagecreatefromwebp($src);
                break;
            default:
                return false;
        }
        if (!$srcImg) {
            return false;
        }
        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);
        $dstImg = imagecreatetruecolor($w, $h);
        if (!$dstImg) {
            imagedestroy($srcImg);
            return false;
        }
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $w, $h, $origW, $origH);
        imagedestroy($srcImg);

        $ok = false;
        switch (strtolower($format)) {
            case 'png':
                $ok = imagepng($dstImg, $dest, 8);
                break;
            case 'webp':
                $ok = function_exists('imagewebp') && imagewebp($dstImg, $dest, 85);
                break;
            case 'avif':
                $ok = function_exists('imageavif') && imageavif($dstImg, $dest, 80);
                break;
            case 'jpeg':
            case 'jpg':
                $ok = imagejpeg($dstImg, $dest, 88);
                break;
            case 'gif':
                $ok = imagegif($dstImg, $dest);
                break;
        }
        imagedestroy($dstImg);
        return $ok;
    }

    /** Checker: retorna capacidades do servidor para a interface admin */
    public static function getServerCapabilities(): array
    {
        $gd = extension_loaded('gd');
        $imagick = extension_loaded('imagick');
        $webp = $gd && function_exists('imagewebp');
        $avif = ($gd && function_exists('imageavif')) || $imagick;
        return [
            'gd' => $gd,
            'imagick' => $imagick,
            'webp' => $webp,
            'avif' => $avif,
            'driver' => $imagick ? 'imagick' : ($gd ? 'gd' : null),
            'conversion_available' => $gd || $imagick,
        ];
    }
}
