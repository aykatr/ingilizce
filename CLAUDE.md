# CLAUDE.md

Bu dosya, bu depoda çalışırken Claude Code'a rehberlik eder.

## Geliştirme Kuralı — Faz Disiplini

Bu proje `ROADMAP.md` içinde tanımlı fazlar halinde geliştirilir. **Bir faz tamamen bitmeden, kod kontrol edilip hatalar düzeltilmeden, gerekli migrationlar hazırlanmadan, test edilmeden, README ve CHANGELOG güncellenmeden ve kullanıcı onayı alınmadan bir sonraki faza geçilmez.** Aksi açıkça istenmedikçe bu kurala uyulmalıdır. Güncel faz durumu için `ROADMAP.md` dosyasına bakın.

## Mimari

Custom, hafif bir MVC iskeleti — Laravel/Symfony gibi bir framework kullanılmıyor. Composer yalnızca PSR-4 autoload için kullanılıyor.

- **Front controller**: Tüm istekler kök dizindeki `index.php`'ye düşer (`.htaccess` rewrite ile). `public/` klasörü yok — `DocumentRoot` proje kökünü gösteriyor, bu yüzden `app/`, `config/`, `routes/`, `storage/`, `database/` klasörleri kendi `.htaccess` dosyalarıyla ve kök `.htaccess`'teki `RewriteRule ... [F,L]` kuralıyla web erişimine kapatılmış durumda. Yeni bir "gizli" klasör eklerken bu korumayı unutmayın.
- **Routing**: `routes/web.php` bir `App\Core\Router` örneği döndürür. `$router->get()/post()` ile route tanımlanır, `{param}` segmentleri controller metoduna pozisyonel argüman olarak geçer.
- **Controller**: `App\Controllers\BaseController`'dan türetilir. `$this->view()`, `$this->redirect()`, `$this->json()` yardımcılarını kullanır.
- **Model**: `App\Models\BaseModel`'den türetilir. `protected static string $table` tanımlayarak `all()/find()/where()/create()/update()/delete()` PDO yardımcı metodlarını miras alır. Tüm sorgular prepared statement kullanır — asla ham string birleştirme ile SQL yazmayın.
- **View**: `App\Core\View::render()` nokta notasyonu kullanır (`admin.login` → `app/Views/admin/login.php`). Varsayılan layout `layouts/main.php`; `render($view, $data, null)` ile layout'suz render edilebilir.
- **Config/Env**: `.env` dosyası `App\Core\Env` ile okunur, `config/*.php` dosyaları `App\Core\Config` ile yüklenir. Kod içinde `env()` değil, `config()` helper'ı tercih edilmeli (env sadece config dosyaları içinde kullanılır).
- **Global helper fonksiyonları** `app/Core/helpers.php` içinde tanımlı ve composer'ın `autoload.files` ile otomatik yüklenir: `config()`, `env()`, `base_url()`, `asset()`, `e()` (XSS-safe escape), `csrf_field()`, `dd()`.
- **Session/Auth**: `App\Core\Session` genel session işlemlerini (get/put/flash/csrf) sağlar; `App\Core\Auth` bunun üzerine admin kimlik doğrulama semantiğini (`login()/logout()/check()/user()`) katar. `index.php` `Session::start()` çağırır — ham `session_start()` kullanmayın.
- **Admin route koruması**: Giriş gerektiren admin controller'ları `App\Controllers\Admin\AdminBaseController`'dan türetilir; constructor `Auth::check()` değilse `/admin/login`'e yönlendirir ve `$this->admin` dizisini doldurur. Herkese açık admin route'ları (login/logout) düz `BaseController`'dan türetilir.
- **CSRF**: State değiştiren tüm POST formlarında `<?= csrf_field() ?>` kullanılmalı, controller tarafında `Session::verifyCsrf($request->input('_csrf'))` ile doğrulanmalı. Bir view'da birden fazla form varsa (ör. admin layout'taki çıkış formu + sayfa formu) her ikisi de aynı token'ı üretir — tek bir token yeterlidir.
- **Migration**: `database/migrations/*.php` her biri `App\Core\Migration`'ı extend eden anonim bir sınıf döndürür (`up(PDO)/down(PDO)`). Dosya adı `YYYY_MM_DD_HHMMSS_aciklama.php` formatında olmalı (sıralama buna göre yapılır). Çalıştırmak için `php database/migrate.php migrate`, geri almak için `php database/migrate.php rollback` (son batch'i geri alır).

## Güvenlik

- View'larda kullanıcı girdisi veya veritabanından gelen veri basılırken her zaman `e()` helper'ı ile escape edin.
- SQL sorgularında her zaman prepared statement / named parameter kullanın (`BaseModel` zaten bunu sağlıyor).
- `storage/`, `app/`, `config/`, `routes/`, `database/` klasörleri web'den erişilemez olmalı — yeni bir alt klasör eklerken kök `.htaccess`'teki `RewriteRule` listesine eklemeyi unutmayın.

## Test

Yerleşik bir test suite henüz yok (ilerleyen fazlarda eklenebilir). Değişiklik sonrası manuel doğrulama için:

```bash
composer install
```

ve `http://ingilizce.test/` ile `http://ingilizce.test/health` adreslerini kontrol edin (health endpoint DB bağlantısını da doğrular).

## Dil

Kullanıcıya dönük tüm metinler (view, hata mesajları, admin paneli) Türkçe yazılmalıdır. Kod (değişken/fonksiyon/sınıf isimleri, commit mesajları, yorumlar) İngilizce kalabilir; proje genelinde tutarlı olmak önemli.
