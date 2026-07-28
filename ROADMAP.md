# ROADMAP

Bu proje, her biri kullanıcı onayı ile kapatılan fazlar halinde geliştirilir. Bir faz tamamen bitmeden (kod kontrolü, hata düzeltme, migration, test, README/CHANGELOG güncellemesi) bir sonraki faza geçilmez.

> Faz 3 sonunda proje kapsamı "YIPPEE LEARNING PLATFORM" tam spesifikasyonuyla netleştirildi (Repository+Service mimarisi, Bootstrap 5/QRCode.js frontend, zenginleştirilmiş lisans şeması). Faz 1-3'ün kodu bu mimariye göre revize edildi — aşağıdaki Faz 1-3 bölümleri güncel halini yansıtır.

## Durum

| Faz | Konu | Durum |
|---|---|---|
| 1 | Proje Altyapısı | ✅ Tamamlandı |
| 2 | Admin Paneli | ✅ Tamamlandı |
| 3 | Lisans Sistemi | ✅ Tamamlandı (genişletilmiş kapsam) |
| 4 | Soru Modülü | ⏳ Onay bekleniyor |
| 5 | Seçenek Sistemi | ⏳ Bekliyor |
| 6 | Oyun Motoru | ⏳ Bekliyor |
| 7 | Ses Sistemi | ⏳ Bekliyor |
| 8 | Puan ve Rozet Sistemi | ⏳ Bekliyor |
| 9 | Medya ve Ayarlar | ⏳ Bekliyor |
| 10 | Optimizasyon | ⏳ Bekliyor |
| 11 | Çoklu Dil Altyapısı | ⏳ Bekliyor |
| 12 | Kart Paketleri | ⏳ Bekliyor |
| 13 | İlerleme ve Kaldığı Yerden Devam | ⏳ Bekliyor |
| 14 | PWA / Offline Altyapı | ⏳ Bekliyor |
| 15 | Analytics Altyapısı | ⏳ Bekliyor |
| 16 | Medya Optimizasyonu (toplu yükleme, önizleme, WebP, MP3/OGG) | ⏳ Bekliyor |

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
- [x] Site URL ayarı — `settings` tablosu, `SettingService`, `/admin/settings` (QR/oynama linki bu adresi kullanır, Faz 9'un tam "Site Ayarları" modülünün öncüsü olarak minimal tutuldu)
- [x] Mimari revizyonu — Repository+Service katmanı eklendi (`App\Repositories`, `App\Services`), Admin/License/Setting bu katmanlardan geçiyor
- [x] Frontend revizyonu — tüm admin+public view'lar Bootstrap 5'e (CDN) geçirildi, vanilla JS (`assets/js/license-qr.js`)

**Teslim:** Lisans oluşturma (süreli/süresiz), listeleme (durum rozetleri, aktivasyon/kullanım/cihaz/IP kolonları), QR modal, aktif/pasif toggle, site URL güncelleme ve `play.php?t=...` doğrulama+takip akışı `ingilizce.test` üzerinde uçtan uca test edildi. Tüm aktif lisanslar tüm içeriğe erişebiliyor (kategori bazlı kısıtlama yok — ihtiyaç olursa ayrı bir faz olarak ele alınabilir).

## Faz 4 — Soru Modülü

- [ ] Kategori yönetimi
- [ ] Soru oluşturma
- [ ] Kart görseli
- [ ] Kart sesi
- [ ] İngilizce soru
- [ ] Soru sesi
- [ ] Süre
- [ ] Puan

## Faz 5 — Seçenek Sistemi

- [ ] 4 seçenek
- [ ] Görsel yükleme
- [ ] Ses yükleme
- [ ] Doğru cevap
- [ ] Sıralama

## Faz 6 — Oyun Motoru

- [ ] Oyun ekranı (referans tasarım: üstte logo/ana sayfa/ses/rozet/skor/ilerleme/soru sayısı/süre, ortada soru+ses+kart, altta 2x2 seçenek+ses, en altta önceki/can/sonraki)
- [ ] Kart gösterimi
- [ ] Soru akışı
- [ ] Süre
- [ ] Can sistemi (varsayılan 3, admin değiştirebilir)
- [ ] Önceki / Sonraki

## Faz 7 — Ses Sistemi

- [ ] Howler.js entegrasyonu, AudioManager
- [ ] Queue
- [ ] Preload
- [ ] Cache
- [ ] Fade In / Fade Out
- [ ] Mute / Volume
- [ ] Ses öncelikleri (priority)

## Faz 8 — Puan ve Rozet Sistemi

- [ ] Puan (+10 doğru cevapta, admin değiştirebilir)
- [ ] Rozet (başlık/açıklama/resim/ses/animasyon/puan/koşul, admin sınırsız oluşturabilir)
- [ ] Doğru cevap mesajları (yazı+ses+animasyon, admin sınırsız oluşturabilir, rastgele gösterilir)
- [ ] Yanlış cevap mesajları (yazı+ses+animasyon, admin sınırsız oluşturabilir, rastgele gösterilir)
- [ ] Geçiş mesajları (soru arası, yazı+ses+animasyon, rastgele gösterilir)

## Faz 9 — Medya ve Ayarlar

- [ ] Ses yönetimi (admin panel modülü)
- [ ] Görsel yönetimi (admin panel modülü)
- [ ] Site ayarları — Faz 3'te site URL için minimal başlatıldı, burada tam modül (genel ayarlar, yedekleme, loglar dahil admin menüsü) tamamlanacak
- [ ] Dil altyapısı (bkz. Faz 11 — çoklu dil burada temel altyapı, Faz 11'de genişletilecek)

## Faz 10 — Optimizasyon

- [ ] Performans
- [ ] Güvenlik
- [ ] Responsive kontrolleri
- [ ] Son testler
- [ ] Production hazırlığı

## Faz 11 — Çoklu Dil Altyapısı

- [ ] Çeviri/i18n altyapısı (admin panel + oyun arayüzü)

## Faz 12 — Kart Paketleri

- [ ] Lisansları/soru modüllerini paketler halinde gruplama

## Faz 13 — İlerleme ve Kaldığı Yerden Devam

- [ ] Oyuncu ilerleme kaydı
- [ ] Kaldığı yerden devam etme

## Faz 14 — PWA / Offline Altyapı

- [ ] Service worker, manifest, offline oynama desteği

## Faz 15 — Analytics Altyapısı

- [ ] Chart.js ile admin dashboard istatistikleri
- [ ] Kullanım/etkileşim analitiği

## Faz 16 — Medya Optimizasyonu

- [ ] Toplu medya yükleme
- [ ] Ses önizleme
- [ ] WebP optimizasyonu
- [ ] MP3 / OGG desteği
