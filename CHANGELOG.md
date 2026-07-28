# Changelog

Bu proje [Keep a Changelog](https://keepachangelog.com/) formatını takip eder.

## [0.8.1] - 2026-07-28

Faz 7 onayından sonra, Faz 8'e geçmeden önce tamamlanan ek modül: Geçiş Mesajları (Faz 7'nin orijinal taslağında vardı, o fazın kapsamına dahil edilmemişti).

### Eklendi

- `transition_messages` tablosu + `App\Services\TransitionMessageService` — tek grup (başlık+ses+animasyon+aktif/pasif), `/admin/transition-messages` CRUD, oyun sırasında aktif bir mesaj rastgele seçilir (`pickRandom()`)
- `GameSessionService` entegrasyonu — her doğru cevapta, sıradaki bir soru varsa (`isFinished=false`) yanıta `transitionMessage` eklenir; son soruda `null` (gösterilecek bir "sıradaki" olmadığı için)
- Frontend: mor renkli `.transition-toast` bildirimi, doğru cevap bildiriminden ~700ms sonra gösterilir, `AudioManager`'ın daha önce bağlı olmayan `transition` kategorisini kullanır (öncelik=6, `correct/wrong`'un altında — mevcut sesi kesmeden sıraya girer)

### Test

Admin CRUD ve oyun akışı entegrasyonu (sıradaki soru varken/yokken mesaj var/yok) curl ile uçtan uca doğrulandı. Playwright ile 2 ek kontrol (geçiş toastı görünürlüğü, son soruda gösterilmemesi) — 2/2 geçti. Faz 5 (58 kontrol), Faz 6 (26 kontrol) ve Faz 7 (8 kontrol) regresyon paketleri tekrar çalıştırıldı, hepsi temiz — toplam 68/68.

## [0.8.0] - 2026-07-28

Faz 7 — Puan ve Rozet Sistemi. Puan, başarı mesajları ve rozetler bağımsız sistemler olarak geliştirildi.

### Eklendi

- `achievement_messages` tablosu + `App\Services\AchievementMessageService` — Doğru/Yanlış grupları, `/admin/messages` CRUD, oyun sırasında aktif bir mesaj rastgele seçilir (`pickRandom()`)
- `badges` tablosu + `App\Services\BadgeService` — `/admin/badges` CRUD, genişletilebilir koşul değerlendirme motoru (`condition_type` + closure haritası — kod içine sabit rozet kuralı yazılmadı)
- Hazır rozet koşulları: ilk doğru cevap, belirli sayıda doğru cevap, belirli puana ulaşma, hatasız tamamlama, süre dolmadan tamamlama
- `GameSessionService` entegrasyonu — her cevaptan sonra mesaj seçimi + rozet değerlendirmesi, `earnedBadges`/`message` JSON alanları; oturum içinde `awarded_badge_ids` ile aynı rozetin ikinci kez verilmesi engellendi
- `App\Services\AnimationTypes` — mesaj/rozet ortak animasyon kataloğu (bounce/pulse/shake/pop/fade)
- Frontend: dinamik başarı mesajı (metin+ses+animasyon, `AudioManager` correct/wrong kategorisi), rozet kazanımında altın renkli toast bildirimi (`badge` kategorisi, birden fazla rozet sırayla gösterilir), sonuç ekranında oturum boyunca kazanılan rozetlerin özeti

### Düzeltildi

- `routes/web.php`'de Faz 5 revizyonundan kalma iki ölü route (`GameController::state()/question()` — artık var olmayan metotlara işaret ediyorlardı) temizlendi

### Test

Admin CRUD (mesaj+rozet, validasyon dahil) ve oyun akışındaki mesaj/rozet entegrasyonu (tekli/çoklu rozet kazanımı, tekrar-vermeme, hatasız/hatalı senaryo ayrımı) curl ile uçtan uca doğrulandı. Playwright ile 8 ek kontrol — 8/8 geçti. Faz 5 (58 kontrol) ve Faz 6 (26 kontrol) regresyon paketleri tekrar çalıştırıldı, hâlâ temiz.

### Kapsam dışı

Geçiş mesajları (soru arası) bu fazda yer almadı — bu turun açık talimatı yalnızca Puan/Başarı Mesajları(Doğru+Yanlış)/Rozet'i kapsıyordu. `AudioManager`'ın `transition` kategorisi buna hazır.

## [0.7.0] - 2026-07-28

Faz 6 — Ses Sistemi. Kapsam kullanıcı tarafından net çizildi: yalnızca profesyonel bir `AudioManager` geliştirildi, oyun ekranı/UI değişmedi.

### Eklendi

- `assets/js/audio-manager.js` — Howler.js üzerine kurulu `AudioManager` sınıfı, `window.AudioManager` singleton'ı olarak tek giriş noktası. Uygulamada hiçbir yerde doğrudan `new Howl()` yok.
- Play/Stop/Pause/Resume/Replay, Queue (öncelik sıralı, otomatik sıradaki), Preload/Cache (`Map<url,Howl>`), Fade In/Out (180ms kesme fade'i), Volume, Mute/Unmute, öncelik (priority) sistemi, aynı sesin üst üste binmesini engelleme, mobil unlock (`prime()`)
- 8 ses kategorisi: card, question, option, correct, wrong, transition, badge, ui — "main" kanalı (aynı anda tek ses) ve bağımsız "ui" kanalı
- `GameEngine` minimum entegrasyonu: `playCardAudio()/playQuestionAudio()/playOptionAudio()` + soru değiştiğinde otomatik preload; ses dosyası yolları tamamen DB'den (`questions`/`question_options`) geliyor, kod içinde sabit yol yok
- `game-ui.js`'deki mute butonları artık gerçekten `AudioManager.mute()/unmute()`'a bağlı (önceden yalnızca ikon değiştiren yerel bir bayraktı)

### Test

Playwright ile 26 otomatik kontrol (standalone AudioManager davranışları + GameEngine buton entegrasyonu) — 26/26 geçti, 0 konsol hatası. Faz 5 regresyon paketi (58 kontrol) tekrar çalıştırıldı, hâlâ temiz.

**Bulunan ve düzeltilen tasarım sorunu:** eşit öncelikli farklı bir ses istendiğinde (ör. kullanıcı başka bir hoparlör butonuna tıklaması) sessizce sıraya alınıyordu; artık hemen çalıyor (`priority >= current` kesme kuralı — sıraya alma yalnızca gerçekten düşük öncelikli istekler için).

## [0.6.1] - 2026-07-28

Faz 5 kapanışı öncesi Playwright ile kapsamlı görsel/fonksiyonel doğrulama (kullanıcı talebiyle). 58 otomatik kontrol — 0 konsol hatası, 0 network hatası.

### Düzeltildi

- Süre sayacında `is-low` (son 5 saniye kırmızı+pulse) sınıfı yalnızca ilk `setInterval` tick'inde uygulanıyordu; kısa süreli sorularda (ör. 2sn) bu görsel geri bildirim neredeyse hiç görünmüyordu. `startTimer()` artık başlangıç anında da kontrol ediyor (`assets/js/game-ui.js`).

### Eklendi

- Kart ve seçenek görselleri için `.image-placeholder` — görsel yüklenmemişse nötr bir ikon kutusu gösterilir (önceden `<img>` tamamen gizlenip boşluk bırakılıyordu; artık tasarım daha tutarlı doluyor)

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
