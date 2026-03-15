<?php
namespace NanoCDN;

class ImageConverter
{
    /** Tamanhos padrão: bases (LxH) × escalas. Gera apenas downscale/igual (nunca maior que a imagem). */
    public static function getDefaultSizes(): array
    {
        $bases = [[1920, 1080], [1080, 1920], [1024, 1024]];
        $scales = [1, 2, 0.75, 0.5, 0.25, 0.125];
        $byKey = [];
        foreach ($bases as $b) {
            foreach ($scales as $s) {
                $w = max(1, (int) round($b[0] * $s));
                $h = max(1, (int) round($b[1] * $s));
                $key = $w . 'x' . $h;
                $byKey[$key] = ['w' => $w, 'h' => $h, 'key' => $key];
            }
        }
        return array_values($byKey);
    }

    /** Opções globais (base): tamanhos e formatos. Lê de config e sobrescreve com settings (DB) se existir. */
    public static function getGlobalConversionOptions(): array
    {
        $cfg = config();
        $enabled = !empty($cfg['conversion']['enabled']);
        $rawSizes = $cfg['conversion']['sizes'] ?? self::getDefaultSizes();
        $formats = $cfg['conversion']['formats'] ?? ['png', 'webp', 'avif'];
        $quality = (int) ($cfg['conversion']['quality'] ?? 85);
        $quality = max(1, min(100, $quality));

        try {
            $rows = Database::fetchAll('SELECT `key`, `value` FROM settings WHERE `key` IN (\'conversion_enabled\', \'conversion_sizes\', \'conversion_formats\', \'conversion_quality\')');
            $settings = [];
            foreach ($rows as $r) {
                $settings[$r['key']] = $r['value'];
            }
            if (isset($settings['conversion_enabled'])) {
                $enabled = $settings['conversion_enabled'] === '1' || $settings['conversion_enabled'] === 'true';
            }
            if (!empty($settings['conversion_sizes'])) {
                $dec = json_decode($settings['conversion_sizes'], true);
                if (is_array($dec)) {
                    $rawSizes = $dec;
                }
            }
            if (!empty($settings['conversion_formats'])) {
                $dec = json_decode($settings['conversion_formats'], true);
                if (is_array($dec)) {
                    $formats = $dec;
                }
            }
            if (isset($settings['conversion_quality']) && $settings['conversion_quality'] !== '') {
                $q = (int) $settings['conversion_quality'];
                $quality = max(1, min(100, $q));
            }
        } catch (\Throwable $e) {
            // settings não disponível ou tabela inexistente
        }
        if (empty($rawSizes)) {
            $rawSizes = self::getDefaultSizes();
        }

        if (!$enabled) {
            return ['enabled' => false, 'sizes' => [], 'formats' => [], 'size_keys' => [], 'quality' => $quality];
        }
        $sizes = [];
        $sizeKeys = [];
        foreach ($rawSizes as $s) {
            if (is_string($s)) {
                if (preg_match('/^(\d+)\s*x\s*(\d+)$/i', str_replace(' ', '', $s), $m)) {
                    $w = (int) $m[1];
                    $h = (int) $m[2];
                } else {
                    continue;
                }
            } else {
                $w = (int) ($s['w'] ?? 0);
                $h = (int) ($s['h'] ?? 0);
            }
            if ($w < 1 || $h < 1) {
                continue;
            }
            $key = $w . 'x' . $h;
            $sizes[] = ['w' => $w, 'h' => $h, 'key' => $key];
            $sizeKeys[] = $key;
        }
        return ['enabled' => true, 'sizes' => $sizes, 'formats' => $formats, 'size_keys' => $sizeKeys, 'quality' => $quality];
    }

    /** Opções efetivas para um tenant: subconjunto das globais (nunca mais que o global). */
    public static function getEffectiveConversionOptions(array $tenant): array
    {
        $global = self::getGlobalConversionOptions();
        if (!$global['enabled'] || empty($global['formats'])) {
            return ['sizes' => [], 'formats' => [], 'quality' => $global['quality'] ?? 85];
        }
        $tenantSizes = self::decodeJsonColumn($tenant['conversion_sizes'] ?? null);
        $tenantFormats = self::decodeJsonColumn($tenant['conversion_formats'] ?? null);
        $sizes = $global['sizes'];
        if (!empty($tenantSizes)) {
            $allowedKeys = array_flip(array_intersect($tenantSizes, $global['size_keys']));
            $sizes = array_filter($global['sizes'], fn($s) => isset($allowedKeys[$s['key']]));
        }
        $formats = array_values($global['formats']);
        if (!empty($tenantFormats)) {
            $formats = array_values(array_intersect($tenantFormats, $global['formats']));
        }
        return ['sizes' => array_values($sizes), 'formats' => $formats, 'quality' => $global['quality'] ?? 85];
    }

    private static function decodeJsonColumn(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $dec = json_decode($json, true);
        return is_array($dec) ? $dec : [];
    }

    public static function generateVariants(int $fileId, string $sourcePath, string $tenantUuid, string $fileUuid, string $safeName, string $origExt, ?array $tenant = null): void
    {
        $driver = self::detectDriver();
        if (!$driver) {
            return;
        }
        if ($tenant !== null) {
            $effective = self::getEffectiveConversionOptions($tenant);
            $sizes = $effective['sizes'];
            $formats = $effective['formats'];
            $quality = (int) ($effective['quality'] ?? 85);
        } else {
            $global = self::getGlobalConversionOptions();
            $sizes = $global['sizes'];
            $formats = $global['formats'];
            $quality = (int) ($global['quality'] ?? 85);
        }
        $quality = max(1, min(100, $quality));

        if (empty($formats)) {
            return;
        }

        $origDimensions = self::getImageDimensions($sourcePath, $driver);
        if (!$origDimensions) {
            return;
        }
        $origW = $origDimensions['w'];
        $origH = $origDimensions['h'];

        // Incluir tamanho "original" (mesmas dimensões) para outros formatos
        $originalSize = ['w' => $origW, 'h' => $origH, 'key' => 'original'];
        $allSizes = $sizes;
        if (!self::sizeInList($originalSize, $sizes)) {
            $allSizes = array_merge([$originalSize], $sizes);
        }

        $baseDir = storage_path($tenantUuid . '/' . $fileUuid);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        foreach ($allSizes as $size) {
            $w = (int) ($size['w'] ?? 0);
            $h = (int) ($size['h'] ?? 0);
            $sizeKey = $size['key'] ?? ($w . 'x' . $h);
            // Só gerar se a imagem original for >= alvo (nunca esticar/upscale)
            if ($w > $origW || $h > $origH) {
                continue;
            }
            foreach ($formats as $format) {
                if (!self::formatSupported($format, $driver)) {
                    continue;
                }
                // Original já existe no formato de origem; pular para não sobrescrever
                if ($sizeKey === 'original' && strtolower($format) === strtolower($origExt)) {
                    continue;
                }
                $outPath = $baseDir . '/' . $safeName . '-' . $sizeKey . '.' . $format;
                $relativePath = $tenantUuid . '/' . $fileUuid . '/' . $safeName . '-' . $sizeKey . '.' . $format;
                $ok = self::resizeAndConvert($sourcePath, $outPath, $w, $h, $format, $driver, $origW, $origH, $quality);
                if ($ok && is_file($outPath)) {
                    Database::run(
                        'INSERT INTO file_variants (file_id, size_key, format, path, size_bytes) VALUES (?, ?, ?, ?, ?)',
                        [$fileId, $sizeKey, $format, $relativePath, filesize($outPath)]
                    );
                }
            }
        }
    }

    private static function sizeInList(array $size, array $list): bool
    {
        $key = $size['key'] ?? ($size['w'] . 'x' . $size['h']);
        foreach ($list as $s) {
            if (($s['key'] ?? '') === $key) {
                return true;
            }
        }
        return false;
    }

    private static function getImageDimensions(string $path, string $driver): ?array
    {
        if ($driver === 'imagick') {
            try {
                $img = new \Imagick($path);
                $w = $img->getImageWidth();
                $h = $img->getImageHeight();
                $img->destroy();
                return ['w' => $w, 'h' => $h];
            } catch (\Throwable $e) {
                return null;
            }
        }
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }
        return ['w' => (int) $info[0], 'h' => (int) $info[1]];
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

    private static function resizeAndConvert(string $src, string $dest, int $targetW, int $targetH, string $format, string $driver, int $origW, int $origH, int $quality = 85): bool
    {
        if ($driver === 'imagick') {
            return self::resizeImagick($src, $dest, $targetW, $targetH, $format, $origW, $origH, $quality);
        }
        return self::resizeGd($src, $dest, $targetW, $targetH, $format, $origW, $origH, $quality);
    }

    /**
     * Redimensiona sem esticar: escala para cobrir o retângulo alvo e recorta o centro.
     * Só deve ser chamado quando origW >= targetW e origH >= targetH (sem upscale).
     */
    private static function resizeImagick(string $src, string $dest, int $targetW, int $targetH, string $format, int $origW, int $origH, int $quality): bool
    {
        try {
            $img = new \Imagick($src);
            if ($origW !== $img->getImageWidth() || $origH !== $img->getImageHeight()) {
                $origW = $img->getImageWidth();
                $origH = $img->getImageHeight();
            }
            $scale = max($targetW / $origW, $targetH / $origH);
            $scaleW = (int) round($origW * $scale);
            $scaleH = (int) round($origH * $scale);
            $img->resizeImage($scaleW, $scaleH, \Imagick::FILTER_LANCZOS, 1);
            $x = (int) round(($scaleW - $targetW) / 2);
            $y = (int) round(($scaleH - $targetH) / 2);
            $img->cropImage($targetW, $targetH, max(0, $x), max(0, $y));
            $img->setImageFormat($format);
            $img->setImageCompressionQuality($quality);
            $ok = $img->writeImage($dest);
            $img->destroy();
            return $ok;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function resizeGd(string $src, string $dest, int $targetW, int $targetH, string $format, int $origW, int $origH, int $quality): bool
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
        $ow = imagesx($srcImg);
        $oh = imagesy($srcImg);
        $scale = max($targetW / $ow, $targetH / $oh);
        $scaleW = (int) round($ow * $scale);
        $scaleH = (int) round($oh * $scale);
        $intermediate = imagecreatetruecolor($scaleW, $scaleH);
        if (!$intermediate) {
            imagedestroy($srcImg);
            return false;
        }
        imagecopyresampled($intermediate, $srcImg, 0, 0, 0, 0, $scaleW, $scaleH, $ow, $oh);
        imagedestroy($srcImg);
        $x = (int) round(($scaleW - $targetW) / 2);
        $y = (int) round(($scaleH - $targetH) / 2);
        $x = max(0, min($x, $scaleW - $targetW));
        $y = max(0, min($y, $scaleH - $targetH));
        $dstImg = imagecreatetruecolor($targetW, $targetH);
        if (!$dstImg) {
            imagedestroy($intermediate);
            return false;
        }
        imagecopy($dstImg, $intermediate, 0, 0, $x, $y, $targetW, $targetH);
        imagedestroy($intermediate);

        $q = (int) round($quality * 0.9); // PNG compression 0-9
        $q = min(9, max(0, (int) (9 - ($quality / 100) * 9)));
        $ok = false;
        switch (strtolower($format)) {
            case 'png':
                $ok = imagepng($dstImg, $dest, $q);
                break;
            case 'webp':
                $ok = function_exists('imagewebp') && imagewebp($dstImg, $dest, $quality);
                break;
            case 'avif':
                $ok = function_exists('imageavif') && imageavif($dstImg, $dest, $quality);
                break;
            case 'jpeg':
            case 'jpg':
                $ok = imagejpeg($dstImg, $dest, $quality);
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
