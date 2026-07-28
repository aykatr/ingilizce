# ROADMAP

Bu proje, her biri kullanıcı onayı ile kapatılan fazlar halinde geliştirilir. Bir faz tamamen bitmeden (kod kontrolü, hata düzeltme, migration, test, README/CHANGELOG güncellemesi) bir sonraki faza geçilmez.

> Faz 3 sonunda proje kapsamı "YIPPEE LEARNING PLATFORM" tam spesifikasyonuyla netleştirildi (Repository+Service mimarisi, Bootstrap 5/QRCode.js frontend, zenginleştirilmiş lisans şeması). Faz 1-3'ün kodu bu mimariye göre revize edildi.
>
> Faz 4 tasarım kararlarıyla birlikte eski "Faz 5 — Seçenek Sistemi" içeriği (4 seçenek yönetimi, seçenek medya yükleme) Faz 4'ün kapsamına dahil edildi ve fazlar buna göre yeniden numaralandırıldı: eski Faz 6 → yeni Faz 5, eski Faz 7 → yeni Faz 6, ... eski Faz 16 → yeni Faz 15.

## Durum

| Faz | Konu | Durum |
|---|---|---|
| 1 | Proje Altyapısı | ✅ Tamamlandı |
| 2 | Admin Paneli | ✅ Tamamlandı |
| 3 | Lisans Sistemi | ✅ Tamamlandı (genişletilmiş kapsam) |
| 4 | Soru Modülü (Seçenek Sistemi dahil) | ✅ Tamamlandı |
| 5 | Oyun Motoru | ✅ Tamamlandı |
| 6 | Ses Sistemi | ⏳ Onay bekleniyor |
| 7 | Puan ve Rozet Sistemi | ⏳ Bekliyor |
| 8 | Medya ve Ayarlar | ⏳ Bekliyor |
| 9 | Optimizasyon | ⏳ Bekliyor |
| 10 | Çoklu Dil Altyapısı | ⏳ Bekliyor |
| 11 | Kart Paketleri | ⏳ Bekliyor |
| 12 | İlerleme ve Kaldığı Yerden Devam | ⏳ Bekliyor |
| 13 | PWA / Offline Altyapı | ⏳ Bekliyor |
| 14 | Analytics Altyapısı | ⏳ Bekliyor |
| 15 | Medya Optimizasyonu (toplu yükleme, önizleme, WebP, MP3/OGG) | ⏳ Bekliyor |

## Faz 1 — Proje Altyapısı ✅

- [x] Klasör yapısı
- [x] MVC mimarisi
- [x] Routing (`App\Core\Router`)
- [x] Config (`config/app.php`, `config/database.php`, `App\Core\Config`, `App\Core\Env`)
- [x] Database bağlantısı (`App\Core\Database`, PDO/MySQL)
- [x] Helper sınıfları (`App\Helpers\Str`, global helper fonksiyonları)
- [x] Base Controller
- [x] Base Model
- [x] Login sayfası iskeleti (`GET /admin/login`)
- [x] README
- [x] CLAUDE.md
- [x] ROADMAP.md

**Teslim:** Çalışan boş proje iskeleti. `/` ve `/admin/login` render ediliyor, `/health` veritabanı bağlantısını doğruluyor, `app/` `config/` `routes/` `storage/` `database/` klasörleri web'den erişime kapalı.

## Faz 2 — Admin Paneli ✅

- [x] Admin giriş (kimlik doğrulama mantığı) — `admins` tablosu, `password_hash`/`password_verify`, CSRF korumalı form, iş kuralı `App\Services\AuthService` içinde
- [x] Şifre değiştirme — mevcut şifre doğrulama + minimum 8 karakter kuralı (`AuthService::changePassword()`)
- [x] Dashboard — `GET /admin/dashboard`
- [x] Yetkilendirme — `AdminBaseController` route guard, oturumsuz erişimde `/admin/login`'e yönlendirme
- [x] Session yönetimi — `App\Core\Session` (generic) + `App\Core\Auth` (admin auth semantiği), login'de session id regenerate

**Teslim:** Migration (`admins` tablosu) ve seed script ile çalışan admin paneli. Giriş/çıkış, şifre değiştirme ve route koruması `ingilizce.test` üzerinde uçtan uca test edildi. Bootstrap 5'e geçirildi (Faz 3 revizyonuyla birlikte).

## Faz 3 — Lisans Sistemi ✅ (genişletilmiş kapsam)

- [x] Lisans oluşturma — admin panelinden ad (+opsiyonel son kullanma tarihi) girilerek oluşturulur; benzersiz `token` (URL için) ve `code` (insan-okunur lisans kodu) üretilir — `LicenseService::create()`
- [x] QR üretme — istemci tarafında QRCode.js ile admin panelde (lisans listesi → "QR" butonu → modal) üretilir; PHP tarafında QR kütüphanesi yok
- [x] Token doğrulama — `LicenseService::validateAndTrack()` (`LicenseRepository` üzerinden), geçersiz/pasif/süresi dolmuş durumlarını ayırt eder
- [x] play.php — kök dizinde bağımsız giriş noktası, `?t=TOKEN` doğrular, geçersizse 403 + neden mesajı
- [x] Aktif / Pasif yönetimi — `POST /admin/licenses/{id}/toggle`
- [x] Lisans şeması genişletildi — `code`, `expires_at`, `first_activated_at`, `last_used_at`, `last_device`, `last_ip`
- [x] Durum hesaplama — Aktif / Pasif / Süresi Doldu (`LicenseService::statusLabel()`, DB'de ayrı alan yok, `is_active`+`expires_at`'ten türetilir)
- [x] Site URL ayarı — `settings` tablosu, `SettingService`, `/admin/settings` (QR/oynama linki bu adresi kullanır, Faz 8'in tam "Site Ayarları" modülünün öncüsü olarak minimal tutuldu)
- [x] Mimari revizyonu — Repository+Service katmanı eklendi (`App\Repositories`, `App\Services`), Admin/License/Setting bu katmanlardan geçiyor
- [x] Frontend revizyonu — tüm admin+public view'lar Bootstrap 5'e (CDN) geçirildi, vanilla JS (`assets/js/license-qr.js`)

**Teslim:** Lisans oluşturma (süreli/süresiz), listeleme (durum rozetleri, aktivasyon/kullanım/cihaz/IP kolonları), QR modal, aktif/pasif toggle, site URL güncelleme ve `play.php?t=...` doğrulama+takip akışı `ingilizce.test` üzerinde uçtan uca test edildi. Tüm aktif lisanslar tüm içeriğe erişebiliyor (kategori bazlı kısıtlama yok — ihtiyaç olursa ayrı bir faz olarak ele alınabilir).

## Faz 4 — Soru Modülü ✅ (Seçenek Sistemi dahil)

Soru Modülü Sistemi ilkesi: her soru bağımsız bir modül, birbirine bağlı değil. Yeni soru eklemek mevcutları etkilemez.

- [x] Kategori CRUD — `App\Services\CategoryService`, `/admin/categories` (index/create/edit/delete), kategoriye bağlı soru varsa silme engellenir
- [x] Soru CRUD — `App\Services\QuestionService`, `/admin/questions`, tek sayfa sekmeli form (Genel Bilgiler / Kart / Soru / Seçenekler / Önizleme)
- [x] Kart görseli/sesi yükleme, İngilizce soru + soru sesi yükleme
- [x] 4 seçenek yönetimi (A/B/C/D) — her biri başlık + görsel + ses + doğru/yanlış (radio ile tek doğru cevap zorunlu)
- [x] Süre ve puan — soru bazında opsiyonel (`NULL` ise Site Ayarları'ndaki varsayılan miras alınır: `SettingService::getDefaultDuration()/getDefaultPoints()`)
- [x] Durum (aktif/pasif) ve Sıra alanları
- [x] Medya sistemi — Görsel: WebP/PNG/JPG, Ses: MP3/OGG; yükleme, önizleme, değiştirme (eski dosya otomatik silinir), kaldırma (`MediaUploadService`, `uploads/` — web'den erişilebilir, PHP çalıştırma engelli `.htaccess`)
- [x] Bootstrap 5 uyumlu admin arayüzü
- [x] Tüm veriler MySQL'de (`categories`, `questions`, `question_options`; medya sadece dosya yolu olarak DB'de tutulur, dosyalar `uploads/` altında)

**Teslim:** Kategori CRUD (silme koruması dahil), soru CRUD (medya yükleme/değiştirme/kaldırma, varsayılan süre-puan mirası, doğru cevap validasyonu), soru silme (medya dosyaları dahil temizlik) `ingilizce.test` üzerinde gerçek dosya yüklemeleriyle uçtan uca test edildi. **Kapsam dışı (bilinçli):** oyun ekranı, soru akışı, ses oynatma, kullanıcı deneyimi — bunlar Faz 5'te ele alınacak, bu faz yalnızca içerik yönetim sistemidir.

## Faz 5 — Oyun Motoru ✅

Referans tasarıma (kullanıcı tarafından sağlanan giriş/kart ekranı görselleri) sadık kalınarak geliştirildi. Bootstrap yalnızca grid için, animasyonlar CSS + GSAP ile.

- [x] Başlangıç ekranı — lisans doğrulandıktan sonra gösterilir (yalnızca ilk girişte), tüm görselleri/metinleri admin panelden yönetilebilir (`/admin/settings/start-screen`, `StartScreenService`); görsel yüklenmemişse zarifçe gizlenir, tasarım bozulmaz
- [x] Oyun ekranı — kart görseli, İngilizce soru + dinleme butonu, 2x2 seçenek grid'i (A/B/C/D renkli rozetler + ses butonu), ilerleme çubuğu ("Soru X/Y"), skor rozeti
- [x] Soru akışı — her geçişte yeni soru sunucu tarafından (`GameSessionService`) servis edilir, tüm sorular tek seferde belleğe alınmaz; lisans artık kategoriye bağlı değil, tüm aktif sorular `sort_order`'a göre tek oturumda oynanır
- [x] Süre sayacı — geriye sayar, son 5 saniyede renk değişimi + pulse animasyonu (CSS), süre biterse yanlış cevap kabul edilir (`/play/api/timeout`)
- [x] Can sistemi — varsayılan 3 (Site Ayarları'ndan değiştirilebilir: `default_lives`), yanlış cevapta can azalır, can biterse oyun biter (Game Over)
- [x] Doğru cevap — buton yeşil, diğerleri pasifleşir, +puan animasyonu (GSAP), otomatik geçiş
- [x] Yanlış cevap — buton kırmızı ve devre dışı kalır, doğru cevap hemen gösterilmez, çocuk kalan seçeneklerle tekrar deneyebilir; can biterse oyun sonlanır
- [x] Önceki / Sonraki — Önceki yalnızca tamamlanan (istemci tarafında zaten önbelleğe alınmış) sorular için salt-okunur inceleme sağlar; Sonraki yalnızca inceleme modundayken aktif olur, mevcut soru tamamlanmadan aktif olmaz
- [x] İlerleme çubuğu — "Soru X / Y" + dolum yüzdesi
- [x] GameEngine — istemci tarafı JS sınıfı (`assets/js/game-engine.js`, DOM'a hiç dokunmaz), sunucu tarafında `App\Services\GameSessionService` (oturum durumu: soru sırası, skor, can, index, cevap doğrulama — asla istemciye güvenilmez). UI katmanı (`assets/js/game-ui.js`) ayrı dosyada, yalnızca DOM/GSAP.
- [x] Sonuç ekranı (basit) — final skor, tamamlanan/toplam soru veya Game Over durumu, "Tekrar Oyna" butonu

**Kapsam dışı (bilinçli, sonraki fazlara bırakıldı):** AudioManager (Faz 6), Rozet sistemi (Faz 7), Geçiş mesajları (Faz 7), rastgele doğru/yanlış mesajları (Faz 7). Ses butonları arayüzde mevcut ve basit `Audio.play()` ile çalışıyor ama queue/fade/cache/priority gibi AudioManager özellikleri yok — bunlar kasıtlı olarak eklenmedi.

**Teslim:** Oyun oturumu başlatma, doğru/yanlış cevap akışı (tekrar deneme dahil), zaman aşımı, can sistemi (game over dahil), puan/varsayılan süre-puan mirası, önceki/sonraki inceleme, güvenlik guard'ları (yetkisiz erişim 403, CSRF 419, oturum bitince 422) ve admin başlangıç ekranı ayarları (metin + görsel yükleme/kaldırma) `ingilizce.test` üzerinde gerçek API çağrılarıyla uçtan uca test edildi.

**Playwright doğrulaması (kullanıcı talebiyle, ikinci tur):** Node + Chromium ile 58 otomatik kontrol — başlangıç ekranı öğeleri, 3 viewport'ta (mobil/tablet/masaüstü) responsive/overflow kontrolü, tüm butonlar (mute, info, başla, önceki/sonraki, restart, ses butonları), Başla→ilk soru, süre sayacı geri sayımı, son-5-saniye `is-low` pulse animasyonu, doğru/yanlış cevap akışları, can sistemi, önceki/sonraki inceleme modu, sonuç ekranı (normal bitiş + Game Over), placeholder/gerçek görsel render, admin başlangıç ekranı ayarlarının anlık yansıması, konsol hataları ve network 404/500 taraması. **58/58 geçti, 0 konsol hatası, 0 network hatası.** İlk turda 2 sorun bulundu ve düzeltildi: (1) gerçek uygulama hatası — kısa süreli sorularda `is-low` sınıfı ilk saniye tick'ine kadar gecikiyordu (`startTimer()` artık başlangıç anında da kontrol ediyor); (2) test script'inde `button[type="submit"]` seçicisi admin layout'taki "Çıkış Yap" butonuyla çakışıyordu (rol-tabanlı seçiciyle düzeltildi, uygulama kodu etkilenmedi).

## Faz 6 — Ses Sistemi

- [ ] Howler.js entegrasyonu, AudioManager
- [ ] Queue
- [ ] Preload
- [ ] Cache
- [ ] Fade In / Fade Out
- [ ] Mute / Volume
- [ ] Ses öncelikleri (priority)

## Faz 7 — Puan ve Rozet Sistemi

- [ ] Puan (+10 doğru cevapta, admin değiştirebilir)
- [ ] Rozet (başlık/açıklama/resim/ses/animasyon/puan/koşul, admin sınırsız oluşturabilir)
- [ ] Doğru cevap mesajları (yazı+ses+animasyon, admin sınırsız oluşturabilir, rastgele gösterilir)
- [ ] Yanlış cevap mesajları (yazı+ses+animasyon, admin sınırsız oluşturabilir, rastgele gösterilir)
- [ ] Geçiş mesajları (soru arası, yazı+ses+animasyon, rastgele gösterilir)

## Faz 8 — Medya ve Ayarlar

- [ ] Ses yönetimi (admin panel modülü)
- [ ] Görsel yönetimi (admin panel modülü)
- [ ] Site ayarları — Faz 3'te site URL, Faz 4'te varsayılan süre/puan için minimal başlatıldı; burada tam modül (genel ayarlar, yedekleme, loglar dahil admin menüsü) tamamlanacak
- [ ] Dil altyapısı (bkz. Faz 10 — çoklu dil burada temel altyapı, Faz 10'da genişletilecek)

## Faz 9 — Optimizasyon

- [ ] Performans
- [ ] Güvenlik
- [ ] Responsive kontrolleri
- [ ] Son testler
- [ ] Production hazırlığı

## Faz 10 — Çoklu Dil Altyapısı

- [ ] Çeviri/i18n altyapısı (admin panel + oyun arayüzü)

## Faz 11 — Kart Paketleri

- [ ] Lisansları/soru modüllerini paketler halinde gruplama

## Faz 12 — İlerleme ve Kaldığı Yerden Devam

- [ ] Oyuncu ilerleme kaydı
- [ ] Kaldığı yerden devam etme

## Faz 13 — PWA / Offline Altyapı

- [ ] Service worker, manifest, offline oynama desteği

## Faz 14 — Analytics Altyapısı

- [ ] Chart.js ile admin dashboard istatistikleri
- [ ] Kullanım/etkileşim analitiği

## Faz 15 — Medya Optimizasyonu

- [ ] Toplu medya yükleme
- [ ] Ses önizleme
- [ ] WebP optimizasyonu (otomatik dönüştürme)
- [ ] MP3 / OGG desteği genişletme
