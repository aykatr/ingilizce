# Changelog

Bu proje [Keep a Changelog](https://keepachangelog.com/) formatını takip eder.

## [0.6.0] - 2026-07-28

Faz 5 — Oyun Motoru. Kullanıcının sağladığı referans tasarıma (giriş ekranı + kart/oyun ekranı) sadık kalınarak geliştirildi.

### Eklendi

- `App\Services\GameSessionService` — sunucu taraflı oyun oturumu: soru sırası (tüm aktif sorular, kategoriye bağlı olmaksızın `sort_order`), skor, can, cevap doğrulama (asla istemciye güvenilmez)
- `App\Controllers\GameController` + `/play/api/start|answer|timeout` — session-flag ve CSRF korumalı JSON API
- `assets/js/game-engine.js` — istemci taraflı `GameEngine` sınıfı (ES2023, DOM'suz, saf state/mantık)
- `assets/js/game-ui.js` — DOM render + GSAP animasyon katmanı (timer, can, doğru/yanlış geri bildirimi, +puan animasyonu, önceki/sonraki inceleme modu)
- `assets/css/game.css` — oyun ekranına özel pixel-sadık stil (Bootstrap yalnızca grid için)
- `app/Views/play/index.php` — tek sayfa kabuk: başlangıç / oyun / sonuç ekranları (JS ile geçiş yapılır, sayfa yenilenmez)
- `App\Services\StartScreenService` + `/admin/settings/start-screen` — başlangıç ekranının tüm görselleri (arka plan, logo, maskotlar, robot, roket, balon, dekor) ve metinleri (başlık, açıklama, buton yazısı) admin panelden yönetilir; kod içinde sabit görsel/metin yok, yüklenmemiş görseller zarifçe gizlenir
- `SettingService::getDefaultLives()/updateDefaults()` genişletildi — varsayılan can sayısı `/admin/settings`'ten yönetiliyor
- `SettingRepository::delete()` — bir ayarı "varsayılana dön" anlamında silme (görsel kaldırma / boş metin senaryoları için)

### Değişti

- `play.php` artık `Session::start()` çağırıyor ve doğrulama sonrası `play_authorized` session flag'i set ediyor (oyun API'lerinin yetki kontrolü için)

### Test

Oyun oturumu başlatma, doğru/yanlış cevap akışı (tekrar deneme dahil), zaman aşımı, can sistemi (game over dahil), soru bazlı puan/süre override, güvenlik guard'ları (403/419/422) ve admin başlangıç ekranı ayarları `ingilizce.test` üzerinde gerçek API çağrılarıyla uçtan uca test edildi. Görsel pixel-doğrulama için tarayıcı ekran görüntüsü aracı mevcut değildi — kod, sağlanan referans görsellerin analizinden üretildi.

## [0.5.0] - 2026-07-28

Faz 4 — Soru Modülü (eski Faz 5 "Seçenek Sistemi" kapsamı dahil edildi, roadmap buna göre yeniden numaralandı).

### Eklendi

- `categories`, `questions`, `question_options` tabloları (migration)
- `App\Services\CategoryService` — kategori CRUD; bağlı soru varsa silme engellenir (DB'de FK `RESTRICT` ile de korunuyor)
- `App\Services\QuestionService` — soru + tam olarak 4 seçenek (A/B/C/D) + medyayı tek işlemde yönetir; tek doğru cevap zorunluluğu ve zorunlu alan validasyonu
- `App\Services\MediaUploadService` — görsel (WebP/PNG/JPG, ≤5MB) ve ses (MP3/OGG, ≤10MB) yükleme, değiştirme (eskiyi otomatik silme), kaldırma
- `uploads/` dizini (proje kökü, web'den erişilebilir, `.htaccess` ile script çalıştırma engelli) — `questions/{id}/` ve `options/{id}/` alt klasörleri
- Süre/puan için "genel varsayılan → Site Ayarları, istisna → soru bazında" deseni: `SettingService::getDefaultDuration()/getDefaultPoints()/updateDefaults()`, `/admin/settings` formuna eklendi
- Admin panel: `/admin/categories` (index/create/edit/delete), `/admin/questions` (index/create/edit/delete) — tek sayfa sekmeli soru formu (Genel Bilgiler/Kart/Soru/Seçenekler/Önizleme)
- Admin nav'a "Kategoriler" ve "Soru Modülleri" bağlantıları

### Roadmap

- Eski "Faz 5 — Seçenek Sistemi" içeriği Faz 4'e taşındı; sonraki tüm fazlar bir numara geriye kaydı (eski Faz 6 → yeni Faz 5, ... eski Faz 16 → yeni Faz 15)

## [0.4.0] - 2026-07-28

Proje adı "Yippee Learning Platform" olarak netleşti (tam ürün spesifikasyonu iletildi); Faz 3 kapsamı bu spesifikasyona göre genişletildi ve Faz 1-3 kodu yeni mimariye göre revize edildi.

### Değişti — Mimari

- Repository Pattern + Service Layer eklendi: `App\Repositories\Contracts\*Interface`, `App\Repositories\{Admin,License,Setting}Repository`, `App\Services\{Auth,License,Setting}Service`
- İş kuralları controller'lardan Service katmanına taşındı; kural ihlalleri `App\Services\Exceptions\ValidationException` ile controller'a bildirilir
- `AuthController`, `PasswordController`, `LicenseController` artık ilgili Service üzerinden çalışıyor

### Değişti — Frontend

- Tüm admin ve public view'lar Bootstrap 5'e (CDN) geçirildi; özel CSS minimuma indirildi
- Lisans listesinde QRCode.js ile istemci taraflı QR modalı eklendi (`assets/js/license-qr.js`, vanilla JS, jQuery yok)

### Eklendi — Lisans Sistemi Genişletmesi

- `licenses` tablosuna `code` (insan-okunur lisans kodu), `expires_at`, `first_activated_at`, `last_used_at`, `last_device`, `last_ip` alanları eklendi
- Lisans durumu (Aktif/Pasif/Süresi Doldu) `LicenseService::statusLabel()` ile hesaplanıyor
- `play.php` her doğrulamada aktivasyon/son kullanım/son cihaz/son IP bilgisini güncelliyor (`LicenseService::validateAndTrack()`)
- `settings` tablosu ve `SettingService` — admin panelden değiştirilebilir site URL (`/admin/settings`), QR/oynama linki bu adresi kullanıyor
- `play.php` token parametresi `token`'dan `t`'ye değiştirildi (spec ile uyum: `play.php?t=TOKEN`)
- `Str::code()` helper — gruplu, karışıklık yaratmayan karakterlerden insan-okunur kod üretimi

### Roadmap

- Kullanıcının ilettiği "Ek Özellikler" listesi için Faz 11-16 eklendi (çoklu dil, kart paketleri, ilerleme/devam, PWA, analytics, medya optimizasyonu)

## [0.3.0] - 2026-07-28

### Eklendi

- `licenses` tablosu migration'ı ve `App\Models\License`
- `App\Controllers\Admin\LicenseController` — lisans oluşturma, listeleme, aktif/pasif toggle
- Kök dizinde bağımsız `play.php` giriş noktası — token doğrulama, geçersiz/pasif lisans için 403
- Lisans oluşturulunca token + oynama linki (`play.php?token=...`) üretimi ve admin panelinde gösterimi
- Admin panel navigasyonuna "Lisanslar" bağlantısı, tablo/sayfa başlığı stilleri

### Not

- QR kod görseli üretimi kapsam dışı bırakıldı — sistem yalnızca token + link üretir, QR görseli kullanıcı tarafından harici bir araçla üretilecek.

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
