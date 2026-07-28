# Yippee Learning Platform

Fiziksel eğitim kartlarının üzerindeki QR kod ile çalışan çocuk eğitim platformu (İngilizce kart oyunu) ve yönetim paneli.

## Gereksinimler

- PHP 8.3+
- MySQL 8.x
- Composer
- Apache (`mod_rewrite`, `AllowOverride All`)

Frontend bağımlılıkları (Bootstrap 5, QRCode.js) CDN üzerinden yüklenir — npm/build adımı yoktur.

## Kurulum

```bash
composer install
copy .env.example .env
```

`.env` dosyasındaki veritabanı bilgilerini kendi ortamınıza göre düzenleyin, ardından veritabanını oluşturun:

```sql
CREATE DATABASE ingilizce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Migration'ları çalıştırın ve varsayılan admin hesabını oluşturun:

```bash
php database/migrate.php migrate
php database/seed.php
```

`ADMIN_PASSWORD` `.env` içinde boş bırakılırsa `database/seed.php` rastgele bir şifre üretir ve yalnızca bir kez terminale yazdırır — not alın. Migration'ı geri almak için `php database/migrate.php rollback` kullanılabilir (son batch'i geri alır).

Proje kökü doğrudan Apache `DocumentRoot` olarak ayarlanmalıdır (örn. `ingilizce.test`). Tüm istekler `.htaccess` üzerinden `index.php` front controller'ına yönlendirilir.

## Mimari

MVC + Repository Pattern + Service Layer:

```
Controller → Service → Repository (interface) → Model (table gateway) → Database (PDO)
```

Detaylar için [CLAUDE.md](CLAUDE.md).

## Klasör Yapısı

```
app/
  Controllers/    Controller sınıfları (BaseController üzerinden türetilir) — yalnızca HTTP orkestrasyonu
  Services/       İş kuralları (AuthService, LicenseService, SettingService, CategoryService, QuestionService, MediaUploadService)
  Repositories/   Domain'e özel veri erişimi; arayüzler Repositories/Contracts altında
  Models/         Tablo gateway'leri (BaseModel üzerinden türetilir)
  Core/           Framework çekirdeği: Router, Request, View, Config, Env, Database, Session, Auth, Migration
  Helpers/        Yardımcı sınıflar (Str vb.)
  Views/          PHP view dosyaları (nokta notasyonu: home.index -> Views/home/index.php)
config/           Ortam bağımsız yapılandırma dosyaları (app.php, database.php)
routes/           Route tanımları (web.php)
database/
  migrations/     Veritabanı migration dosyaları
  migrate.php     Migration runner (migrate|rollback)
  seed.php        Varsayılan admin hesabını oluşturan seed script
storage/          Loglar, cache (web'den erişilemez)
uploads/          Soru/seçenek medyası (görsel/ses) — web'den erişilebilir, script çalıştırma engelli
assets/           CSS, JS, görseller (doğrudan web'den erişilebilir)
```

## Admin Paneli

| Route | Açıklama |
|---|---|
| `GET /admin/login` | Giriş formu |
| `POST /admin/login` | Giriş işlemi |
| `POST /admin/logout` | Çıkış işlemi |
| `GET /admin/dashboard` | Panel (giriş gerektirir) |
| `GET/POST /admin/password` | Şifre değiştirme (giriş gerektirir) |
| `GET /admin/licenses` | Lisans listesi (giriş gerektirir) |
| `GET /admin/licenses/create` | Yeni lisans formu (giriş gerektirir) |
| `POST /admin/licenses` | Lisans oluştur (giriş gerektirir) |
| `POST /admin/licenses/{id}/toggle` | Lisansı aktif/pasif yap (giriş gerektirir) |
| `GET/POST /admin/settings` | Site URL + varsayılan süre/puan ayarı (giriş gerektirir) |
| `GET /admin/categories` | Kategori listesi (giriş gerektirir) |
| `GET /admin/categories/create` | Yeni kategori formu (giriş gerektirir) |
| `POST /admin/categories` | Kategori oluştur (giriş gerektirir) |
| `GET /admin/categories/{id}/edit` | Kategori düzenleme formu (giriş gerektirir) |
| `POST /admin/categories/{id}` | Kategori güncelle (giriş gerektirir) |
| `POST /admin/categories/{id}/delete` | Kategori sil — bağlı soru varsa engellenir (giriş gerektirir) |
| `GET /admin/questions` | Soru listesi (giriş gerektirir) |
| `GET /admin/questions/create` | Yeni soru formu — sekmeli (giriş gerektirir) |
| `POST /admin/questions` | Soru + 4 seçenek + medya oluştur (giriş gerektirir) |
| `GET /admin/questions/{id}/edit` | Soru düzenleme formu — sekmeli, önizleme dahil (giriş gerektirir) |
| `POST /admin/questions/{id}` | Soru güncelle (giriş gerektirir) |
| `POST /admin/questions/{id}/delete` | Soru sil — medya dosyaları dahil temizlenir (giriş gerektirir) |

Giriş gerektiren rotalar `App\Controllers\Admin\AdminBaseController` üzerinden korunur; oturumu olmayan istekler `/admin/login`'e yönlendirilir.

## Lisans Sistemi

Bir lisans oluşturulduğunda rastgele bir `token` (URL için, 32 karakter) ve insan-okunur bir `code` (ör. `K3F9-8H2M-QW7X`, referans/yazdırma için) üretilir. Oynama linki `{site_url}/play.php?t={token}` şeklindedir; `site_url` admin panelinden (`/admin/settings`) değiştirilebilir. QR kod görseli **istemci tarafında** QRCode.js ile admin panelde (lisans listesindeki "QR" butonu → modal) üretilir; PHP tarafında QR kütüphanesi kullanılmaz.

`play.php` kök dizinde bağımsız bir giriş noktasıdır (MVC router'dan geçmez, `index.php` ile aynı şekilde bootstrap olur, parametre adı `t`). Token'ı doğrular; lisans yoksa, pasifse veya süresi dolmuşsa 403 ile nedeni açıklayan bir sayfa gösterir, geçerliyse oyun sayfasını render eder (oyun motoru Faz 5'te eklenecek) ve aktivasyon/son kullanım/son cihaz/son IP bilgilerini günceller. Lisans durumu (Aktif / Pasif / Süresi Doldu) admin panelde `is_active` ve opsiyonel `expires_at` alanlarından hesaplanarak gösterilir. Tüm aktif lisanslar tüm içeriğe erişebilir (kategori bazlı kısıtlama yok).

## Soru Modülü

Her soru bağımsız bir modüldür (kategori, kart görseli/sesi, İngilizce soru + soru sesi, süre, puan, durum, sıra, 4 seçenek A/B/C/D — her biri başlık+görsel+ses+doğru/yanlış). Süre ve puan soru bazında opsiyoneldir: boş bırakılırsa `/admin/settings`'teki varsayılan değer kullanılır, doldurulursa o soruya özel istisna geçerli olur.

Medya formatları: görsel WebP (tercih)/PNG/JPG (≤5MB), ses MP3 (zorunlu destek)/OGG (opsiyonel destek) (≤10MB). Yüklenen dosyalar `uploads/questions/{id}/` ve `uploads/options/{id}/` altında saklanır; yeni dosya yüklendiğinde eskisi otomatik silinir, "kaldır" seçeneğiyle dosya tamamen silinebilir. Soru silindiğinde ilişkili tüm medya dosyaları da diskten temizlenir.

Soru oluşturma/düzenleme tek sayfada, sekmeler halinde yapılır: Genel Bilgiler, Kart, Soru, Seçenekler, Önizleme (önizleme yalnızca kaydedilmiş sorularda gösterilir).

## Sağlık Kontrolü

`GET /health` — uygulamanın ve veritabanı bağlantısının çalıştığını doğrular:

```json
{"status": "ok", "database": "connected"}
```

## Geliştirme Süreci

Bu proje fazlar halinde geliştirilmektedir. Güncel durum ve faz listesi için [ROADMAP.md](ROADMAP.md), sürüm geçmişi için [CHANGELOG.md](CHANGELOG.md) dosyasına bakın. Geliştiriciler için mimari kurallar [CLAUDE.md](CLAUDE.md) içinde yer alır.
