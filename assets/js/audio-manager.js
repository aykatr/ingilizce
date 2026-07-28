/**
 * AudioManager — tek giriş noktalı Howler.js sarmalayıcısı.
 *
 * Uygulamada hiçbir yerde doğrudan `new Howl()` çağrılmaz; tüm ses oynatma
 * işlemleri bu sınıfın API'si üzerinden yapılır. GameEngine ve UI katmanı
 * yalnızca bu API'yi çağırır.
 *
 * Kanallar:
 *  - "main" kanalı: card/question/option/correct/wrong/transition/badge
 *    kategorileri buradan geçer. Aynı anda yalnızca BİR ana ses çalar.
 *    Daha yüksek öncelikli bir ses istendiğinde mevcut ses fade-out ile
 *    kesilir; eşit/düşük öncelikli istekler sıraya (queue) alınır ve mevcut
 *    ses bitince öncelik sırasına göre oynatılır.
 *  - "ui" kategorisi: arayüz sesleri (tık vb.) ana kanalı bloklamaz, kendi
 *    başına çalar; aynı ses zaten çalıyorsa yeniden üst üste bindirilmez.
 */
class AudioManager {
    static Category = {
        CARD: 'card',
        QUESTION: 'question',
        OPTION: 'option',
        CORRECT: 'correct',
        WRONG: 'wrong',
        TRANSITION: 'transition',
        BADGE: 'badge',
        UI: 'ui',
    };

    static Priority = {
        LOW: 1,
        NORMAL: 5,
        HIGH: 10,
        CRITICAL: 20,
    };

    static #DEFAULT_PRIORITY = {
        card: 5,
        question: 5,
        option: 5,
        transition: 6,
        correct: 8,
        wrong: 8,
        badge: 10,
        ui: 3,
    };

    static #INTERRUPT_FADE_MS = 180;

    #cache = new Map();
    #main = { url: null, howl: null, category: null, priority: -Infinity };
    #queue = [];
    #uiPlaying = new Map();
    #muted = false;
    #volume = 1;

    constructor() {
        if (typeof Howl === 'undefined' || typeof Howler === 'undefined') {
            throw new Error('AudioManager: Howler.js yüklenmeden başlatılamaz.');
        }

        this.Category = AudioManager.Category;
        this.Priority = AudioManager.Priority;
    }

    /** Bir sesi indirir/çözer ama çalmaz. Aynı url ikinci kez istenirse cache'ten döner. */
    preload(url) {
        if (!url) {
            return null;
        }

        return this.#getHowl(url);
    }

    /**
     * @param {string} url
     * @param {{category?:string, priority?:number, loop?:boolean, fadeIn?:number, onEnd?:Function, restart?:boolean}} options
     */
    play(url, options = {}) {
        if (!url) {
            return null;
        }

        const category = options.category || AudioManager.Category.UI;
        const priority = options.priority ?? AudioManager.#DEFAULT_PRIORITY[category] ?? AudioManager.Priority.NORMAL;
        const { loop = false, fadeIn = 0, onEnd = null, restart = false } = options;

        if (category === AudioManager.Category.UI) {
            return this.#playUi(url, { fadeIn, onEnd });
        }

        return this.#playMain(url, { category, priority, loop, fadeIn, onEnd, restart });
    }

    replay(url, options = {}) {
        return this.play(url, { ...options, restart: true });
    }

    stop(url) {
        if (!url) {
            return;
        }

        if (this.#main.url === url) {
            this.#clearMain();
            this.#playNextInQueue();
            return;
        }

        const howl = this.#uiPlaying.get(url);
        if (howl) {
            howl.stop();
            this.#uiPlaying.delete(url);
        }
    }

    stopAll() {
        if (this.#main.howl) {
            this.#main.howl.stop();
        }

        this.#main = { url: null, howl: null, category: null, priority: -Infinity };
        this.#queue = [];

        this.#uiPlaying.forEach((howl) => howl.stop());
        this.#uiPlaying.clear();
    }

    pause(url) {
        if (!url) {
            return;
        }

        if (this.#main.url === url && this.#main.howl) {
            this.#main.howl.pause();
            return;
        }

        const howl = this.#uiPlaying.get(url);
        if (howl) {
            howl.pause();
        }
    }

    resume(url) {
        if (!url) {
            return;
        }

        if (this.#main.url === url && this.#main.howl) {
            this.#main.howl.play();
            return;
        }

        const howl = this.#uiPlaying.get(url);
        if (howl) {
            howl.play();
        }
    }

    pauseMain() {
        this.#main.howl?.pause();
    }

    resumeMain() {
        this.#main.howl?.play();
    }

    setVolume(level) {
        this.#volume = Math.min(1, Math.max(0, level));
        Howler.volume(this.#volume);
    }

    getVolume() {
        return this.#volume;
    }

    mute() {
        this.#muted = true;
        Howler.mute(true);
    }

    unmute() {
        this.#muted = false;
        Howler.mute(false);
    }

    toggleMute() {
        this.#muted ? this.unmute() : this.mute();
        return this.#muted;
    }

    isMuted() {
        return this.#muted;
    }

    /** Mobil tarayıcılarda ilk kullanıcı etkileşiminde audio context'i açar. */
    prime() {
        if (Howler.ctx && Howler.ctx.state === 'suspended') {
            Howler.ctx.resume();
        }
    }

    isCached(url) {
        return this.#cache.has(url);
    }

    unload(url) {
        const howl = this.#cache.get(url);
        if (howl) {
            howl.unload();
            this.#cache.delete(url);
        }
    }

    unloadAll() {
        this.stopAll();
        this.#cache.forEach((howl) => howl.unload());
        this.#cache.clear();
    }

    /** Test/gözlem amaçlı: mevcut kanal durumunu döndürür. */
    getState() {
        return {
            main: {
                url: this.#main.url,
                category: this.#main.category,
                priority: this.#main.priority,
                playing: !!(this.#main.howl && this.#main.howl.playing()),
            },
            queue: this.#queue.map((item) => ({ url: item.url, category: item.category, priority: item.priority })),
            uiActive: Array.from(this.#uiPlaying.keys()),
            muted: this.#muted,
            volume: this.#volume,
            cacheSize: this.#cache.size,
        };
    }

    #getHowl(url, { loop = false } = {}) {
        if (this.#cache.has(url)) {
            const howl = this.#cache.get(url);
            howl.loop(loop);
            return howl;
        }

        const howl = new Howl({
            src: [url],
            preload: true,
            loop,
        });

        this.#cache.set(url, howl);
        return howl;
    }

    #playMain(url, { category, priority, loop, fadeIn, onEnd, restart }) {
        if (this.#main.url === url && this.#main.howl) {
            if (restart) {
                this.#main.howl.stop();
                this.#main.howl.play();
                return this.#main.howl;
            }

            if (this.#main.howl.playing()) {
                return this.#main.howl;
            }
        }

        if (this.#main.howl && this.#main.howl.playing()) {
            // Eşit öncelikli farklı bir ses istendiğinde (ör. kullanıcı başka bir hoparlör
            // butonuna tıkladı) yeni istek daha "taze" kullanıcı niyetini temsil eder ve
            // hemen çalar; yalnızca gerçekten DÜŞÜK öncelikli istekler sıraya alınır.
            if (priority >= this.#main.priority) {
                this.#interruptMain();
                this.#startMain(url, category, priority, loop, fadeIn, onEnd);
                return this.#main.howl;
            }

            if (!this.#queue.some((item) => item.url === url)) {
                this.#queue.push({ url, category, priority, loop, fadeIn, onEnd });
                this.#queue.sort((a, b) => b.priority - a.priority);
            }

            return null;
        }

        this.#startMain(url, category, priority, loop, fadeIn, onEnd);
        return this.#main.howl;
    }

    #startMain(url, category, priority, loop, fadeIn, onEnd) {
        const howl = this.#getHowl(url, { loop });
        howl.stop();
        howl.off('end');

        this.#main = { url, howl, category, priority };

        if (!loop) {
            howl.once('end', () => this.#onMainEnded(onEnd));
        }

        if (fadeIn > 0) {
            howl.volume(0);
            howl.play();
            howl.fade(0, this.#volume, fadeIn);
        } else {
            howl.volume(this.#volume);
            howl.play();
        }
    }

    #interruptMain() {
        const { howl } = this.#main;

        if (!howl) {
            return;
        }

        howl.off('end');

        try {
            howl.fade(howl.volume(), 0, AudioManager.#INTERRUPT_FADE_MS);
            howl.once('fade', () => howl.stop());
        } catch {
            howl.stop();
        }
    }

    #clearMain() {
        if (this.#main.howl) {
            this.#main.howl.off('end');
            this.#main.howl.stop();
        }

        this.#main = { url: null, howl: null, category: null, priority: -Infinity };
    }

    #onMainEnded(onEnd) {
        this.#main = { url: null, howl: null, category: null, priority: -Infinity };

        if (onEnd) {
            onEnd();
        }

        this.#playNextInQueue();
    }

    #playNextInQueue() {
        if (this.#queue.length === 0) {
            return;
        }

        const next = this.#queue.shift();
        this.#startMain(next.url, next.category, next.priority, next.loop, next.fadeIn, next.onEnd);
    }

    #playUi(url, { fadeIn, onEnd }) {
        const existing = this.#uiPlaying.get(url);

        if (existing && existing.playing()) {
            return existing;
        }

        const howl = this.#getHowl(url);
        howl.off('end');
        this.#uiPlaying.set(url, howl);

        howl.once('end', () => {
            this.#uiPlaying.delete(url);
            if (onEnd) {
                onEnd();
            }
        });

        if (fadeIn > 0) {
            howl.volume(0);
            howl.play();
            howl.fade(0, this.#volume, fadeIn);
        } else {
            howl.volume(this.#volume);
            howl.play();
        }

        return howl;
    }
}

window.AudioManager = new AudioManager();
