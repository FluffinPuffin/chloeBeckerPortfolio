window.addEventListener('load', () => {
    // ── Elements ──────────────────────────────────────────────
    const previewOverlay = document.getElementById('pack-preview-overlay');
    const previewEl = document.getElementById('pack-preview');
    const previewName = document.getElementById('preview-name');
    const previewDesc = document.getElementById('preview-desc');

    const modal = document.getElementById('modal');
    const stackWrap = document.getElementById('card-stack-wrap');
    const spreadWrap = document.getElementById('card-spread-wrap');
    const cardEl = document.getElementById('modal-card');
    const cardRarity = document.getElementById('modal-card-rarity');
    const cardTitle = document.getElementById('modal-card-title');
    const cardBody = document.getElementById('modal-card-body');
    const closeBtn = document.getElementById('modal-close-btn');

    // ── Helpers ───────────────────────────────────────────────
    function linkify(text) {
        const escaped = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        return escaped.replace(/https?:\/\/[^\s<>"]+/g, url => `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`);
    }

    // ── State ─────────────────────────────────────────────────
    let cards = [];
    let currentIndex = 0;
    let currentPackEl = null;
    let currentProjId = null;
    let fromTransform = '';
    let flyingBack = false;

    // ── Opened tracking (server session) ──────────────────────
    const packsEl = document.querySelector('.packs');
    const openedPacks = JSON.parse(packsEl?.dataset.opened || '[]');

    function markOpened(projectId) {
        const body = new FormData();
        body.append('project_id', projectId);
        fetch('mark_opened.php', { method: 'POST', body });
    }

    // Apply opened class on load

    document.querySelectorAll('.pack').forEach(pack => {
        if (openedPacks.includes(pack.dataset.projectId)) {

            pack.classList.add('opened');
        }
    });

    // ── Pack grid clicks ──────────────────────────────────────
    document.querySelectorAll('.pack:not(#pack-preview)').forEach(pack => {
        pack.addEventListener('click', () => {
            if (pack.classList.contains('opened')) {
                // Already opened — skip preview, go straight to cards
                currentProjId = pack.dataset.projectId;
                currentPackEl = pack;
                cards = JSON.parse(pack.dataset.cards || '[]');
                if (cards.length > 0) openCards(true);
            } else {
                selectPack(pack);
            }
        });
    });

    function selectPack(packEl) {
        if (flyingBack) return;

        const rect = packEl.getBoundingClientRect();
        previewEl.style.width = rect.width + 'px';
        const cx = window.innerWidth / 2;
        const cy = window.innerHeight / 2;
        const dx = (rect.left + rect.width / 2) - cx;
        const dy = (rect.top + rect.height / 2) - cy;

        fromTransform = `translate(${dx}px, ${dy}px)`;
        currentPackEl = packEl;
        currentProjId = packEl.dataset.projectId;
        cards = JSON.parse(packEl.dataset.cards || '[]');

        previewName.textContent = packEl.querySelector('h2').textContent;
        previewDesc.textContent = packEl.querySelector('.pack-desc')?.textContent || '';

        previewEl.style.transition = 'none';
        previewEl.style.transform = fromTransform;
        previewOverlay.classList.add('active');

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                previewEl.style.transition = '';
                previewEl.style.transform = '';
            });
        });
    }

    // ── Click overlay background → fly pack back ──────────────
    previewOverlay.addEventListener('click', (e) => {
        if (e.target === previewOverlay) flyBack();
    });

    function flyBack() {
        if (flyingBack) return;
        flyingBack = true;

        previewEl.style.transform = fromTransform;

        previewEl.addEventListener('transitionend', function handler() {
            previewEl.removeEventListener('transitionend', handler);
            previewOverlay.classList.remove('active');
            previewEl.style.transform = '';
            flyingBack = false;
        });
    }

    // ── Click centered preview → open cards ───────────────────
    previewEl.addEventListener('click', () => {
        if (flyingBack || cards.length === 0) return;

        markOpened(currentProjId);
        currentPackEl.classList.add('opened');
        previewOverlay.classList.remove('active');
        previewEl.style.transform = '';

        openCards(false);
    });

    // ── Card modal ────────────────────────────────────────────
    function openCards(showAll) {
        currentIndex = 0;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        if (showAll) {
            stackWrap.style.display = 'none';
            showSpread();
        } else {
            stackWrap.style.display = '';
            spreadWrap.classList.remove('active');
            spreadWrap.innerHTML = '';
            showCard(0);
        }
    }

    function showSpread() {
        stackWrap.style.display = 'none';
        spreadWrap.innerHTML = '';
        spreadWrap.classList.add('active');
        cards.forEach((card, i) => {
            const rarity = card.rarity || 'common';
            const wrap = document.createElement('div');
            wrap.className = 'modal-card rarity-' + rarity;
            wrap.style.animationDelay = (i * 0.07) + 's';

            const r = document.createElement('div');
            r.className = 'modal-card-rarity';
            r.textContent = rarity.charAt(0).toUpperCase() + rarity.slice(1);

            const t = document.createElement('div');
            t.className = 'modal-card-title';
            t.textContent = card.title || '';

            const b = document.createElement('div');
            b.className = 'modal-card-body';
            b.innerHTML = linkify(card.body || '');

            wrap.append(r, t, b);
            spreadWrap.appendChild(wrap);
        });
    }

    // Click card stack to advance
    stackWrap.addEventListener('click', advanceCard);

    // Click dark background to close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeCards();
    });

    closeBtn.addEventListener('click', closeCards);

    // ── Swipe to advance (mobile) ──────────────────────────────
    let touchStartX = 0;
    let touchStartY = 0;

    stackWrap.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    stackWrap.addEventListener('touchend', (e) => {
        const dx = touchStartX - e.changedTouches[0].clientX;
        const dy = Math.abs(touchStartY - e.changedTouches[0].clientY);
        if (Math.abs(dx) > 40 && dy < 80) advanceCard();
    }, { passive: true });

    function advanceCard() {
        if (currentIndex < cards.length - 1) {
            showCard(currentIndex + 1);
        } else {
            showSpread();
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (modal.classList.contains('active')) closeCards();
            else if (previewOverlay.classList.contains('active')) flyBack();
        }
        if (e.key === 'ArrowRight' && modal.classList.contains('active')) advanceCard();
    });

    function showCard(index) {
        currentIndex = index;
        const card = cards[index];
        const rarity = card.rarity || 'common';

        cardEl.className = 'modal-card rarity-' + rarity;
        cardEl.style.animation = 'none';
        void cardEl.offsetWidth;
        cardEl.style.animation = '';

        cardRarity.textContent = rarity.charAt(0).toUpperCase() + rarity.slice(1);
        cardTitle.textContent = card.title || '';
        cardBody.innerHTML = linkify(card.body || '');

        // Update stack depth indicators
        const remaining = cards.length - 1 - index;
        stackWrap.classList.remove('stack-last', 'stack-one');
        if (remaining === 0) stackWrap.classList.add('stack-last');
        else if (remaining === 1) stackWrap.classList.add('stack-one');
    }

    function closeCards() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        stackWrap.style.display = '';
        spreadWrap.classList.remove('active');
        spreadWrap.innerHTML = '';
    }
});
