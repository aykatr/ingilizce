(function () {
    let modalEl = null;
    let bsModal = null;
    let activeInput = null;
    let activeType = 'image';
    let searchTimer = null;

    function ensureModal() {
        if (modalEl) {
            return;
        }

        modalEl = document.createElement('div');
        modalEl.className = 'modal fade';
        modalEl.tabIndex = -1;
        modalEl.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kütüphaneden Seç</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control form-control-sm mb-3" placeholder="Ara..." data-picker-search>
                        <div class="row g-2" data-picker-grid></div>
                        <p class="text-muted small d-none" data-picker-empty>Dosya bulunamadı.</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalEl);
        bsModal = new bootstrap.Modal(modalEl);

        modalEl.querySelector('[data-picker-search]').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadFiles(this.value), 250);
        });
    }

    function loadFiles(query) {
        const grid = modalEl.querySelector('[data-picker-grid]');
        const empty = modalEl.querySelector('[data-picker-empty]');
        grid.innerHTML = '';

        const params = new URLSearchParams({ type: activeType, q: query || '' });

        fetch(window.location.origin + '/admin/media-library/api/list?' + params.toString())
            .then((res) => res.json())
            .then((files) => {
                empty.classList.toggle('d-none', files.length > 0);

                files.forEach((file) => {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-md-3';

                    const preview = activeType === 'image'
                        ? `<img src="${file.url}" alt="" class="w-100" style="height: 80px; object-fit: contain;">`
                        : `<audio controls src="${file.url}" style="width: 100%;"></audio>`;

                    col.innerHTML = `
                        <button type="button" class="btn btn-outline-secondary w-100 p-1 text-start" data-picker-item>
                            ${preview}
                            <div class="small text-truncate mt-1">${file.original_name || ''}</div>
                        </button>
                    `;

                    col.querySelector('[data-picker-item]').addEventListener('click', () => selectFile(file));
                    grid.appendChild(col);
                });
            });
    }

    function selectFile(file) {
        fetch(file.url)
            .then((res) => res.blob())
            .then((blob) => {
                const name = file.original_name || file.path.split('/').pop();
                const picked = new File([blob], name, { type: blob.type });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(picked);
                activeInput.files = dataTransfer.files;
                activeInput.dispatchEvent(new Event('change', { bubbles: true }));
                bsModal.hide();
            });
    }

    function open(input, type) {
        activeInput = input;
        activeType = type;
        ensureModal();
        modalEl.querySelector('[data-picker-search]').value = '';
        loadFiles('');
        bsModal.show();
    }

    function decorate(input) {
        if (input.dataset.mediaPickerReady) {
            return;
        }
        input.dataset.mediaPickerReady = '1';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary mt-1 d-block';
        btn.textContent = 'Kütüphaneden Seç';
        btn.addEventListener('click', () => open(input, input.dataset.mediaPicker));
        input.insertAdjacentElement('afterend', btn);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type=file][data-media-picker]').forEach(decorate);
    });
})();
