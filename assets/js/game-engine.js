class GameEngine extends EventTarget {
    #csrfToken;
    #audio;

    constructor({ csrfToken, audioManager = null }) {
        super();
        this.#csrfToken = csrfToken;
        this.#audio = audioManager;
        this.score = 0;
        this.lives = 0;
        this.maxLives = 0;
        this.index = 0;
        this.total = 0;
        this.currentQuestion = null;
        this.attempted = [];
        this.history = [];
        this.viewIndex = null;
        this.finished = false;
        this.gameOver = false;
    }

    async #post(url, body = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ _csrf: this.#csrfToken, ...body }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'İstek başarısız oldu.');
        }

        return data;
    }

    async start() {
        this.score = 0;
        this.history = [];
        this.viewIndex = null;
        this.finished = false;
        this.gameOver = false;

        const data = await this.#post('/play/api/start');
        this.#applyPayload(data);
        this.dispatchEvent(new CustomEvent('question'));

        return data;
    }

    #applyPayload(data) {
        this.finished = !!data.finished;
        this.gameOver = !!data.gameOver;
        this.score = data.score;
        this.lives = data.lives;
        this.maxLives = data.maxLives;
        this.total = data.total;

        if (!this.finished) {
            this.index = data.index;
            this.attempted = data.attempted || [];
            this.currentQuestion = data.question;
            this.#preloadCurrentAudio();
        } else {
            this.currentQuestion = null;
        }
    }

    /** Ses dosyalarının yolları her zaman soru verisinden (DB'den) gelir — burada sabit bir yol yoktur. */
    #preloadCurrentAudio() {
        if (!this.#audio || !this.currentQuestion) {
            return;
        }

        const urls = [
            this.currentQuestion.cardAudio,
            this.currentQuestion.questionAudio,
            ...this.currentQuestion.options.map((option) => option.audio),
        ];

        urls.filter(Boolean).forEach((url) => this.#audio.preload(url));
    }

    playCardAudio() {
        const url = this.getDisplayedQuestion().question?.cardAudio;

        if (url) {
            this.#audio?.play(url, { category: 'card' });
        }
    }

    playQuestionAudio() {
        const url = this.getDisplayedQuestion().question?.questionAudio;

        if (url) {
            this.#audio?.play(url, { category: 'question' });
        }
    }

    playOptionAudio(position) {
        const option = this.getDisplayedQuestion().question?.options.find((o) => o.position === position);

        if (option?.audio) {
            this.#audio?.play(option.audio, { category: 'option' });
        }
    }

    isReviewing() {
        return this.viewIndex !== null;
    }

    getDisplayedQuestion() {
        if (this.isReviewing()) {
            const entry = this.history[this.viewIndex];

            return {
                index: entry.index,
                question: entry.question,
                readonly: true,
                selectedPosition: entry.selectedPosition,
                correctPosition: entry.correctPosition,
                attempted: [],
            };
        }

        return {
            index: this.index,
            question: this.currentQuestion,
            readonly: false,
            selectedPosition: null,
            correctPosition: null,
            attempted: this.attempted,
        };
    }

    canGoPrevious() {
        return this.isReviewing() ? this.viewIndex > 0 : this.history.length > 0;
    }

    canGoNext() {
        return this.isReviewing();
    }

    goToPrevious() {
        if (!this.canGoPrevious()) {
            return;
        }

        this.viewIndex = this.isReviewing() ? this.viewIndex - 1 : this.history.length - 1;
        this.dispatchEvent(new CustomEvent('question'));
    }

    goToNext() {
        if (!this.canGoNext()) {
            return;
        }

        this.viewIndex = this.viewIndex < this.history.length - 1 ? this.viewIndex + 1 : null;
        this.dispatchEvent(new CustomEvent('question'));
    }

    async submitAnswer(position) {
        if (this.isReviewing() || this.finished || this.gameOver) {
            return null;
        }

        const data = await this.#post('/play/api/answer', { position });

        return this.#handleAnswerResult(data, position);
    }

    async submitTimeout() {
        if (this.isReviewing() || this.finished || this.gameOver) {
            return null;
        }

        const data = await this.#post('/play/api/timeout');

        return this.#handleAnswerResult(data, null);
    }

    #handleAnswerResult(data, position) {
        if (data.correct) {
            this.history.push({
                index: this.index,
                question: this.currentQuestion,
                selectedPosition: position,
                correctPosition: data.correctPosition,
                correct: true,
            });

            this.score = data.score;
            this.lives = data.lives;
            this.maxLives = data.maxLives;

            if (data.next.finished) {
                this.finished = true;
                this.gameOver = !!data.next.gameOver;
                this.currentQuestion = null;
            } else {
                this.index = data.next.index;
                this.attempted = data.next.attempted || [];
                this.currentQuestion = data.next.question;
                this.#preloadCurrentAudio();
            }
        } else {
            this.attempted = data.attempted || this.attempted;
            this.score = data.score;
            this.lives = data.lives;
            this.maxLives = data.maxLives;
            this.gameOver = !!data.gameOver;

            if (this.gameOver) {
                this.finished = true;
            }
        }

        return data;
    }
}
