<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\CategoryRepository;
use App\Repositories\QuestionOptionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SettingRepository;
use App\Services\CategoryService;
use App\Services\Exceptions\ValidationException;
use App\Services\MediaUploadService;
use App\Services\QuestionService;
use App\Services\SettingService;

class QuestionController extends AdminBaseController
{
    private QuestionService $questionService;
    private CategoryService $categoryService;
    private SettingService $settingService;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->questionService = new QuestionService(new QuestionRepository(), new QuestionOptionRepository(), $media);
        $this->categoryService = new CategoryService(new CategoryRepository());
        $this->settingService = new SettingService(new SettingRepository());
    }

    public function index(): void
    {
        $this->view('admin.questions.index', [
            'title' => 'Soru Modülleri',
            'questions' => $this->questionService->list(),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.questions.create', [
            'title' => 'Yeni Soru',
            'categories' => $this->categoryService->list(),
            'defaultDuration' => $this->settingService->getDefaultDuration(),
            'defaultPoints' => $this->settingService->getDefaultPoints(),
            'error' => Session::getFlash('error'),
            'isEdit' => false,
            'question' => null,
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/questions/create'));
        }

        try {
            $this->questionService->create($request->all(), $_FILES);
            Session::flash('success', 'Soru oluşturuldu.');
            $this->redirect(base_url('admin/questions'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/questions/create'));
        }
    }

    public function edit(int|string $id): void
    {
        $question = $this->questionService->find($id);

        if (!$question) {
            $this->redirect(base_url('admin/questions'));
        }

        $this->view('admin.questions.edit', [
            'title' => 'Soru Düzenle',
            'categories' => $this->categoryService->list(),
            'defaultDuration' => $this->settingService->getDefaultDuration(),
            'defaultPoints' => $this->settingService->getDefaultPoints(),
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'isEdit' => true,
            'question' => $question,
        ], 'admin');
    }

    public function update(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url("admin/questions/{$id}/edit"));
        }

        try {
            $this->questionService->update($id, $request->all(), $_FILES);
            Session::flash('success', 'Soru güncellendi.');
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect(base_url("admin/questions/{$id}/edit"));
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $this->questionService->delete($id);
            Session::flash('success', 'Soru silindi.');
        }

        $this->redirect(base_url('admin/questions'));
    }
}
