document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('qrModal');

    if (!modal) {
        return;
    }

    const qrContainer = document.getElementById('qrCode');
    const qrLink = document.getElementById('qrLink');

    modal.addEventListener('show.bs.modal', (event) => {
        const url = event.relatedTarget?.dataset.qrUrl ?? '';

        qrContainer.innerHTML = '';
        qrLink.textContent = url;

        new QRCode(qrContainer, {
            text: url,
            width: 200,
            height: 200,
        });
    });
});
