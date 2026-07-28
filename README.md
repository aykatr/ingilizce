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
| `GET /admin/settings/menu` | Menü Yönetimi (Kart Seçim Menüsü ayarları) (giriş gerektirir) |
| `POST /admin/settings/menu` | Menü genel+görünüm ayarlarını güncelle (giriş gerektirir) |
| `POST /admin/settings/menu/cards` | Kartların menü sırasını/görünürlüğünü toplu güncelle (giriş gerektirir) |
| `GET /admin/media-library` | Medya kütüphanesi listesi (giriş gerektirir) |
| `GET /admin/media-library/api/list` | Medya listesi (JSON, "Kütüphaneden Seç" widget'ı için — giriş gerektirir) |
| `POST /admin/media-library/upload` | Toplu dosya yükleme (giriş gerektirir) |
| `POST /admin/media-library/{id}/replace` | Dosyayı aynı uzantıda yenisiyle değiştir (giriş gerektirir) |
| `POST /admin/media-library/{id}/delete` | Dosya sil — kullanımdaysa engellenir (giriş gerektirir) |
| `GET /admin/audit-log` | Denetim kaydı listesi, filtre+sayfalama (giriş gerektirir) |
| `GET /admin/backup` | Yedekleme sayfası (giriş gerektirir) |
| `POST /admin/backup/download` | Veritabanının SQL yedeğini indirir (giriş gerektirir) |

Giriş gerektiren rotalar `App\Controllers\Admin\AdminBaseController` üzerinden korunur; oturumu olmayan istekler `/admin/login`'e yönlendirilir.

## Oyun API'si (herkese açık, lisans gerektirir)

| Route | Açıklama |
|---|---|
| `POST /play/api/start` | Kart Seçim Menüsü'nden seçilen kartın (`question_id`) oturumunu başlatır/sıfırlar |
| `POST /play/api/answer` | Seçilen şıkkı gönderir (`position=A\|B\|C\|D`), sonucu döndürür |
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

Oyun akışı: QR → `play.php?t=TOKEN` → lisans doğrulanır → **Başlangıç Ekranı** ("Başla" butonuyla) → **Kart Seçim Menüsü** → çocuk bir kart seçer → o kartın (sorunun) oturumu başlar → kart tamamlanır → otomatik olarak Kart Seçim Menüsü'ne dönülür. "Ana Menü" (🏠) butonu oyun ekranında her zaman menüye döner.

- Sunucu tarafı oyun durumu (`App\Services\GameSessionService`) PHP session'da tutulur: seçilen kartın verisi, o anki soruda denenmiş şıklar, kart-bazlı can. Doğru cevap sunucuda doğrulanır, istemciye asla sızdırılmaz.
- İstemci tarafı `GameEngine` sınıfı (`assets/js/game-engine.js`) saf mantık — DOM'a dokunmaz, ses için yalnızca `AudioManager` API'sini çağırır, sunucudan yalnızca seçilen kartın ID'sini geçirir. `assets/js/game-ui.js` DOM/GSAP render katmanı.
- **Süre**: geriye sayar, son 5 saniyede kırmızıya döner + nabız animasyonu; süre dolarsa yanlış cevap sayılır.
- **Can**: varsayılan 3 (Site Ayarları'ndan değiştirilebilir), yanlış cevapta azalır, 0 olunca o kart için oyun biter. Can **kart-bazlı bağımsızdır** — her kart seçiminde sıfırdan başlar, bir karttaki can bitmesi diğer kartları etkilemez, aynı kart tekrar seçilebilir.
- **Doğru cevap**: buton yeşil, diğerleri pasif, +puan animasyonu, ardından otomatik olarak Kart Seçim Menüsü'ne dönüş.
- **Yanlış cevap**: buton kırmızı ve devre dışı kalır, doğru cevap hemen gösterilmez — kalan şıklarla tekrar denenebilir.
- **Başlangıç ekranı içeriği** (arka plan, logo, maskot görselleri, başlık/açıklama/buton metni) tamamen admin panelden yönetilir (`/admin/settings/start-screen`); kod içinde sabit görsel/metin yoktur. Yüklenmemiş görseller sayfa bozulmadan gizlenir.
- Eski "tüm soruları tek oturumda sırayla oynatma" modeli ve buna bağlı Önceki/Sonraki inceleme + Sonuç Ekranı akışı Kart Seçim Menüsü ile kaldırıldı; ilgili kod (`#screen-result`, `renderResult()`) silinmedi, sadece artık bu akış tarafından tetiklenmiyor (bkz. aşağıki bölüm).

## Kart Seçim Menüsü

Fiziksel kartların dijital karşılığı olan bir seçim ekranı: aktif sorular (`/admin/settings/menu`'deki "Sıra" alanına göre sıralanır) kart görseliyle (mevcut "Kart Görseli", ayrıca yönetilmez) listelenir; tamamlanan kartlar ✓ işaretiyle gösterilir. Üstte ziyaret boyunca kalıcı **Toplam Puan**, **Tamamlanan Kart Sayısı** ve **Toplam Rozet** istatistikleri gösterilir (`App\Services\MenuProgressService`, kartlar arası korunur — sayfa yenilense/menüye dönülse de sıfırlanmaz).

`/admin/settings/menu` (Menü Yönetimi) üç bölüm sunar: **Genel** (menü başlığı/açıklaması/arka plan görseli), **Görünüm** (kolon sayısı, kart boyutu, kartlar arası boşluk, kart köşe yuvarlaklığı — CSS custom property'lerle uygulanır), **Kartlar** (mevcut "Sıra" ve "Aktif" — burada "Menüde Göster" olarak — alanlarını toplu düzenleyen bir tablo). Yeni bir veritabanı tablosu/kolonu eklenmedi; tüm ayarlar mevcut `settings` tablosu ve `questions.sort_order`/`questions.is_active` üzerinden yönetiliyor.

Can her kart için bağımsız olduğundan, rozet koşullarındaki "N doğru cevap" / "belirli puana ulaşma" gibi çok-adımlı koşullar artık kart-bazlı değil ziyaret-bazlı (yukarıdaki `MenuProgressService`) değerlendirilir — aksi halde tek soruluk bir kart oturumunda hiçbir zaman sağlanamazlardı.

**Görünüm**: Kullanıcının sağladığı referans tasarıma göre — mor ışın/bulut zeminli panel, "Ana Sayfa"/"Ses" ikon-etiket butonları, kart başına döngüsel pastel renk teması (8 tema, kart sırasına göre), her kartın altında 3 yıldız + "Tamamlandı"/"Başlamadın" durum etiketi, alt kısımda Toplam Puan/Tamamlanan Kart/Rozetler ve yeşil bir ilerleme çubuğu. Veri modelimiz yalnızca tamamlandı/tamamlanmadı ikili durumunu tuttuğu için referans tasarımdaki bazı kartların kısmi (1/3, 2/3) yıldız görünümü uygulanmadı — tamamlanan kart 3/3 dolu yıldız, tamamlanmamış kart boş yıldız gösterir.

## Faz 9 Öncesi UI Kalite Kontrol Turu

Version 1.0 öncesi, üç ekran (Giriş/Kart Seçim Menüsü/Soru Ekranı) referans tasarımlarla görsel olarak hizalandı — iş mantığı/veri modeli/GameEngine değişmedi.

- **Giriş Ekranı**: "QR Kodu ile Giriş / Lisans Kodu ile Giriş" sekmeli paneli eklendi — **tamamen dekoratif** (kamera/tarama/doğrulama yapmaz, sekmeler arası geçiş saf istemci-taraflı). QR alanı yeni bir admin ayarı kullanır: `/admin/settings/start-screen` → **"QR Kod Görseli"** (görsel yüklenmemişse nötr bir placeholder ikonu gösterilir). "Nasıl Çalışır?" 3 adımlı bilgi paneli statik olarak eklendi.
- **Soru Ekranı**: Artık hiçbir zaman aktif olamayan eski "Önceki/Sonraki" (çok-sorulu inceleme modu) butonları kaldırıldı; aynı konumda **"🏠 Ana Menü"** (Kart Seçim Menüsü'ne döner) ve **"🔄 Tekrar Oyna"** (aynı kartı can/süre/skor sıfırlanmış olarak yeniden başlatır, ziyaret-boyu toplam puan/rozet/tamamlanan-kart sayısı korunur) butonları var.
- **Masaüstü/Tablet**: `768px`/`1024px` breakpoint'lerinde içerik ve yazı tipleri kontrollü şekilde büyütüldü — uygulama artık geniş ekranlarda "telefon ekranı gibi ortada küçük kalmıyor", yapısı/yerleşimi değişmeden ölçekleniyor.

## Puan, Başarı Mesajları, Rozetler ve Geçiş Mesajları

Dört bağımsız sistem: **Puan** (soru bazlı, doğru cevapta eklenir, `GameSessionService` içinde tutulur, sonuç ekranında gösterilir), **Başarı Mesajları** (`/admin/messages` — Doğru/Yanlış grupları, her mesaj başlık+ses+animasyon tipi+aktif/pasif; oyun sırasında ilgili gruptan aktif bir mesaj rastgele seçilir), **Rozetler** (`/admin/badges` — başlık+açıklama+görsel+ses+animasyon+koşul+aktif/pasif), **Geçiş Mesajları** (`/admin/transition-messages` — başlık+ses+animasyon tipi+aktif/pasif, tek grup; her doğru cevapta sıradaki bir soru varsa aktif bir mesaj rastgele seçilir, `AudioManager`'ın `transition` kategorisiyle çalınır).

Rozet koşulları kod içine sabit yazılmaz: her rozet bir `condition_type` (+ opsiyonel `condition_value`) taşır, `App\Services\BadgeService` bunu genişletilebilir bir closure haritasıyla değerlendirir. Hazır koşullar: ilk doğru cevap, belirli sayıda doğru cevap, hatasız tamamlama, süre dolmadan tamamlama, belirli puana ulaşma — yenileri kolayca eklenebilir. Değerlendirme tamamen sunucu tarafında (`GameSessionService`); istemci yalnızca hangi rozetlerin kazanıldığı bilgisini alır ve gösterir. Aynı rozet aynı ziyaret boyunca (Kart Seçim Menüsü'nden oynanan tüm kartlar arasında) ikinci kez verilmez; "belirli sayıda doğru cevap"/"belirli puana ulaşma" gibi koşullar da tek bir kartla değil, ziyaret boyunca biriken toplamla değerlendirilir.

Kazanılan rozetler oyun sırasında altın renkli bir bildirimle (görsel+ses+animasyon) gösterilir. **Geçiş Mesajları modülü Kart Seçim Menüsü'nden sonra fiilen devre dışıdır**: her kart oturumu artık tam olarak bir soru içerdiği için "sıradaki soru" hiçbir zaman olmuyor ve geçiş mesajı tetiklenmiyor — modül (admin CRUD dahil) kasıtlı olarak silinmedi, ileride farklı bir oyun modunda yeniden anlamlı olabilir.

## AudioManager

Tüm ses oynatma `assets/js/audio-manager.js` üzerinden geçer (`window.AudioManager` — tek giriş noktası, Howler.js sarmalayıcısı); uygulamada hiçbir yerde doğrudan `new Howl()` çağrılmaz.

- **Play / Stop / Pause / Resume / Replay**, **Queue** (düşük öncelikli istekler mevcut ses bitene kadar bekler, otomatik sıradaki çalar), **Preload/Cache** (aynı ses tekrar istenirse yeniden indirilmez), **Fade In/Out**, **Volume**, **Mute/Unmute**, **Öncelik (priority)** — kategoriye göre varsayılan, istek bazında override edilebilir.
- Aynı anda yalnızca bir "ana" ses çalar (kart/soru/seçenek/doğru/yanlış/geçiş/rozet kategorileri bu kanalı paylaşır); arayüz sesleri (`ui` kategorisi) ayrı kanalda, ana sesi etkilemez.
- Aynı sesin üst üste binmesi engellenir; eşit veya yüksek öncelikli yeni istek mevcut sesi 180ms fade-out ile keser, düşük öncelikli istek sıraya alınır.
- `GameEngine` minimum entegrasyon: `playCardAudio()/playQuestionAudio()/playOptionAudio()` DB'den gelen gerçek ses yollarını (`questions`/`question_options` tabloları) `AudioManager` üzerinden çalar; yeni soru yüklendiğinde ilgili sesler otomatik preload edilir. Correct/wrong/badge kategorileri Faz 7'de, `transition` kategorisi geçiş mesajları modülüyle bağlandı — 8 kategorinin tamamı artık kullanımda.

## Medya Kütüphanesi, Denetim Kaydı ve Yedekleme

**Medya Kütüphanesi** (`/admin/media-library`) sistemdeki tüm görsel (WebP/PNG/JPG) ve ses (MP3/OGG) dosyalarını tek ekranda listeler — soru/seçenek/başarı mesajı/rozet/geçiş mesajı/başlangıç ekranı için daha önce yüklenmiş her dosya dahil, ayrıca bir aktarım gerekmeden otomatik görünür. Arama, tür/kullanım filtresi, önizleme (görsel için thumbnail, ses için oynatıcı), dosya boyutu/türü, "kullanıldığı yerler" listesi (ilgili kaydın düzenleme sayfasına bağlantılı), toplu yükleme ve aynı-uzantı dosya değiştirme desteklenir. Kullanımda olan bir dosya silinemez — önce ilgili kayıttan kaldırılması gerekir.

Soru/mesaj/rozet/geçiş mesajı/başlangıç ekranı formlarındaki her dosya alanının yanında bir **"Kütüphaneden Seç"** butonu bulunur; bu buton kütüphaneden seçilen dosyayı gerçek bir dosya olarak forma yükler (bilgisayardan seçmiş gibi) — istenirse bilgisayardan doğrudan yükleme de her zaman çalışmaya devam eder, ikisi birbirini dışlamaz.

**Denetim Kaydı** (`/admin/audit-log`) giriş/çıkış, tüm içerik CRUD işlemleri, ayar güncellemeleri, lisans işlemleri ve medya işlemlerini kim/ne zaman/ne yaptı bilgisiyle kayıt altına alır; işlem türü, arama ve tarih aralığına göre filtrelenebilir.

**Yedekleme** (`/admin/backup`) veritabanının tam bir SQL yedeğini (tüm tablo yapıları + veriler) tek tıkla `.sql` dosyası olarak indirmeyi sağlar. Bu sürümde yalnızca indirme desteklenir; yedekten geri yükleme (restore) kapsam dışıdır.

## Sağlık Kontrolü

`GET /health` — uygulamanın ve veritabanı bağlantısının çalıştığını doğrular:

```json
{"status": "ok", "database": "connected"}
```

## Geliştirme Süreci

Bu proje fazlar halinde geliştirilmektedir. Güncel durum ve faz listesi için [ROADMAP.md](ROADMAP.md), sürüm geçmişi için [CHANGELOG.md](CHANGELOG.md) dosyasına bakın. Geliştiriciler için mimari kurallar [CLAUDE.md](CLAUDE.md) içinde yer alır.
