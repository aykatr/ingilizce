# Yippee Learning Platform

Fiziksel eğitim kartlarının üzerindeki QR kod ile çalışan çocuk eğitim platformu (İngilizce kart oyunu) ve yönetim paneli.

## Gereksinimler

- PHP 8.3+
- MySQL 8.x
- Composer
- Apache (`mod_rewrite`, `AllowOverride All`)

Frontend bağımlılıkları (Bootstrap 5, QRCode.js, GSAP, Howler.js, Google Fonts) CDN üzerinden yüklenir — npm/build adımı yoktur.

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
  Services/       İş kuralları (AuthService, LicenseService, SettingService, CategoryService, QuestionService, MediaUploadService, StartScreenService, GameSessionService, AchievementMessageService, BadgeService)
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
| `GET/POST /admin/settings` | Site URL + varsayılan süre/puan/can ayarı (giriş gerektirir) |
| `GET/POST /admin/settings/start-screen` | Başlangıç ekranı görselleri/metinleri (giriş gerektirir) |
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
| `GET /admin/messages` | Başarı mesajları listesi — Doğru/Yanlış grupları (giriş gerektirir) |
| `GET /admin/messages/create` | Yeni mesaj formu (giriş gerektirir) |
| `POST /admin/messages` | Mesaj oluştur (giriş gerektirir) |
| `GET /admin/messages/{id}/edit` | Mesaj düzenleme formu (giriş gerektirir) |
| `POST /admin/messages/{id}` | Mesaj güncelle (giriş gerektirir) |
| `POST /admin/messages/{id}/delete` | Mesaj sil (giriş gerektirir) |
| `GET /admin/badges` | Rozet listesi (giriş gerektirir) |
| `GET /admin/badges/create` | Yeni rozet formu (giriş gerektirir) |
| `POST /admin/badges` | Rozet oluştur (giriş gerektirir) |
| `GET /admin/badges/{id}/edit` | Rozet düzenleme formu (giriş gerektirir) |
| `POST /admin/badges/{id}` | Rozet güncelle (giriş gerektirir) |
| `POST /admin/badges/{id}/delete` | Rozet sil (giriş gerektirir) |
| `GET /admin/transition-messages` | Geçiş mesajları listesi (giriş gerektirir) |
| `GET /admin/transition-messages/create` | Yeni geçiş mesajı formu (giriş gerektirir) |
| `POST /admin/transition-messages` | Geçiş mesajı oluştur (giriş gerektirir) |
| `GET /admin/transition-messages/{id}/edit` | Geçiş mesajı düzenleme formu (giriş gerektirir) |
| `POST /admin/transition-messages/{id}` | Geçiş mesajı güncelle (giriş gerektirir) |
| `POST /admin/transition-messages/{id}/delete` | Geçiş mesajı sil (giriş gerektirir) |

Giriş gerektiren rotalar `App\Controllers\Admin\AdminBaseController` üzerinden korunur; oturumu olmayan istekler `/admin/login`'e yönlendirilir.

## Oyun API'si (herkese açık, lisans gerektirir)

| Route | Açıklama |
|---|---|
| `POST /play/api/start` | Oyun oturumunu başlatır/sıfırlar, ilk soruyu döndürür |
| `POST /play/api/answer` | Seçilen şıkkı gönderir (`position=A\|B\|C\|D`), sonucu ve (doğruysa) sıradaki soruyu döndürür |
| `POST /play/api/timeout` | Süre dolduğunda çağrılır, yanlış cevap gibi işlenir |

Bu uçlar yalnızca `play.php?t=...` üzerinden geçerli bir lisansla girildiyse çalışır (session flag), ve CSRF token gerektirir.

## Lisans Sistemi

Bir lisans oluşturulduğunda rastgele bir `token` (URL için, 32 karakter) ve insan-okunur bir `code` (ör. `K3F9-8H2M-QW7X`, referans/yazdırma için) üretilir. Oynama linki `{site_url}/play.php?t={token}` şeklindedir; `site_url` admin panelinden (`/admin/settings`) değiştirilebilir. QR kod görseli **istemci tarafında** QRCode.js ile admin panelde (lisans listesindeki "QR" butonu → modal) üretilir; PHP tarafında QR kütüphanesi kullanılmaz.

`play.php` kök dizinde bağımsız bir giriş noktasıdır (MVC router'dan geçmez, `index.php` ile aynı şekilde bootstrap olur, parametre adı `t`). Token'ı doğrular; lisans yoksa, pasifse veya süresi dolmuşsa 403 ile nedeni açıklayan bir sayfa gösterir, geçerliyse oyun kabuğunu (başlangıç/oyun/sonuç ekranları) render eder ve aktivasyon/son kullanım/son cihaz/son IP bilgilerini günceller. Lisans durumu (Aktif / Pasif / Süresi Doldu) admin panelde `is_active` ve opsiyonel `expires_at` alanlarından hesaplanarak gösterilir. Tüm aktif lisanslar tüm içeriğe erişebilir (kategori bazlı kısıtlama yok — lisansların kategori/pakete bağlanması ileride Faz 10 "Kart Paketleri" ile gelecek).

## Soru Modülü

Her soru bağımsız bir modüldür (kategori, kart görseli/sesi, İngilizce soru + soru sesi, süre, puan, durum, sıra, 4 seçenek A/B/C/D — her biri başlık+görsel+ses+doğru/yanlış). Süre ve puan soru bazında opsiyoneldir: boş bırakılırsa `/admin/settings`'teki varsayılan değer kullanılır, doldurulursa o soruya özel istisna geçerli olur.

Medya formatları: görsel WebP (tercih)/PNG/JPG (≤5MB), ses MP3 (zorunlu destek)/OGG (opsiyonel destek) (≤10MB). Yüklenen dosyalar `uploads/questions/{id}/` ve `uploads/options/{id}/` altında saklanır; yeni dosya yüklendiğinde eskisi otomatik silinir, "kaldır" seçeneğiyle dosya tamamen silinebilir. Soru silindiğinde ilişkili tüm medya dosyaları da diskten temizlenir.

Soru oluşturma/düzenleme tek sayfada, sekmeler halinde yapılır: Genel Bilgiler, Kart, Soru, Seçenekler, Önizleme (önizleme yalnızca kaydedilmiş sorularda gösterilir).

## Oyun Motoru

Oyun akışı: QR → `play.php?t=TOKEN` → lisans doğrulanır → tüm aktif sorular sıraya konur → **Başlangıç Ekranı** (yalnızca ilk girişte, "Başla" butonuyla) → sorular tek tek (her geçişte sunucudan) yüklenir → **Sonuç Ekranı**.

- Sunucu tarafı oyun durumu (`App\Services\GameSessionService`) PHP session'da tutulur: soru sırası, mevcut index, skor, can, o anki soruda denenmiş şıklar. Doğru cevap sunucuda doğrulanır, istemciye asla sızdırılmaz.
- İstemci tarafı `GameEngine` sınıfı (`assets/js/game-engine.js`) saf mantık — DOM'a dokunmaz, ses için yalnızca `AudioManager` API'sini çağırır. `assets/js/game-ui.js` DOM/GSAP render katmanı.
- **Süre**: geriye sayar, son 5 saniyede kırmızıya döner + nabız animasyonu; süre dolarsa yanlış cevap sayılır.
- **Can**: varsayılan 3 (Site Ayarları'ndan değiştirilebilir), yanlış cevapta azalır, 0 olunca oyun biter. Can, tüm oyun boyunca paylaşılır (soru başına sıfırlanmaz).
- **Doğru cevap**: buton yeşil, diğerleri pasif, +puan animasyonu, otomatik geçiş.
- **Yanlış cevap**: buton kırmızı ve devre dışı kalır, doğru cevap hemen gösterilmez — kalan şıklarla tekrar denenebilir.
- **Önceki/Sonraki**: Önceki yalnızca tamamlanmış sorularda çalışır (istemci tarafında önbelleklenmiş, salt-okunur inceleme); Sonraki yalnızca inceleme modundayken aktiftir.
- **Başlangıç ekranı içeriği** (arka plan, logo, maskot görselleri, başlık/açıklama/buton metni) tamamen admin panelden yönetilir (`/admin/settings/start-screen`); kod içinde sabit görsel/metin yoktur. Yüklenmemiş görseller sayfa bozulmadan gizlenir.

## Puan, Başarı Mesajları, Rozetler ve Geçiş Mesajları

Dört bağımsız sistem: **Puan** (soru bazlı, doğru cevapta eklenir, `GameSessionService` içinde tutulur, sonuç ekranında gösterilir), **Başarı Mesajları** (`/admin/messages` — Doğru/Yanlış grupları, her mesaj başlık+ses+animasyon tipi+aktif/pasif; oyun sırasında ilgili gruptan aktif bir mesaj rastgele seçilir), **Rozetler** (`/admin/badges` — başlık+açıklama+görsel+ses+animasyon+koşul+aktif/pasif), **Geçiş Mesajları** (`/admin/transition-messages` — başlık+ses+animasyon tipi+aktif/pasif, tek grup; her doğru cevapta sıradaki bir soru varsa aktif bir mesaj rastgele seçilir, `AudioManager`'ın `transition` kategorisiyle çalınır).

Rozet koşulları kod içine sabit yazılmaz: her rozet bir `condition_type` (+ opsiyonel `condition_value`) taşır, `App\Services\BadgeService` bunu genişletilebilir bir closure haritasıyla değerlendirir. Hazır koşullar: ilk doğru cevap, belirli sayıda doğru cevap, hatasız tamamlama, süre dolmadan tamamlama, belirli puana ulaşma — yenileri kolayca eklenebilir. Değerlendirme tamamen sunucu tarafında (`GameSessionService`); istemci yalnızca hangi rozetlerin kazanıldığı bilgisini alır ve gösterir. Aynı rozet aynı oyun oturumunda ikinci kez verilmez.

Kazanılan rozetler oyun sırasında altın renkli bir bildirimle (görsel+ses+animasyon) gösterilir, oturum sonunda sonuç ekranında özetlenir. Geçiş mesajı ise mor renkli bir toast olarak, doğru cevap bildiriminden kısa bir süre sonra gösterilir; son soru cevaplandığında (geçilecek sıradaki soru olmadığı için) hiç gösterilmez.

## AudioManager

Tüm ses oynatma `assets/js/audio-manager.js` üzerinden geçer (`window.AudioManager` — tek giriş noktası, Howler.js sarmalayıcısı); uygulamada hiçbir yerde doğrudan `new Howl()` çağrılmaz.

- **Play / Stop / Pause / Resume / Replay**, **Queue** (düşük öncelikli istekler mevcut ses bitene kadar bekler, otomatik sıradaki çalar), **Preload/Cache** (aynı ses tekrar istenirse yeniden indirilmez), **Fade In/Out**, **Volume**, **Mute/Unmute**, **Öncelik (priority)** — kategoriye göre varsayılan, istek bazında override edilebilir.
- Aynı anda yalnızca bir "ana" ses çalar (kart/soru/seçenek/doğru/yanlış/geçiş/rozet kategorileri bu kanalı paylaşır); arayüz sesleri (`ui` kategorisi) ayrı kanalda, ana sesi etkilemez.
- Aynı sesin üst üste binmesi engellenir; eşit veya yüksek öncelikli yeni istek mevcut sesi 180ms fade-out ile keser, düşük öncelikli istek sıraya alınır.
- `GameEngine` minimum entegrasyon: `playCardAudio()/playQuestionAudio()/playOptionAudio()` DB'den gelen gerçek ses yollarını (`questions`/`question_options` tabloları) `AudioManager` üzerinden çalar; yeni soru yüklendiğinde ilgili sesler otomatik preload edilir. Correct/wrong/badge kategorileri Faz 7'de, `transition` kategorisi geçiş mesajları modülüyle bağlandı — 8 kategorinin tamamı artık kullanımda.

## Sağlık Kontrolü

`GET /health` — uygulamanın ve veritabanı bağlantısının çalıştığını doğrular:

```json
{"status": "ok", "database": "connected"}
```

## Geliştirme Süreci

Bu proje fazlar halinde geliştirilmektedir. Güncel durum ve faz listesi için [ROADMAP.md](ROADMAP.md), sürüm geçmişi için [CHANGELOG.md](CHANGELOG.md) dosyasına bakın. Geliştiriciler için mimari kurallar [CLAUDE.md](CLAUDE.md) içinde yer alır.
