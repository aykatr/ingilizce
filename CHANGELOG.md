# Changelog

Bu proje [Keep a Changelog](https://keepachangelog.com/) formatını takip eder.

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
