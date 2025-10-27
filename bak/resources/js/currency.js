// Auto-format rupiah: 1000000 -> 1.000.000  (locale id-ID)
// Cara pakai: tambahkan class "js-currency" pada input (type="text").
// Opsional: data-decimals="0|2" (default 0)

(function () {
    const nf = (decimals = 0) =>
        new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });

    function normalize(raw) {
        if (raw == null) return '';
        let s = String(raw).trim();

        // buang semua karakter selain digit, titik, koma, minus
        s = s.replace(/[^\d,.\-]/g, '');

        // Jika ada koma & titik sekaligus → anggap titik = pemisah ribu, koma = desimal
        if (s.includes(',') && s.includes('.')) {
            s = s.replace(/\./g, '').replace(',', '.');
        } else if (s.includes(',') && !s.includes('.')) {
            // hanya koma → anggap koma = desimal
            s = s.replace(/\./g, '').replace(',', '.');
        } else {
            // hanya titik → buang pemisah ribu (titik), sisakan titik desimal terakhir
            const parts = s.split('.');
            if (parts.length > 2) {
                const last = parts.pop();
                s = parts.join('') + '.' + last;
            }
        }
        return s;
    }

    function format(val, decimals) {
        if (val === '' || val == null) return '';
        const num = parseFloat(normalize(val));
        if (Number.isNaN(num)) return '';
        return nf(decimals).format(num);
    }

    function attach(el) {
        // JANGAN format input type=number (browser anggap 1.000 tidak valid)
        if (el.type === 'number') {
            console.warn('js-currency: ubah ke type="text" + inputmode="numeric" pada input', el.name);
            return;
        }

        const decimals = parseInt(el.dataset.decimals ?? '0', 10);

        // Format saat load awal (jika ada value)
        if (el.value) el.value = format(el.value, decimals);

        const onInput = () => {
            const caretEnd = document.activeElement === el;
            el.value = format(el.value, decimals);
            if (caretEnd) {
                const len = el.value.length;
                el.setSelectionRange(len, len);
            }
        };

        el.addEventListener('input', onInput);
        el.addEventListener('blur', onInput);

        // sebelum submit, kirim nilai numerik standar
        el.form?.addEventListener('submit', () => {
            el.value = normalize(el.value);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input.js-currency').forEach(attach);
    });
})();
