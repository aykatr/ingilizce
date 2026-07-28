# Changelog

Bu proje [Keep a Changelog](https://keepachangelog.com/) formatını takip eder.

## [0.2.0] - 2026-07-28

### Eklendi

- Hafif migration sistemi: `App\Core\Migration`, `database/migrate.php` (`migrate`/`rollback`, batch takibi)
- `admins` tablosu migration'ı ve varsayılan admin hesabı için `database/seed.php`
- `App\Core\Session` (generic session/flash/CSRF) ve `App\Core\Auth` (admin login/logout/check/user)
- Admin giriş (`AuthController`), şifre değiştirme (`PasswordController`), dashboard (`DashboardController`)
- `App\Controllers\Admin\AdminBaseController` ile route bazlı yetkilendirme guard'ı
- CSRF korumalı formlar (`csrf_field()` helper, `Session::verifyCsrf()`)
- Admin panel layout'u ve stilleri (`layouts/admin.php`, nav, alert mesajları)

## [0.1.0] - 2026-07-28

### Eklendi

- Proje iskeleti: özel, hafif MVC mimarisi (Composer PSR-4 autoload, framework yok)
- `App\Core\Router` — closure ve `[Controller::class, 'method']` destekli routing
- `App\Core\Request`, `App\Core\View`, `App\Core\Config`, `App\Core\Env`, `App\Core\Database` (PDO/MySQL)
- `App\Controllers\BaseController`, `App\Models\BaseModel` (prepared statement tabanlı CRUD)
- `App\Helpers\Str` ve global helper fonksiyonları (`config()`, `env()`, `base_url()`, `asset()`, `e()`, `dd()`)
- Admin giriş sayfası iskeleti (`GET /admin/login`)
- Ana sayfa iskeleti (`GET /`) ve sağlık kontrolü uç noktası (`GET /health`)
- `ingilizce` MySQL veritabanı oluşturuldu
- Kök ve alt klasörlerde `.htaccess` ile `app/`, `config/`, `routes/`, `storage/`, `database/` klasörlerinin web erişimine kapatılması
- README, CLAUDE.md, ROADMAP.md
