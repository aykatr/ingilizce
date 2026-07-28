<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\MediaFile;
use App\Repositories\Contracts\MediaFileRepositoryInterface;
use PDO;

class MediaFileRepository implements MediaFileRepositoryInterface
{
    public function all(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['type'])) {
            $conditions[] = 'type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(original_name LIKE ? OR path LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = Database::connection()->prepare("SELECT * FROM media_files {$where} ORDER BY created_at DESC");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function allPaths(): array
    {
        return Database::connection()->query('SELECT path FROM media_files')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function find(int|string $id): ?array
    {
        return MediaFile::find($id);
    }

    public function findByPath(string $path): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM media_files WHERE path = ? LIMIT 1');
        $stmt->execute([$path]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): string
    {
        return MediaFile::create($data);
    }

    public function updateByPath(string $path, array $data): bool
    {
        $set = implode(', ', array_map(fn ($col) => "`{$col}` = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $path;

        $stmt = Database::connection()->prepare("UPDATE media_files SET {$set} WHERE path = ?");

        return $stmt->execute($params);
    }

    public function delete(int|string $id): bool
    {
        return MediaFile::delete($id);
    }

    public function deleteByPath(string $path): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM media_files WHERE path = ?');

        return $stmt->execute([$path]);
    }

    public function usages(string $path): array
    {
        $db = Database::connection();
        $usages = [];

        $stmt = $db->prepare(
            "SELECT id, title,
                CASE WHEN card_image = ? THEN 'card_image' WHEN card_audio = ? THEN 'card_audio' ELSE 'question_audio' END AS field
             FROM questions WHERE card_image = ? OR card_audio = ? OR question_audio = ?"
        );
        $stmt->execute([$path, $path, $path, $path, $path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['source' => 'question', 'field' => $row['field'], 'id' => $row['id'], 'name' => $row['title']];
        }

        $stmt = $db->prepare(
            "SELECT qo.id, qo.question_id, qo.position, q.title,
                CASE WHEN qo.image = ? THEN 'image' ELSE 'audio' END AS field
             FROM question_options qo
             JOIN questions q ON q.id = qo.question_id
             WHERE qo.image = ? OR qo.audio = ?"
        );
        $stmt->execute([$path, $path, $path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = [
                'source' => 'question_option',
                'field' => $row['field'],
                'id' => $row['question_id'],
                'name' => $row['title'] . ' — Seçenek ' . $row['position'],
            ];
        }

        $stmt = $db->prepare('SELECT id, title FROM achievement_messages WHERE audio = ?');
        $stmt->execute([$path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['source' => 'achievement_message', 'field' => 'audio', 'id' => $row['id'], 'name' => $row['title']];
        }

        $stmt = $db->prepare(
            "SELECT id, title, CASE WHEN image = ? THEN 'image' ELSE 'audio' END AS field
             FROM badges WHERE image = ? OR audio = ?"
        );
        $stmt->execute([$path, $path, $path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['source' => 'badge', 'field' => $row['field'], 'id' => $row['id'], 'name' => $row['title']];
        }

        $stmt = $db->prepare('SELECT id, title FROM transition_messages WHERE audio = ?');
        $stmt->execute([$path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['source' => 'transition_message', 'field' => 'audio', 'id' => $row['id'], 'name' => $row['title']];
        }

        $stmt = $db->prepare('SELECT `key` FROM settings WHERE `value` = ?');
        $stmt->execute([$path]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['source' => 'setting', 'field' => $row['key'], 'id' => null, 'name' => 'Başlangıç Ekranı'];
        }

        return $usages;
    }
}
