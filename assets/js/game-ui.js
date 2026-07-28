document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const audio = window.AudioManager;
    const engine = new GameEngine({ csrfToken, audioManager: audio });

    const screens = {
        start: document.getElementById('screen-start'),
        menu: document.getElementById('screen-menu'),
        game: document.getElementById('screen-game'),
        result: document.getElementById('screen-result'),
    };

    let timerInterval = null;
    let timeLeft = 0;

    const ANIMATIONS = {
        bounce: (el) => gsap.fromTo(el, { y: 0 }, { y: -14, duration: 0.22, yoyo: true, repeat: 3, ease: 'power1.inOut' }),
        pulse: (el) => gsap.fromTo(el, { scale: 1 }, { scale: 1.15, duration: 0.25, yoyo: true, repeat: 3, ease: 'power1.inOut' }),
        shake: (el) => gsap.fromTo(el, { x: -8 }, { x: 8, duration: 0.08, yoyo: true, repeat: 5, ease: 'power1.inOut', onComplete: () => gsap.set(el, { x: 0 }) }),
        pop: (el) => gsap.fromTo(el, { scale: 0.6, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.35, ease: 'back.out(2.5)' }),
        fade: (el) => gsap.fromTo(el, { opacity: 0 }, { opacity: 1, duration: 0.5 }),
    };

    function playAnimation(el, type) {
        if (!window.gsap) {
            return;
        }

        (ANIMATIONS[type] || ANIMATIONS.pop)(el);
    }

    if (localStorage.getItem('yippee_muted') === '1') {
        audio.mute();
    }

    updateMuteIcons();

    function showScreen(name) {
        Object.values(screens).forEach((el) => el.classList.remove('is-active'));
        screens[name].classList.add('is-active');

        if (window.gsap) {
            gsap.fromTo(screens[name], { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
        }
    }

    function updateMuteIcons() {
        const muted = audio.isMuted();
        const icon = muted ? '🔇' : '🔊';
        document.getElementById('btn-mute-start').textContent = icon;
        document.getElementById('btn-mute-game').textContent = icon;

        const menuIcon = document.querySelector('#btn-mute-menu .menu-icon-circle');
        const menuLabel = document.getElementById('menu-mute-label');
        if (menuIcon) menuIcon.textContent = icon;
        if (menuLabel) menuLabel.textContent = muted ? 'SES KAPALI' : 'SES AÇIK';
    }

    function toggleMute() {
        const muted = audio.toggleMute();
        localStorage.setItem('yippee_muted', muted ? '1' : '0');
        updateMuteIcons();
    }

    document.getElementById('btn-mute-start').addEventListener('click', toggleMute);
    document.getElementById('btn-mute-menu').addEventListener('click', toggleMute);
    document.getElementById('btn-mute-game').addEventListener('click', toggleMute);

    document.getElementById('btn-info').addEventListener('click', () => {
        alert('Yippee! Akıllı Kart Oyunu ile eğlenerek İngilizce öğren.');
    });

    function confirmReturnToMenu() {
        if (confirm('Kart Seçim Menüsü\'ne dönmek istiyor musun? Bu karttaki ilerleme kaybolacak.')) {
            stopTimer();
            returnToMenu();
        }
    }

    document.getElementById('btn-home').addEventListener('click', confirmReturnToMenu);
    document.getElementById('btn-bottom-menu').addEventListener('click', confirmReturnToMenu);

    document.getElementById('btn-start-game').addEventListener('click', () => {
        audio.prime();
        showScreen('menu');
    });

    document.getElementById('btn-home-menu').addEventListener('click', () => {
        showScreen('start');
    });

    document.querySelectorAll('.menu-card').forEach((card) => {
        card.addEventListener('click', async () => {
            card.disabled = true;

            try {
                await engine.start(Number(card.dataset.questionId));
                showScreen('game');
            } catch (error) {
                alert(error.message);
            } finally {
                card.disabled = false;
            }
        });
    });

    document.getElementById('btn-restart').addEventListener('click', () => {
        returnToMenu();
    });

    document.getElementById('btn-bottom-replay').addEventListener('click', async () => {
        if (!engine.lastQuestionId) {
            return;
        }

        stopTimer();

        try {
            await engine.start(engine.lastQuestionId);
        } catch (error) {
            alert(error.message);
        }
    });

    // Dekoratif QR/Lisans Kodu sekmeleri (Giriş Ekranı) — yalnızca görsel, ağa hiçbir istek gitmez.
    document.querySelectorAll('.qr-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.qr-tab').forEach((t) => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            const target = tab.dataset.qrTab;
            document.querySelectorAll('.qr-tab-panel').forEach((panel) => {
                panel.classList.toggle('is-hidden', panel.dataset.qrPanel !== target);
            });
        });
    });

    document.getElementById('btn-question-audio').addEventListener('click', () => {
        engine.playQuestionAudio();
    });

    document.querySelectorAll('.option-audio-btn').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.stopPropagation();
            engine.playOptionAudio(btn.dataset.audioPosition);
        });
    });

    document.querySelectorAll('.option-card').forEach((card) => {
        card.addEventListener('click', async () => {
            const view = engine.getDisplayedQuestion();

            if (view.readonly) {
                return;
            }

            const position = card.dataset.position;

            if (view.attempted.includes(position)) {
                return;
            }

            stopTimer();
            await handleAnswer(position);
        });
    });

    async function handleAnswer(position) {
        try {
            const result = await engine.submitAnswer(position);
            reactToAnswer(result, position);
        } catch (error) {
            alert(error.message);
        }
    }

    async function handleTimeout() {
        try {
            const result = await engine.submitTimeout();
            reactToAnswer(result, null);
        } catch (error) {
            alert(error.message);
        }
    }

    function reactToAnswer(result, position) {
        const message = result.message;

        if (result.correct) {
            const card = document.getElementById('option-' + position);
            card.classList.add('is-correct');
            disableOtherOptions(position);
            showToast(message?.title || 'Harika!', true, message?.animationType);
            floatScore(result.scoreDelta);

            if (message?.audio) {
                audio.play(message.audio, { category: 'correct' });
            }

            if (window.gsap) {
                gsap.fromTo(card, { scale: 1 }, { scale: 1.06, duration: 0.18, yoyo: true, repeat: 1, ease: 'power1.inOut' });
            }
        } else {
            if (position) {
                const card = document.getElementById('option-' + position);
                card.classList.add('is-wrong', 'is-disabled');

                if (window.gsap) {
                    gsap.fromTo(card, { x: -6 }, { x: 0, duration: 0.4, ease: 'elastic.out(1, 0.35)' });
                }
            }

            showToast(message?.title || (result.gameOver ? 'Oyun Bitti' : 'Tekrar Dene'), false, message?.animationType);

            if (message?.audio) {
                audio.play(message.audio, { category: 'wrong' });
            }
        }

        renderHearts(result.lives, result.maxLives);
        document.getElementById('score-label').textContent = result.score;
        showEarnedBadges(result.earnedBadges);

        if (result.correct && result.transitionMessage) {
            setTimeout(() => showTransitionToast(result.transitionMessage), 700);
        }

        if (result.correct) {
            setTimeout(() => {
                if (engine.finished) {
                    returnToMenu();
                } else {
                    renderQuestion();
                }
            }, 1200);
        } else if (result.gameOver) {
            setTimeout(() => {
                returnToMenu();
            }, 1200);
        }
    }

    /** Kart tamamlanınca (veya "Ana Menü" ile terk edilince) Kart Seçim Menüsü'ne döner. */
    function returnToMenu() {
        stopTimer();

        if (engine.menuProgress) {
            document.getElementById('menu-total-score').textContent = engine.menuProgress.totalScore;
            document.getElementById('menu-completed-count').textContent = engine.menuProgress.completedCount;
            document.getElementById('menu-total-badges').textContent = engine.menuProgress.totalBadges;

            const totalCardsEl = document.getElementById('menu-total-cards');
            const totalCards = totalCardsEl ? Number(totalCardsEl.textContent) : 0;
            const pct = totalCards ? Math.round((engine.menuProgress.completedCount / totalCards) * 100) : 0;

            const pctEl = document.getElementById('menu-progress-pct');
            const fillEl = document.getElementById('menu-progress-fill');
            const captionEl = document.getElementById('menu-progress-caption-count');
            if (pctEl) pctEl.textContent = pct + '%';
            if (fillEl) fillEl.style.width = pct + '%';
            if (captionEl) captionEl.textContent = engine.menuProgress.completedCount + ' / ' + totalCards;
        }

        if (engine.finished && !engine.gameOver && engine.lastQuestionId) {
            const card = document.querySelector('.menu-card[data-question-id="' + engine.lastQuestionId + '"]');
            card?.classList.add('is-completed');
        }

        showScreen('menu');
    }

    function disableOtherOptions(exceptPosition) {
        ['A', 'B', 'C', 'D'].forEach((pos) => {
            if (pos !== exceptPosition) {
                document.getElementById('option-' + pos).classList.add('is-disabled');
            }
        });
    }

    function showToast(text, correct, animationType) {
        const toast = document.getElementById('feedback-toast');
        toast.textContent = text;
        toast.classList.remove('is-correct', 'is-wrong');
        toast.classList.add(correct ? 'is-correct' : 'is-wrong');

        if (window.gsap) {
            gsap.killTweensOf(toast);
            gsap.set(toast, { opacity: 1, scale: 1, x: 0, y: 0 });
            playAnimation(toast, animationType || (correct ? 'pop' : 'shake'));
            gsap.to(toast, { opacity: 0, duration: 0.3, delay: 1.1 });
        } else {
            toast.style.opacity = 1;
            setTimeout(() => {
                toast.style.opacity = 0;
            }, 1200);
        }
    }

    function showEarnedBadges(badges) {
        if (!Array.isArray(badges) || badges.length === 0) {
            return;
        }

        badges.forEach((badge, i) => {
            setTimeout(() => showBadgeToast(badge), i * 1600);
        });
    }

    function showBadgeToast(badge) {
        const el = document.createElement('div');
        el.className = 'badge-toast';

        const media = document.createElement(badge.image ? 'img' : 'div');
        if (badge.image) {
            media.src = badge.image;
            media.alt = '';
        } else {
            media.className = 'badge-toast-icon';
            media.textContent = '🏅';
        }

        const text = document.createElement('div');
        const label = document.createElement('div');
        label.className = 'badge-toast-label';
        label.textContent = 'Rozet Kazandın!';
        const title = document.createElement('div');
        title.className = 'badge-toast-title';
        title.textContent = badge.title;
        text.appendChild(label);
        text.appendChild(title);

        el.appendChild(media);
        el.appendChild(text);
        document.body.appendChild(el);

        if (badge.audio) {
            audio.play(badge.audio, { category: 'badge' });
        }

        if (window.gsap) {
            gsap.set(el, { opacity: 0, y: -20 });
            gsap.to(el, { opacity: 1, y: 0, duration: 0.35, ease: 'back.out(2)' });
            playAnimation(media, badge.animationType || 'pop');
            gsap.to(el, { opacity: 0, y: -20, duration: 0.35, delay: 1.6, onComplete: () => el.remove() });
        } else {
            setTimeout(() => el.remove(), 2000);
        }
    }

    function showTransitionToast(message) {
        const el = document.createElement('div');
        el.className = 'transition-toast';
        el.textContent = message.title;
        document.body.appendChild(el);

        if (message.audio) {
            audio.play(message.audio, { category: 'transition' });
        }

        if (window.gsap) {
            gsap.set(el, { opacity: 0, y: 10 });
            gsap.to(el, { opacity: 1, y: 0, duration: 0.3 });
            playAnimation(el, message.animationType || 'fade');
            gsap.to(el, { opacity: 0, duration: 0.3, delay: 1.1, onComplete: () => el.remove() });
        } else {
            setTimeout(() => el.remove(), 1400);
        }
    }

    function floatScore(delta) {
        if (!delta) {
            return;
        }

        const el = document.createElement('div');
        el.className = 'score-float';
        el.textContent = '+' + delta;

        const scoreBadge = document.querySelector('.score-badge');
        const rect = scoreBadge.getBoundingClientRect();
        el.style.left = rect.left + 'px';
        el.style.top = rect.top + 'px';
        document.body.appendChild(el);

        if (window.gsap) {
            gsap.fromTo(
                el,
                { opacity: 1, y: 0 },
                { opacity: 0, y: -40, duration: 0.9, ease: 'power1.out', onComplete: () => el.remove() }
            );
        } else {
            setTimeout(() => el.remove(), 900);
        }
    }

    function renderHearts(lives, maxLives) {
        const pill = document.getElementById('hearts-pill');
        pill.innerHTML = '';

        for (let i = 0; i < maxLives; i++) {
            const span = document.createElement('span');
            span.className = 'heart-icon' + (i < lives ? '' : ' is-empty');
            span.textContent = '❤';
            pill.appendChild(span);
        }
    }

    function renderQuestion() {
        const view = engine.getDisplayedQuestion();
        const question = view.question;

        if (!question) {
            return;
        }

        document.getElementById('question-text').textContent = question.questionText;

        const cardImage = document.getElementById('card-image');
        const cardImagePlaceholder = document.getElementById('card-image-placeholder');

        if (question.cardImage) {
            cardImage.src = question.cardImage;
            cardImage.style.display = '';
            cardImagePlaceholder.style.display = 'none';
        } else {
            cardImage.style.display = 'none';
            cardImagePlaceholder.style.display = '';
        }

        ['A', 'B', 'C', 'D'].forEach((pos) => {
            const option = question.options.find((o) => o.position === pos) || {};
            const card = document.getElementById('option-' + pos);
            card.classList.remove('is-correct', 'is-wrong', 'is-disabled');

            document.getElementById('option-' + pos + '-title').textContent = option.title || '';

            const img = document.getElementById('option-' + pos + '-image');
            const imgPlaceholder = document.getElementById('option-' + pos + '-placeholder');

            if (option.image) {
                img.src = option.image;
                img.style.display = '';
                imgPlaceholder.style.display = 'none';
            } else {
                img.style.display = 'none';
                imgPlaceholder.style.display = '';
            }

            if (view.readonly) {
                card.classList.add('is-disabled');

                if (pos === view.correctPosition) {
                    card.classList.add('is-correct');
                }

                if (pos === view.selectedPosition && view.selectedPosition !== view.correctPosition) {
                    card.classList.add('is-wrong');
                }
            } else if (view.attempted.includes(pos)) {
                card.classList.add('is-disabled', 'is-wrong');
            }
        });

        document.getElementById('progress-label').textContent = 'Soru ' + (view.index + 1) + ' / ' + engine.total;

        const completed = view.readonly ? view.index : engine.history.length;
        const pct = engine.total ? Math.round((completed / engine.total) * 100) : 0;
        document.getElementById('progress-fill').style.width = pct + '%';

        document.getElementById('score-label').textContent = engine.score;
        renderHearts(engine.lives, engine.maxLives);

        stopTimer();

        const timerBadge = document.getElementById('timer-badge');

        if (!view.readonly) {
            startTimer(question.duration);
        } else {
            timerBadge.textContent = '--';
            timerBadge.classList.remove('is-low');
        }
    }

    function startTimer(seconds) {
        timeLeft = seconds;
        const badge = document.getElementById('timer-badge');
        badge.textContent = timeLeft;
        badge.classList.toggle('is-low', timeLeft <= 5);

        timerInterval = setInterval(() => {
            timeLeft -= 1;
            badge.textContent = Math.max(timeLeft, 0);
            badge.classList.toggle('is-low', timeLeft <= 5);

            if (timeLeft <= 0) {
                stopTimer();
                handleTimeout();
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function renderResult() {
        stopTimer();
        const heading = document.getElementById('result-heading');
        const sub = document.getElementById('result-sub');

        if (engine.gameOver) {
            heading.textContent = 'Oyun Bitti';
            sub.textContent = engine.history.length + ' / ' + engine.total + ' soru tamamladın.';
        } else {
            heading.textContent = 'Tebrikler!';
            sub.textContent = 'Tüm soruları tamamladın!';
        }

        document.getElementById('result-score').textContent = engine.score;
        renderResultBadges();
    }

    function renderResultBadges() {
        const container = document.getElementById('result-badges');
        container.innerHTML = '';

        if (engine.badges.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'flex';

        engine.badges.forEach((badge) => {
            const item = document.createElement('div');
            item.className = 'result-badge-item';

            const media = document.createElement(badge.image ? 'img' : 'div');
            if (badge.image) {
                media.src = badge.image;
                media.alt = '';
            } else {
                media.className = 'result-badge-icon';
                media.textContent = '🏅';
            }

            const title = document.createElement('div');
            title.className = 'result-badge-title';
            title.textContent = badge.title;

            item.appendChild(media);
            item.appendChild(title);
            container.appendChild(item);
        });
    }

    engine.addEventListener('question', renderQuestion);
});
