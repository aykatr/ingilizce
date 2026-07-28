<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\MediaFileRepository;
use App\Services\AuditLogService;
use App\Services\Exceptions\ValidationException;
use App\Services\MediaLibraryService;

class MediaLibraryController extends AdminBaseController
{
    private MediaLibraryService $mediaLibrary;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();

        $this->mediaLibrary = new MediaLibraryService(new MediaFileRepository(), config('app.uploads_path'));
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function index(): void
    {
        $request = new Request();

        $filters = [
            'type' => trim((string) $request->input('type', '')),
            'q' => trim((string) $request->input('q', '')),
            'usage' => trim((string) $request->input('usage', '')),
        ];

        $this->mediaLibrary->reconcile();

        $this->view('admin.media_library.index', [
            'title' => 'Medya Kütüphanesi',
            'files' => $this->mediaLibrary->list($filters),
            'filters' => $filters,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function apiList(): void
    {
        $request = new Request();

        $filters = [
            'type' => trim((string) $request->input('type', '')),
            'q' => trim((string) $request->input('q', '')),
        ];

        $this->mediaLibrary->reconcile();

        $files = array_map(function (array $file) {
            return [
                'id' => $file['id'],
                'path' => $file['path'],
                'url' => base_url($file['path']),
                'original_name' => $file['original_name'],
                'type' => $file['type'],
                'extension' => $file['extension'],
                'size_bytes' => $file['size_bytes'],
            ];
        }, $this->mediaLibrary->list($filters));

        $this->json($files);
    }

    public function upload(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/media-library'));
        }

        $result = $this->mediaLibrary->uploadBatch($_FILES['files'] ?? []);
        $count = count($result['uploaded']);

        if ($count > 0) {
            $this->auditLog->record('media.upload', "{$count} dosya medya kütüphanesine yüklendi.");
            Session::flash('success', "{$count} dosya yüklendi.");
        }

        if (!empty($result['errors'])) {
            Session::flash('error', implode(' ', $result['errors']));
        }

        $this->redirect(base_url('admin/media-library'));
    }

    public function replace(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/media-library'));
        }

        try {
            $this->mediaLibrary->replace($id, $_FILES['file'] ?? []);
            $this->auditLog->record('media.replace', "Medya dosyası değiştirildi (#{$id}).");
            Session::flash('success', 'Dosya değiştirildi.');
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect(base_url('admin/media-library'));
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            try {
                $this->mediaLibrary->delete($id);
                $this->auditLog->record('media.delete', "Medya dosyası silindi (#{$id}).");
                Session::flash('success', 'Dosya silindi.');
            } catch (ValidationException $e) {
                Session::flash('error', $e->getMessage());
            }
        }

        $this->redirect(base_url('admin/media-library'));
    }
}
