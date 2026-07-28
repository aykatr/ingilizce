# ROADMAP

Bu proje, her biri kullanıcı onayı ile kapatılan fazlar halinde geliştirilir. Bir faz tamamen bitmeden (kod kontrolü, hata düzeltme, migration, test, README/CHANGELOG güncellemesi) bir sonraki faza geçilmez.

> Faz 3 sonunda proje kapsamı "YIPPEE LEARNING PLATFORM" tam spesifikasyonuyla netleştirildi (Repository+Service mimarisi, Bootstrap 5/QRCode.js frontend, zenginleştirilmiş lisans şeması). Faz 1-3'ün kodu bu mimariye göre revize edildi.
>
> Faz 4 tasarım kararlarıyla birlikte eski "Faz 5 — Seçenek Sistemi" içeriği (4 seçenek yönetimi, seçenek medya yükleme) Faz 4'ün kapsamına dahil edildi ve fazlar buna göre yeniden numaralandırıldı: eski Faz 6 → yeni Faz 5, eski Faz 7 → yeni Faz 6, ... eski Faz 16 → yeni Faz 15.
>
> Faz 8 kapsamı netleştirilirken bu projede çoklu dil desteği olmayacağına karar verildi (oyun içeriği zaten admin panelinden İngilizce girildiği için ayrı bir i18n/çeviri altyapısına ihtiyaç yok, yönetim paneli sadece Türkçe kalacak) — eski "Faz 10 — Çoklu Dil Altyapısı" kaldırıldı ve sonraki fazlar buna göre yeniden numaralandırıldı: eski Faz 11 → yeni Faz 10, eski Faz 12 → yeni Faz 11, eski Faz 13 → yeni Faz 12, eski Faz 14 → yeni Faz 13, eski Faz 15 → yeni Faz 14.

## Durum

| Faz | Konu | Durum |
|---|---|---|
| 1 | Proje Altyapısı | ✅ Tamamlandı |
| 2 | Admin Paneli | ✅ Tamamlandı |
| 3 | Lisans Sistemi | ✅ Tamamlandı (genişletilmiş kapsam) |
| 4 | Soru Modülü (Seçenek Sistemi dahil) | ✅ Tamamlandı |
| 5 | Oyun Motoru | ✅ Tamamlandı |
| 6 | Ses Sistemi | ✅ Tamamlandı |
| 7 | Puan ve Rozet Sistemi | ✅ Tamamlandı |
| 8 | Medya Kütüphanesi, Site Ayarları, Audit Log ve DB Yedekleme | ✅ Tamamlandı |
| 9 | Optimizasyon | ⏳ Bekliyor |
| 10 | Kart Paketleri | ⏳ Bekliyor |
| 11 | İlerleme ve Kaldığı Yerden Devam | ⏳ Bekliyor |
| 12 | PWA / Offline Altyapı | ⏳ Bekliyor |
| 13 | Analytics Altyapısı | ⏳ Bekliyor |
| 14 | Medya Optimizasyonu (toplu yükleme, önizleme, WebP, MP3/OGG) | ⏳ Bekliyor |

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

## Faz 6 — Ses Sistemi ✅

Kapsam kullanıcı tarafından net çizildi: **yalnızca profesyonel bir AudioManager** geliştirildi (oyun ekranı/UI değişikliği yok). Howler.js üzerine kurulu, uygulamada hiçbir yerde doğrudan `new Howl()` çağrılmıyor — tek giriş noktası `App\Core` değil, istemci tarafı `window.AudioManager` singleton'ı.

- [x] Howler.js entegrasyonu, `AudioManager` sınıfı (`assets/js/audio-manager.js`)
- [x] Play / Stop / Pause / Resume / Replay
- [x] Queue — düşük öncelikli istekler mevcut ses bitene kadar sıraya alınır, bitince otomatik oynar
- [x] Preload / Cache — `Map<url, Howl>` ile aynı ses tekrar istenirse yeniden indirilmez
- [x] Fade In / Fade Out — öncelik kesintisinde 180ms fade-out, `fadeIn` opsiyonuyla yumuşak giriş
- [x] Volume / Mute / Unmute — `Howler.volume()/mute()` üzerinden global
- [x] Ses önceliği (priority) — kategori bazlı varsayılan öncelikler + istek bazında override; eşit veya yüksek öncelik anında keser, düşük öncelik sıraya alınır
- [x] Aynı sesin üst üste binmesini engelleme — aynı url zaten çalıyorsa yeni `play()` isteği yok sayılır (cache + tek-slot mantığı)
- [x] Mobil uyumluluk — Howler'ın kendi unlock mekanizması + `prime()` ile "Başla" tıklamasında audio context erken açılıyor
- [x] 8 ses kategorisi tanımlı: card, question, option, correct, wrong, transition, badge, ui (correct/wrong/transition/badge için henüz gerçek ses dosyası yok — Faz 7/8'de admin panelden yüklenecek, AudioManager onlara hazır)
- [x] GameEngine minimum entegrasyonu — `playCardAudio()/playQuestionAudio()/playOptionAudio()` + soru değiştiğinde otomatik `preload()`; GameEngine yalnızca AudioManager API'sini çağırıyor, kendi Howl nesnesi oluşturmuyor
- [x] Ses dosyası yolları DB'den geliyor — kod içinde sabit yol yok (mevcut entegrasyon noktaları: kart/soru/seçenek sesi, hepsi `questions`/`question_options` tablolarından)

**Teslim:** Playwright ile 26 otomatik kontrol (play/pause/resume/stop/replay, preload/cache, öncelik kesintisi, queue+otomatik sıradaki, UI kanalı bağımsızlığı, mute/volume, "doğrudan Howl yok" statik kontrolü, GameEngine buton entegrasyonu, otomatik preload, mute butonunun gerçekten Howler'ı susturması) — **26/26 geçti, 0 konsol hatası**. Faz 5 regresyon paketi (58 kontrol) da tekrar çalıştırıldı, hâlâ temiz. Test sırasında bulunan tek gerçek tasarım sorunu düzeltildi: eşit öncelikli farklı bir ses istendiğinde (ör. kullanıcı başka bir hoparlör butonuna tıklaması) artık sıraya alınmak yerine hemen çalıyor (`priority >= current` kesme kuralı) — sıraya alma yalnızca gerçekten düşük öncelikli istekler için geçerli.

## Faz 7 — Puan ve Rozet Sistemi ✅

Puan, başarı mesajları ve rozetler birbirinden bağımsız sistemler olarak geliştirildi (ayrı Service'ler, ayrı tablolar, aralarında doğrudan bağımlılık yok — yalnızca `GameSessionService` her üçünü orkestre ediyor).

- [x] Puan Sistemi — soru bazlı puan (Faz 4'ten beri mevcut), doğru cevapta eklenir, yanlışta kazandırmaz, `GameSessionService` içinde tutulur, sonuç ekranında gösterilir. Bu fazda yeni kod gerekmedi, davranış doğrulandı.
- [x] Başarı Mesajları — `App\Services\AchievementMessageService`, `/admin/messages`, iki grup (Doğru/Yanlış), her mesaj Başlık+Ses+Animasyon Tipi+Aktif/Pasif; oyun sırasında ilgili gruptan aktif bir mesaj rastgele seçilir (`pickRandom()`)
- [x] Rozet Sistemi — `App\Services\BadgeService`, `/admin/badges`, Başlık+Açıklama+Görsel+Ses+Animasyon+Koşul(+Değer)+Aktif/Pasif
- [x] Genişletilebilir koşul motoru — rozet kuralları kod içine sabit yazılmadı: her rozet DB'de bir `condition_type` (+ opsiyonel `condition_value`) taşır, `BadgeService` içindeki bir closure haritası bu tipi değerlendirir. Yeni koşul türü eklemek tek satır (harita + varsa admin select seçeneği); mevcut rozetler/entegrasyon değişmez.
- [x] Örnek koşullar uygulandı: İlk doğru cevap, belirli sayıda doğru cevap, hatasız tamamlama, süre dolmadan tamamlama, belirli puana ulaşma
- [x] Değerlendirme servis katmanında — `GameSessionService` her cevaptan sonra (ve oyun bitişinde) `BadgeService::evaluateNewlyEarned()` çağırır; **GameEngine yalnızca "rozet kazanıldı" bilgisini** (`earnedBadges` JSON alanı) alır, hiçbir koşul mantığı istemcide yok
- [x] Aynı oyun oturumunda tekrar verilmeme — session state'te `awarded_badge_ids`, her değerlendirmede zaten verilmiş rozetler atlanır
- [x] Frontend entegrasyonu — dinamik başarı mesajı metni+sesi (AudioManager `correct`/`wrong` kategorisi) + animasyon; rozet kazanıldığında altın renkli toast bildirimi (görsel+ses `badge` kategorisiyle, sıralı gösterim); sonuç ekranında oturum boyunca kazanılan tüm rozetlerin özeti
- [x] Geçiş Mesajları — `transition_messages` tablosu + `App\Services\TransitionMessageService`, `/admin/transition-messages` CRUD (Başlık+Ses+Animasyon+Aktif/Pasif+rastgele seçim), `AudioManager`'ın `transition` kategorisini kullanır. Her doğru cevapta bir SONRAKİ soru varsa (`isFinished=false`) `GameSessionService::answer()` yanıtına `transitionMessage` eklenir; oyun bitince (son soru) `null` döner. Frontend'de mor renkli bir toast olarak, doğru-cevap bildirimlerinden ~700ms sonra gösterilir (AudioManager'ın öncelik sırası — `transition`=6 < `correct/wrong`=8 — sesin doğal olarak doğru/yanlış sesinden sonra sıraya girmesini sağlar).

**Teslim:** Admin CRUD (mesaj+rozet+geçiş mesajı, doğrulama dahil) curl ile, oyun akışı içinde mesaj seçimi + rozet kazanımı (tekli/çoklu, aynı anda birden fazla rozet) + geçiş mesajı (sonraki soru varken/yokken) + tekrar-vermeme + hatasız/hatalı senaryo ayrımı curl ile uçtan uca doğrulandı. Playwright ile 10 ek kontrol (dinamik mesaj, rozet toast bildirimi, geçiş mesajı toast'ı, sonuç ekranı özet, restart temizliği, rozetsiz oyun sonu) — **10/10 geçti**. Faz 5 (58 kontrol) ve Faz 6 (26 kontrol) regresyon paketleri tekrar çalıştırıldı, hâlâ temiz. Test sırasında `routes/web.php`'de daha önceden var olan iki ölü route (`GameController::state()/question()` — Faz 5 tasarım revizyonunda silinen metotlara işaret ediyorlardı) fark edildi ve temizlendi.

## Faz 8 — Medya Kütüphanesi, Site Ayarları, Audit Log ve DB Yedekleme ✅

- [x] Merkezi Medya Kütüphanesi (`/admin/media-library`) — `media_files` tablosu + `App\Services\MediaLibraryService`. Sayfa her açıldığında `reconcile()` çalışır: `uploads/` altı yeniden taranır, diskte olup tabloda olmayan dosyalar (Faz 1-7'nin tüm mevcut soru/mesaj/rozet/başlangıç-ekranı dosyaları dahil) otomatik indekslenir, diskten silinmiş dosyaların kayıtları temizlenir — ayrı bir backfill migration'a gerek kalmadı. Listele (görsel/ses), arama, tür/kullanım filtresi, önizleme (`<img>`/`<audio controls>`), dosya boyutu+türü, "kullanıldığı yerler" (6 tabloyu tarayan `MediaFileRepository::usages()` — soru/seçenek/başarı mesajı/rozet/geçiş mesajı/başlangıç ekranı ayarı), kullanılmayan dosya rozeti, toplu yükleme (`uploads/media-library/` havuzu), aynı-uzantı dosya değiştirme, silme (kullanımdaysa `ValidationException` ile engellenir — kategori silme korumasıyla aynı desen).
- [x] Mevcut yükleme ekranlarıyla entegrasyon — `assets/js/media-picker.js`: sayfa yüklendiğinde `data-media-picker="image|audio"` işaretli her `<input type="file">`'ın yanına otomatik "Kütüphaneden Seç" butonu ekler (soru formu: kart+soru+4 seçenek = 11 alan; başarı mesajı, rozet ×2, geçiş mesajı, başlangıç ekranı 10 görsel alanı). Seçilen dosya `fetch()`+`Blob`+`DataTransfer` ile gerçek bir `File` nesnesine dönüştürülüp `input.files`'a atanır — mevcut formlar, controller'lar ve `MediaUploadService` hiç değişmeden, sanki kullanıcı bilgisayarından o dosyayı seçmiş gibi çalışır. Doğrudan bilgisayardan yükleme de aynı şekilde çalışmaya devam eder.
- [x] Site ayarları — nav'a Medya Kütüphanesi/Denetim Kaydı/Yedekleme bağlantıları eklendi; mevcut genel ayarlar (site URL, varsayılan süre/puan/can) ve başlangıç ekranı modülleri değişmeden korundu.
- [x] Admin İşlem Logu (Audit Log) — `audit_logs` tablosu + `App\Services\AuditLogService`, `/admin/audit-log` (filtre: işlem türü, arama, tarih aralığı, sayfalama). Giriş/çıkış, tüm CRUD işlemleri (kategori/soru/başarı mesajı/rozet/geçiş mesajı), lisans oluşturma/durum değiştirme, site+başlangıç ekranı ayar güncelleme, şifre değiştirme ve medya işlemleri (yükleme/değiştirme/silme) kayıt altına alınır — her admin controller'da ilgili başarı noktasına tek satırlık `$this->auditLog->record(...)` çağrısı eklendi.
- [x] Veritabanı Yedeği Alma — `App\Services\BackupService::generateSql()` PDO ile tüm tabloları (`SHOW CREATE TABLE` + `INSERT` satırları) saf PHP ile dışa aktarır (mysqldump gibi harici bir binary'ye bağımlı değil), `/admin/backup` sayfasından tek tıkla `.sql` dosyası indirilir.

**Kapsam dışı (bilinçli):** PHP/sistem hata logu görüntüleme (sonraki bir sürüme bırakıldı), yedekten geri yükleme (restore), çoklu dil altyapısı (bu projede kapsam dışı — bkz. üstteki not).

**Teslim:** Denetim kaydı (giriş/çıkış + kategori CRUD) ve medya kütüphanesi akışının tamamı (reconcile, liste, kullanım tespiti, kullanımdayken silme koruması, toplu yükleme, aynı-uzantı değiştirme, kullanılmayan dosya silme) curl ile uçtan uca doğrulandı; veritabanı yedeği indirme (`Content-Disposition`, DROP+INSERT satır sayıları, audit log kaydı) curl ile doğrulandı. Playwright ile 11 yeni kontrol (admin girişi, medya kütüphanesi listesi, 3 yeni nav bağlantısı, soru formunda 11 seçici butonu, seçici modal'dan gerçek `File` atama, diğer 3 formda buton varlığı) — **11/11 geçti**. Faz 5 (58), Faz 6/ses (26), Faz 7/başarı-rozet (8) ve geçiş mesajları (2) regresyon paketleri tekrar çalıştırıldı, hepsi temiz — toplam 105/105, 0 konsol hatası, 0 network hatası.

## Kart Seçim Menüsü (Faz 8 sonrası ek modül) ✅

Faz 9'a geçmeden önce istenen mimari revizyon: oyun akışına, tüm soruları tek bir sıralı oturumda oynatmak yerine çocuğun fiziksel kartını seçtiği bir hub ekranı eklendi. Kart=Soru mimarisi, veritabanı şeması ve GameEngine'in temel mekanikleri (cevap/deneme/zaman aşımı/rozet değerlendirme) değişmedi — yalnızca "hangi soru oynanıyor" artık bir kuyruk yerine tek bir seçilen karttan geliyor.

- [x] **Akış**: `play.php?t=TOKEN` → lisans doğrulanır → Başlangıç Ekranı (değişmedi) → "Başla" artık oyunu değil **Kart Seçim Menüsü**'nü açar → çocuk bir kart seçer → o kartın (sorunun) oturumu başlar → kart tamamlanır (doğru cevap ya da can biter) → otomatik olarak Kart Seçim Menüsü'ne dönülür → çocuk istediği kartı tekrar seçebilir. "Ana Menü" (🏠) butonu oyun ekranında her zaman menüye döner (devam eden kart ilerlemesi sayılmadan terk edilir).
- [x] **Kart Seçim Menüsü** (`#screen-menu`, `app/Views/play/index.php`) — aktif sorular (`QuestionRepository::activeOrdered()`, mevcut `sort_order`'a göre) kart görseli (mevcut `card_image`, ayrıca yönetilmez) + başlıkla listelenir; tamamlanan kartlar ✓ rozetiyle işaretlenir; Toplam Puan/Tamamlanan Kart Sayısı/Toplam Rozet istatistik satırı gösterilir.
- [x] **Can — kart bazlı bağımsız**: Her kart seçimi (`GameSessionService::start(int $questionId)`) canı sıfırdan başlatır; bir kartta can biterse yalnızca o kart için oyun biter (`game_over`), diğer kartlar etkilenmez, aynı kart tekrar seçilebilir.
- [x] **Skor/Rozet — ziyaret boyunca kalıcı**: Yeni `App\Services\MenuProgressService` (`$_SESSION['menu_progress']`, ayrı bir session anahtarı) toplam puanı, tamamlanan kart id'lerini ve verilmiş rozet id'lerini kartlar arası kalıcı tutar. Rozet dedup artık kart-bazlı değil ziyaret-bazlı — aynı rozet farklı kartlarda tekrar verilmez. **Önemli düzeltme**: rozet değerlendirme bağlamındaki `correctCount`/`score` de bu ziyaret-boyu toplamlardan okunacak şekilde değiştirildi (`GameSessionService::evaluateBadges()`) — aksi halde "N doğru cevap"/"X puana ulaşma" gibi koşullar tek-soruluk kart oturumunda hiçbir zaman sağlanamazdı.
- [x] **Menü Yönetimi** (`/admin/settings/menu`) — Genel (başlık/açıklama/arka plan görseli) ve Görünüm (kolon sayısı/kart boyutu/boşluk/köşe yuvarlaklığı, CSS custom property'lerle uygulanır) `settings` tablosu üzerinden (`App\Services\MenuSettingsService`, `StartScreenService` ile birebir aynı desen) — **yeni bir tablo/kolon eklenmedi**. Kartlar tablosu: mevcut "Sıra" (`sort_order`) ve "Aktif" (`is_active`, "Menüde Göster" olarak yeniden kullanılır) alanlarını toplu düzenleyen bir arayüz (`QuestionService::updateOrder()`) — **"Menü Sırası" adında yeni bir alan oluşturulmadı**, açıkça istenen kısıtlamaydı. Kart görselleri her zaman mevcut "Kart Görseli"nden gelir, menü için ayrıca görsel yüklenmez.
- [x] **GameEngine minimum değişiklik**: `GameEngine.start(questionId)` yalnızca seçilen kartın ID'sini sunucuya iletir; cevap/zaman aşımı/geçmiş/rozet biriktirme mantığı tek satır değişmeden kaldı. Sunucu tarafında `start()` artık tek-elemanlı bir soru kuyruğu kuruyor — `answer()`/`currentPayload()` bunu jenerik bir dizi olarak zaten ele aldığı için ek bir dallanma gerekmedi.
- [x] **Sonuç Ekranı korundu, kullanılmıyor**: Kullanıcı isteği üzerine mevcut `#screen-result`/`renderResult()`/`renderResultBadges()` kodu **silinmedi**, ileride farklı bir oyun modu (ör. tüm kartları sıralı oynatan bir "maraton modu") için modüler olarak duruyor; yeni kart-hub akışı hiçbir zaman bu ekranı tetiklemiyor.

**Bilinen kapsam dışı yan etki (kullanıcıya bildirildi):** Geçiş Mesajları modülü (bkz. yukarıdaki "Geçiş Mesajları" bölümü) artık pratikte tetiklenmiyor — `transitionMessage` yalnızca "sıradaki soru aynı oturumda" durumunda anlamlıydı, ama artık her kart oturumu tam olarak bir soru içeriyor, yani bir kart bittiğinde her zaman `isFinished=true` oluyor. Kod ve admin CRUD (`/admin/transition-messages`) kasıtlı olarak dokunulmadan bırakıldı (silme istenmedi), ama şu an admin bu ekrandan mesaj girse bile oyun içinde görünmeyecek.

**Teslim:** Kart seçimi, can bağımsızlığı, ziyaret-boyu skor/tamamlanan-kart/rozet birikimi, çapraz-kart rozet koşulları (`correct_count`, `score_reached` — daha önce imkânsızdı, düzeltildi), can bitince/Ana Menü ile terk edilince tamamlanma sayılmaması, aynı kartın tekrar oynanabilmesi curl ile uçtan uca doğrulandı. Admin Menü Yönetimi (genel+görünüm ayarları, kart sırası/görünürlük toplu güncelleme) curl ve gerçek tarayıcı (Türkçe karakter girişi) ile doğrulandı. Playwright ile 15 yeni kontrol (`test-card-menu.js`) — **15/15 geçti**. AudioManager entegrasyon testi (`test-audio.js`, 26 kontrol) yeni akışa uyarlanarak tekrar çalıştırıldı, hâlâ temiz.

## Faz 9 — Optimizasyon

- [ ] Performans
- [ ] Güvenlik
- [ ] Responsive kontrolleri
- [ ] Son testler
- [ ] Production hazırlığı

## Faz 10 — Kart Paketleri

- [ ] Lisansları/soru modüllerini paketler halinde gruplama

## Faz 11 — İlerleme ve Kaldığı Yerden Devam

- [ ] Oyuncu ilerleme kaydı
- [ ] Kaldığı yerden devam etme

## Faz 12 — PWA / Offline Altyapı

- [ ] Service worker, manifest, offline oynama desteği

## Faz 13 — Analytics Altyapısı

- [ ] Chart.js ile admin dashboard istatistikleri
- [ ] Kullanım/etkileşim analitiği

## Faz 14 — Medya Optimizasyonu

- [ ] Toplu medya yükleme
- [ ] Ses önizleme
- [ ] WebP optimizasyonu (otomatik dönüştürme)
- [ ] MP3 / OGG desteği genişletme
