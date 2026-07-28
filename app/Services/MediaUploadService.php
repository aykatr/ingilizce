<?php

namespace App\Services;

use App\Services\Exceptions\ValidationException;

class MediaUploadService
{
    private const IMAGE_EXTENSIONS = ['webp', 'png', 'jpg', 'jpeg'];
    private const AUDIO_EXTENSIONS = ['mp3', 'ogg'];
    private const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    private const AUDIO_MAX_BYTES = 10 * 1024 * 1024;

    public function __construct(private string $uploadsRoot)
    {
    }

    public function handleImage(?array $file, string $directory, string $filename, bool $remove, ?string $currentPath): ?string
    {
        return $this->handle($file, $directory, $filename, $remove, $currentPath, self::IMAGE_EXTENSIONS, self::IMAGE_MAX_BYTES);
    }

    public function handleAudio(?array $file, string $directory, string $filename, bool $remove, ?string $currentPath): ?string
    {
        return $this->handle($file, $directory, $filename, $remove, $currentPath, self::AUDIO_EXTENSIONS, self::AUDIO_MAX_BYTES);
    }

    public function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $absolutePath = dirname($this->uploadsRoot) . '/' . $relativePath;

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    private function handle(
        ?array $file,
        string $directory,
        string $filename,
        bool $remove,
        ?string $currentPath,
        array $allowedExtensions,
        int $maxBytes
    ): ?string {
        $hasNewFile = $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

        if (!$hasNewFile) {
            if ($remove && $currentPath) {
                $this->delete($currentPath);

                return null;
            }

            return $currentPath;
        }

        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new ValidationException('Desteklenmeyen dosya formatı: .' . $extension);
        }

        if ($file['size'] > $maxBytes) {
            throw new ValidationException('Dosya boyutu çok büyük (maksimum ' . round($maxBytes / 1024 / 1024) . ' MB).');
        }

        if ($currentPath) {
            $this->delete($currentPath);
        }

        $targetDir = $this->uploadsRoot . '/' . $directory;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new ValidationException('Yükleme klasörü oluşturulamadı.');
        }

        $absolutePath = $targetDir . '/' . $filename . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new ValidationException('Dosya yüklenemedi.');
        }

        return 'uploads/' . $directory . '/' . $filename . '.' . $extension;
    }
}
