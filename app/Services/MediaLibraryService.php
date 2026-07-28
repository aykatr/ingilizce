<?php

namespace App\Services;

use App\Repositories\Contracts\MediaFileRepositoryInterface;
use App\Services\Exceptions\ValidationException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class MediaLibraryService
{
    private const IMAGE_EXTENSIONS = ['webp', 'png', 'jpg', 'jpeg'];
    private const AUDIO_EXTENSIONS = ['mp3', 'ogg'];
    private const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    private const AUDIO_MAX_BYTES = 10 * 1024 * 1024;
    private const LIBRARY_DIRECTORY = 'media-library';

    private const SOURCE_LABELS = [
        'question' => 'Soru',
        'question_option' => 'Soru Seçeneği',
        'achievement_message' => 'Başarı Mesajı',
        'badge' => 'Rozet',
        'transition_message' => 'Geçiş Mesajı',
        'setting' => 'Başlangıç Ekranı Ayarı',
    ];

    public function __construct(
        private MediaFileRepositoryInterface $files,
        private string $uploadsRoot
    ) {
    }

    public function reconcile(): void
    {
        $diskPaths = [];

        if (is_dir($this->uploadsRoot)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->uploadsRoot, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $extension = strtolower($fileInfo->getExtension());
                $type = $this->resolveType($extension);

                if ($type === null) {
                    continue;
                }

                $relative = 'uploads/' . str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($this->uploadsRoot) + 1));
                $diskPaths[$relative] = $fileInfo;
            }
        }

        $existingPaths = array_flip($this->files->allPaths());

        foreach ($diskPaths as $relative => $fileInfo) {
            if (isset($existingPaths[$relative])) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());

            $this->files->create([
                'path' => $relative,
                'original_name' => $fileInfo->getFilename(),
                'type' => $this->resolveType($extension),
                'mime_type' => @mime_content_type($fileInfo->getPathname()) ?: null,
                'extension' => $extension,
                'size_bytes' => $fileInfo->getSize(),
            ]);
        }

        foreach (array_keys($existingPaths) as $path) {
            if (!isset($diskPaths[$path])) {
                $this->files->deleteByPath($path);
            }
        }
    }

    public function list(array $filters): array
    {
        $items = $this->files->all($filters);

        foreach ($items as &$item) {
            $item['usages'] = $this->formatUsages($this->files->usages($item['path']));
            $item['usage_count'] = count($item['usages']);
        }
        unset($item);

        if (($filters['usage'] ?? '') === 'used') {
            $items = array_values(array_filter($items, fn ($item) => $item['usage_count'] > 0));
        } elseif (($filters['usage'] ?? '') === 'unused') {
            $items = array_values(array_filter($items, fn ($item) => $item['usage_count'] === 0));
        }

        return $items;
    }

    public function find(int|string $id): ?array
    {
        return $this->files->find($id);
    }

    public function usages(string $path): array
    {
        return $this->formatUsages($this->files->usages($path));
    }

    private function formatUsages(array $rawUsages): array
    {
        return array_map(function (array $usage) {
            $usage['label'] = self::SOURCE_LABELS[$usage['source']] ?? $usage['source'];
            $usage['edit_url'] = match ($usage['source']) {
                'question', 'question_option' => base_url("admin/questions/{$usage['id']}/edit"),
                'achievement_message' => base_url("admin/messages/{$usage['id']}/edit"),
                'badge' => base_url("admin/badges/{$usage['id']}/edit"),
                'transition_message' => base_url("admin/transition-messages/{$usage['id']}/edit"),
                'setting' => base_url('admin/settings/start-screen'),
                default => null,
            };

            return $usage;
        }, $rawUsages);
    }

    public function uploadBatch(array $filesInput): array
    {
        $uploaded = [];
        $errors = [];

        $count = is_array($filesInput['name'] ?? null) ? count($filesInput['name']) : 0;

        for ($i = 0; $i < $count; $i++) {
            $error = $filesInput['error'][$i] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $name = $filesInput['name'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "{$name}: yükleme hatası.";
                continue;
            }

            $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
            $type = $this->resolveType($extension);

            if ($type === null) {
                $errors[] = "{$name}: desteklenmeyen dosya formatı.";
                continue;
            }

            $maxBytes = $type === 'image' ? self::IMAGE_MAX_BYTES : self::AUDIO_MAX_BYTES;

            if ($filesInput['size'][$i] > $maxBytes) {
                $errors[] = "{$name}: dosya boyutu çok büyük (maksimum " . round($maxBytes / 1024 / 1024) . " MB).";
                continue;
            }

            $targetDir = $this->uploadsRoot . '/' . self::LIBRARY_DIRECTORY;

            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $errors[] = "{$name}: yükleme klasörü oluşturulamadı.";
                continue;
            }

            $filename = bin2hex(random_bytes(8)) . '.' . $extension;
            $absolutePath = $targetDir . '/' . $filename;

            if (!move_uploaded_file($filesInput['tmp_name'][$i], $absolutePath)) {
                $errors[] = "{$name}: dosya yüklenemedi.";
                continue;
            }

            $relative = 'uploads/' . self::LIBRARY_DIRECTORY . '/' . $filename;

            $this->files->create([
                'path' => $relative,
                'original_name' => $name,
                'type' => $type,
                'mime_type' => @mime_content_type($absolutePath) ?: null,
                'extension' => $extension,
                'size_bytes' => filesize($absolutePath),
            ]);

            $uploaded[] = $relative;
        }

        return ['uploaded' => $uploaded, 'errors' => $errors];
    }

    public function replace(int|string $id, array $file): void
    {
        $media = $this->files->find($id);

        if (!$media) {
            throw new ValidationException('Dosya bulunamadı.');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new ValidationException('Dosya yüklenemedi.');
        }

        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension !== $media['extension']) {
            throw new ValidationException("Değiştirme için aynı uzantıda (.{$media['extension']}) bir dosya seçin.");
        }

        $maxBytes = $media['type'] === 'image' ? self::IMAGE_MAX_BYTES : self::AUDIO_MAX_BYTES;

        if ($file['size'] > $maxBytes) {
            throw new ValidationException('Dosya boyutu çok büyük (maksimum ' . round($maxBytes / 1024 / 1024) . ' MB).');
        }

        $absolutePath = dirname($this->uploadsRoot) . '/' . $media['path'];

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new ValidationException('Dosya yüklenemedi.');
        }

        $this->files->updateByPath($media['path'], [
            'size_bytes' => filesize($absolutePath),
            'mime_type' => @mime_content_type($absolutePath) ?: null,
        ]);
    }

    public function delete(int|string $id): void
    {
        $media = $this->files->find($id);

        if (!$media) {
            return;
        }

        $usages = $this->files->usages($media['path']);

        if (!empty($usages)) {
            $names = implode(', ', array_map(fn ($u) => $u['name'], $usages));
            throw new ValidationException("Bu dosya kullanımda olduğu için silinemez: {$names}");
        }

        $absolutePath = dirname($this->uploadsRoot) . '/' . $media['path'];

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }

        $this->files->delete($id);
    }

    private function resolveType(string $extension): ?string
    {
        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return 'image';
        }

        if (in_array($extension, self::AUDIO_EXTENSIONS, true)) {
            return 'audio';
        }

        return null;
    }
}
