# CLAUDE.md

Bu dosya, bu depoda çalışırken Claude Code'a rehberlik eder.

## Proje

YIPPEE Learning Platform — fiziksel eğitim kartlarının üzerindeki QR kod ile çalışan çocuk eğitim platformu. Kullanıcı QR'ı okutur → `play.php?t=TOKEN` → lisans doğrulanır → soru modülü yüklenir. Tam ürün spesifikasyonu için kullanıcının ilettiği "YIPPEE LEARNING PLATFORM" dokümanına bakın (bu dosyadaki mimari kararlar o spesifikasyondan türetilmiştir).

## Geliştirme Kuralı — Faz Disiplini

Bu proje `ROADMAP.md` içinde tanımlı fazlar halinde geliştirilir. **Bir faz tamamen bitmeden, kod kontrol edilip hatalar düzeltilmeden, gerekli migrationlar hazırlanmadan, test edilmeden, README ve CHANGELOG güncellenmeden ve kullanıcı onayı alınmadan bir sonraki faza geçilmez.** Aksi açıkça istenmedikçe bu kurala uyulmalıdır. Güncel faz durumu için `ROADMAP.md` dosyasına bakın.

## Teknoloji

- Backend: PHP 8.3, MySQL 8, custom MVC (framework yok, Composer sadece PSR-4 autoload için)
- Frontend: Bootstrap 5 (CDN), HTML5, CSS3, Vanilla JavaScript (ES2023) — **jQuery kullanılmaz**
- Kütüphaneler (CDN üzerinden, build adımı yok): QRCode.js (admin panelde QR görüntüleme). Howler.js, GSAP, Chart.js henüz entegre edilmedi — sırasıyla oyun motoru (Faz 6-7) ve dashboard istatistikleri gerçek ihtiyaç doğduğunda eklenecek, önden eklenmedi (YAGNI).

## Mimari — Katmanlar

MVC + Repository Pattern + Service Layer. PSR-12, SOLID, DRY hedeflenir.

```
Controller → Service → Repository (interface) → Model (table gateway) → Database (PDO)
```

- **Model** (`app/Models/*`): `BaseModel`'den türer, ham PDO CRUD sağlayan tablo gateway'i (`all/find/where/create/update/delete`). Settings gibi `id`+auto-increment şemasına uymayan tablolarda Model kullanılmaz, Repository doğrudan `Database::connection()` ile PDO'ya konuşur.
- **Repository** (`app/Repositories/*`, arayüzler `app/Repositories/Contracts/*`): Domain'e özel sorgu/yazma metodlarını tanımlar (ör. `LicenseRepositoryInterface::findByToken()`), Model çağrılarını sarar. Service'ler somut Repository değil, **interface**'e bağımlı olur (DIP).
- **Service** (`app/Services/*`): İş kuralları burada yaşar (şifre doğrulama, token/kod üretimi, lisans durum hesaplama, aktivasyon takibi). Kural ihlalinde `App\Services\Exceptions\ValidationException` fırlatır; controller bunu yakalayıp `Session::flash('error', $e->getMessage())` yapar. Yeni bir domain kuralı eklerken controller'a değil, ilgili Service'e yazın.
- **Controller** (`app/Controllers/*`): Yalnızca HTTP orkestrasyonu — request okuma, CSRF doğrulama, Service çağırma, redirect/view. İş kuralı içermez.

Mevcut örnek: `AuthService` (login/logout/şifre değiştirme), `LicenseService` (lisans oluşturma/durum/aktivasyon takibi), `SettingService` (site URL gibi tekil ayarlar).

## Diğer Çekirdek Kurallar

- **Front controller**: Tüm istekler kök dizindeki `index.php`'ye düşer (`.htaccess` rewrite ile). `public/` klasörü yok — `DocumentRoot` proje kökünü gösteriyor, bu yüzden `app/`, `config/`, `routes/`, `storage/`, `database/` klasörleri kendi `.htaccess` dosyalarıyla ve kök `.htaccess`'teki `RewriteRule ... [F,L]` kuralıyla web erişimine kapatılmış durumda. Yeni bir "gizli" klasör eklerken bu korumayı unutmayın.
- **Routing**: `routes/web.php` bir `App\Core\Router` örneği döndürür. `$router->get()/post()` ile route tanımlanır, `{param}` segmentleri controller metoduna pozisyonel argüman olarak geçer.
- **View**: `App\Core\View::render()` nokta notasyonu kullanır (`admin.login` → `app/Views/admin/login.php`). Varsayılan layout `layouts/main.php` (public sayfalar) veya `layouts/admin.php` (admin panel, Bootstrap navbar içerir); `render($view, $data, null)` ile layout'suz render edilebilir (`play.php`'nin kendi tam HTML döküman view'ları gibi).
- **Config/Env**: `.env` dosyası `App\Core\Env` ile okunur, `config/*.php` dosyaları `App\Core\Config` ile yüklenir. Kod içinde `env()` değil, `config()` helper'ı tercih edilmeli (env sadece config dosyaları içinde kullanılır). Admin panelden değiştirilebilen değerler (ör. site URL) `config()`'te değil, `settings` tablosunda (`SettingService`) tutulur.
- **Global helper fonksiyonları** `app/Core/helpers.php` içinde tanımlı ve composer'ın `autoload.files` ile otomatik yüklenir: `config()`, `env()`, `base_url()`, `asset()`, `e()` (XSS-safe escape), `csrf_field()`, `dd()`.
- **Session/Auth**: `App\Core\Session` genel session işlemlerini (get/put/flash/csrf) sağlar; `App\Core\Auth` bunun üzerine admin kimlik doğrulama semantiğini (`login()/logout()/check()/user()`) katar. `index.php` `Session::start()` çağırır — ham `session_start()` kullanmayın.
- **Admin route koruması**: Giriş gerektiren admin controller'ları `App\Controllers\Admin\AdminBaseController`'dan türetilir; constructor `Auth::check()` değilse `/admin/login`'e yönlendirir ve `$this->admin` dizisini doldurur. Herkese açık admin route'ları (login/logout) düz `BaseController`'dan türetilir.
- **CSRF**: State değiştiren tüm POST formlarında `<?= csrf_field() ?>` kullanılmalı, controller tarafında `Session::verifyCsrf($request->input('_csrf'))` ile doğrulanmalı.
- **Migration**: `database/migrations/*.php` her biri `App\Core\Migration`'ı extend eden anonim bir sınıf döndürür (`up(PDO)/down(PDO)`). Dosya adı `YYYY_MM_DD_HHMMSS_aciklama.php` formatında olmalı (sıralama buna göre yapılır). Şema değişikliği gerektiğinde **mevcut migration dosyasını değiştirmeyin** — zaten çalıştırılmış olabilir; yeni bir `ALTER TABLE` migration'ı ekleyin (örnek: `2026_07_28_120000_add_details_to_licenses_table.php`). Çalıştırmak için `php database/migrate.php migrate`, geri almak için `php database/migrate.php rollback` (son batch'i geri alır).
- **play.php**: Roadmap'te ayrı bir dosya olarak belirtildiği için kök dizinde, router'dan bağımsız bir giriş noktası olarak tutuluyor (`index.php` ile aynı bootstrap deseni: autoload + `Env::load` + `Config::load`). Query parametresi `t` (spec'te böyle: `play.php?t=TOKEN`) — `token` değil. Doğrulama/takip mantığı `LicenseService::validateAndTrack()` içinde; `ValidationException` mesajı doğrudan kullanıcıya gösterilir. Gelecekte oyun motoru mantığı büyürse bile bu dosyayı router'a taşımayın — kasıtlı bir mimari karar.
- **Lisans/QR**: Sistem token + lisans kodu (`code`, insan-okunur, `Str::code()`) üretir. QR görseli **istemci tarafında** QRCode.js ile admin panelde (`admin/licenses/index.php` → QR modal, `assets/js/license-qr.js`) üretilir — PHP tarafında QR kütüphanesi yok. Oynama linki `{site_url}/play.php?t={token}` şeklinde, `site_url` admin panelden değiştirilebilir (`SettingService::getSiteUrl()`, `.env`'teki `APP_URL`'e fallback yapar). Lisans durumu (`Aktif/Pasif/Süresi Doldu`) DB'de ayrı bir alan olarak tutulmaz, `LicenseService::statusLabel()` ile `is_active` + `expires_at`'ten hesaplanır (stale veri riskini önler). Her `play.php` doğrulamasında `first_activated_at` (ilk kez), `last_used_at`, `last_ip`, `last_device` güncellenir.

## Frontend

- Bootstrap 5 CSS/JS bundle her iki layout'ta (`layouts/main.php`, `layouts/admin.php`) CDN üzerinden yüklü — build adımı, npm, bundler yok.
- Vanilla JS dosyaları `assets/js/` altında, sayfaya özel `<script>` etiketiyle ilgili view'da dahil edilir (global bir bundle yok). Örnek: `assets/js/license-qr.js`.
- jQuery kesinlikle kullanılmaz — DOM işlemleri için native API (`document.querySelector`, `addEventListener`, `dataset`, vb.).
- `assets/css/app.css` artık yalnızca Bootstrap'in kapsamadığı minik override'lar için var; layout/komponent stilini Bootstrap sınıflarıyla kurun, özel CSS yazmadan önce bir Bootstrap sınıfının işi görüp görmediğini kontrol edin.

## Güvenlik

- View'larda kullanıcı girdisi veya veritabanından gelen veri basılırken her zaman `e()` helper'ı ile escape edin.
- SQL sorgularında her zaman prepared statement / named parameter kullanın (`BaseModel` ve repository'ler zaten bunu sağlıyor).
- `storage/`, `app/`, `config/`, `routes/`, `database/` klasörleri web'den erişilemez olmalı — yeni bir alt klasör eklerken kök `.htaccess`'teki `RewriteRule` listesine eklemeyi unutmayın.

## Test

Yerleşik bir test suite henüz yok (ilerleyen fazlarda eklenebilir). Değişiklik sonrası manuel doğrulama için:

```bash
composer install
php database/migrate.php migrate
```

ve `http://ingilizce.test/` ile `http://ingilizce.test/health` adreslerini kontrol edin (health endpoint DB bağlantısını da doğrular). Admin panel akışlarını (giriş, lisans oluşturma, `play.php?t=...`) gerçek `ingilizce.test` vhost'u üzerinden cookie jar ile uçtan uca test edin — PHP built-in server (`php -S`) `.htaccess` işlemediği için statik dosya/engelleme davranışını yanlış yansıtır.

## Dil

Kullanıcıya dönük tüm metinler (view, hata mesajları, admin paneli) Türkçe yazılmalıdır. Kod (değişken/fonksiyon/sınıf isimleri, commit mesajları, yorumlar) İngilizce kalabilir; proje genelinde tutarlı olmak önemli.
