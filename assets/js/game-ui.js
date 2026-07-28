document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const engine = new GameEngine({ csrfToken });

    const screens = {
        start: document.getElementById('screen-start'),
        game: document.getElementById('screen-game'),
        result: document.getElementById('screen-result'),
    };

    let muted = localStorage.getItem('yippee_muted') === '1';
    let timerInterval = null;
    let timeLeft = 0;

    updateMuteIcons();

    function showScreen(name) {
        Object.values(screens).forEach((el) => el.classList.remove('is-active'));
        screens[name].classList.add('is-active');

        if (window.gsap) {
            gsap.fromTo(screens[name], { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
        }
    }

    function playAudio(url) {
        if (!url || muted) {
            return;
        }

        new Audio(url).play().catch(() => {});
    }

    function updateMuteIcons() {
        const icon = muted ? '🔇' : '🔊';
        document.getElementById('btn-mute-start').textContent = icon;
        document.getElementById('btn-mute-game').textContent = icon;
    }

    function toggleMute() {
        muted = !muted;
        localStorage.setItem('yippee_muted', muted ? '1' : '0');
        updateMuteIcons();
    }

    document.getElementById('btn-mute-start').addEventListener('click', toggleMute);
    document.getElementById('btn-mute-game').addEventListener('click', toggleMute);

    document.getElementById('btn-info').addEventListener('click', () => {
        alert('Yippee! Akıllı Kart Oyunu ile eğlenerek İngilizce öğren.');
    });

    document.getElementById('btn-home').addEventListener('click', () => {
        if (confirm('Ana sayfaya dönmek istiyor musun? Mevcut ilerleme kaybolacak.')) {
            stopTimer();
            showScreen('start');
        }
    });

    document.getElementById('btn-start-game').addEventListener('click', async () => {
        const btn = document.getElementById('btn-start-game');
        btn.disabled = true;

        try {
            await engine.start();
            showScreen('game');
        } catch (error) {
            alert(error.message);
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('btn-restart').addEventListener('click', async () => {
        try {
            await engine.start();
            showScreen('game');
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('btn-prev').addEventListener('click', () => {
        stopTimer();
        engine.goToPrevious();
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        engine.goToNext();
    });

    document.getElementById('btn-question-audio').addEventListener('click', () => {
        const view = engine.getDisplayedQuestion();

        if (view.question) {
            playAudio(view.question.questionAudio);
        }
    });

    document.querySelectorAll('.option-audio-btn').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.stopPropagation();

            const position = btn.dataset.audioPosition;
            const view = engine.getDisplayedQuestion();
            const option = view.question?.options.find((o) => o.position === position);

            if (option) {
                playAudio(option.audio);
            }
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
        if (result.correct) {
            const card = document.getElementById('option-' + position);
            card.classList.add('is-correct');
            disableOtherOptions(position);
            showToast('Harika!', true);
            floatScore(result.scoreDelta);

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

            showToast(result.gameOver ? 'Oyun Bitti' : 'Tekrar Dene', false);
        }

        renderHearts(result.lives, result.maxLives);
        document.getElementById('score-label').textContent = result.score;

        if (result.correct) {
            setTimeout(() => {
                if (engine.finished) {
                    renderResult();
                    showScreen('result');
                } else {
                    renderQuestion();
                }
            }, 1200);
        } else if (result.gameOver) {
            setTimeout(() => {
                renderResult();
                showScreen('result');
            }, 1200);
        }
    }

    function disableOtherOptions(exceptPosition) {
        ['A', 'B', 'C', 'D'].forEach((pos) => {
            if (pos !== exceptPosition) {
                document.getElementById('option-' + pos).classList.add('is-disabled');
            }
        });
    }

    function showToast(text, correct) {
        const toast = document.getElementById('feedback-toast');
        toast.textContent = text;
        toast.classList.remove('is-correct', 'is-wrong');
        toast.classList.add(correct ? 'is-correct' : 'is-wrong');

        if (window.gsap) {
            gsap.fromTo(
                toast,
                { opacity: 0, y: 10, scale: 0.85 },
                { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'back.out(2)' }
            );
            gsap.to(toast, { opacity: 0, y: -10, duration: 0.3, delay: 1.0 });
        } else {
            toast.style.opacity = 1;
            setTimeout(() => {
                toast.style.opacity = 0;
            }, 1200);
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

        document.getElementById('btn-prev').disabled = !engine.canGoPrevious();
        document.getElementById('btn-next').disabled = !engine.canGoNext();

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
    }

    engine.addEventListener('question', renderQuestion);
});
