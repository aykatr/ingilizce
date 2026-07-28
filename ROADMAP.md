# ROADMAP

Bu proje, her biri kullanıcı onayı ile kapatılan fazlar halinde geliştirilir. Bir faz tamamen bitmeden (kod kontrolü, hata düzeltme, migration, test, README/CHANGELOG güncellemesi) bir sonraki faza geçilmez.

## Durum

| Faz | Konu | Durum |
|---|---|---|
| 1 | Proje Altyapısı | ✅ Tamamlandı |
| 2 | Admin Paneli | ✅ Tamamlandı |
| 3 | Lisans Sistemi | ✅ Tamamlandı |
| 4 | Soru Modülü | ⏳ Onay bekleniyor |
| 5 | Seçenek Sistemi | ⏳ Bekliyor |
| 6 | Oyun Motoru | ⏳ Bekliyor |
| 7 | Ses Sistemi | ⏳ Bekliyor |
| 8 | Puan ve Rozet Sistemi | ⏳ Bekliyor |
| 9 | Medya ve Ayarlar | ⏳ Bekliyor |
| 10 | Optimizasyon | ⏳ Bekliyor |

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

- [x] Admin giriş (kimlik doğrulama mantığı) — `admins` tablosu, `password_hash`/`password_verify`, CSRF korumalı form
- [x] Şifre değiştirme — mevcut şifre doğrulama + minimum 8 karakter kuralı
- [x] Dashboard — `GET /admin/dashboard`
- [x] Yetkilendirme — `AdminBaseController` route guard, oturumsuz erişimde `/admin/login`'e yönlendirme
- [x] Session yönetimi — `App\Core\Session` (generic) + `App\Core\Auth` (admin auth semantiği), login'de session id regenerate

**Teslim:** Migration (`admins` tablosu) ve seed script ile çalışan admin paneli. Giriş/çıkış, şifre değiştirme ve route koruması `ingilizce.test` üzerinde uçtan uca test edildi.

## Faz 3 — Lisans Sistemi ✅

- [x] Lisans oluşturma — admin panelinden ad girilerek oluşturulur, 32 karakterlik benzersiz token üretilir
- [x] QR üretme — kapsam kullanıcı kararıyla değiştirildi: sistem yalnızca token + oynama linki üretir, QR görseli harici araçla kullanıcı tarafından üretiliyor
- [x] Token doğrulama — `License::findByToken()` + `is_active` kontrolü
- [x] play.php — kök dizinde bağımsız giriş noktası, token doğrular, geçersiz/pasifse 403
- [x] Aktif / Pasif yönetimi — `POST /admin/licenses/{id}/toggle`

**Teslim:** Lisans oluşturma, listeleme, aktif/pasif toggle ve `play.php` token doğrulaması `ingilizce.test` üzerinde uçtan uca test edildi. Lisanslar süresiz (yalnızca aktif/pasif), tüm aktif lisanslar tüm içeriğe erişebiliyor (kategori bazlı kısıtlama yok).

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

- [ ] Oyun ekranı
- [ ] Kart gösterimi
- [ ] Soru akışı
- [ ] Süre
- [ ] Can sistemi
- [ ] Önceki / Sonraki

## Faz 7 — Ses Sistemi

- [ ] AudioManager
- [ ] Queue
- [ ] Preload
- [ ] Cache
- [ ] Fade In
- [ ] Fade Out
- [ ] Mute
- [ ] Ses öncelikleri

## Faz 8 — Puan ve Rozet Sistemi

- [ ] Puan
- [ ] Rozet
- [ ] Doğru cevap mesajları
- [ ] Yanlış cevap mesajları
- [ ] Geçiş mesajları

## Faz 9 — Medya ve Ayarlar

- [ ] Ses yönetimi
- [ ] Görsel yönetimi
- [ ] Site ayarları
- [ ] Dil altyapısı

## Faz 10 — Optimizasyon

- [ ] Performans
- [ ] Güvenlik
- [ ] Responsive kontrolleri
- [ ] Son testler
- [ ] Production hazırlığı
