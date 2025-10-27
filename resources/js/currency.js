// Lightweight Rupiah formatter: 2000000 -> 2.000.000
// Works on every keystroke without breaking input (no Intl.NumberFormat during typing).
// Usage: <input type="text" inputmode="numeric" class="js-currency" data-decimals="0">
(function () {
  function group(digits) {
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function attach(el) {
    if (el.type === 'number') {
      el.type = 'text';
      el.setAttribute('inputmode', 'numeric');
    }
    const decimals = parseInt(el.dataset.decimals ?? '0', 10);

    function fmt() {
      let v = (el.value ?? '') + '';
      // detect decimal separator if decimals>0 (we display comma for decimals)
      let sepIndex = -1;
      if (decimals > 0) {
        const iComma = v.indexOf(',');
        const iDot = v.indexOf('.');
        if (iComma >= 0 && (iDot < 0 || iComma < iDot)) sepIndex = iComma;
        else if (iDot >= 0) sepIndex = iDot;
      }

      let intPart = v, fracPart = '';
      if (sepIndex >= 0) {
        intPart = v.slice(0, sepIndex);
        fracPart = v.slice(sepIndex + 1);
      }
      intPart = intPart.replace(/\D/g, '');
      if (decimals > 0) {
        fracPart = (fracPart || '').replace(/\D/g, '').slice(0, decimals);
      }

      if (!intPart && !fracPart) { el.value = ''; return; }

      const intFormatted = group(intPart);
      el.value = (decimals > 0 && fracPart) ? (intFormatted + ',' + fracPart) : intFormatted;
    }

    // init
    fmt();

    el.addEventListener('input', () => {
      const end = document.activeElement === el;
      fmt();
      if (end) {
        try { el.setSelectionRange(el.value.length, el.value.length); } catch (e) {}
      }
    });

    el.addEventListener('blur', fmt);

    // Before submit: send plain numeric (remove thousands, comma -> dot)
    el.form && el.form.addEventListener('submit', () => {
      let v = (el.value ?? '') + '';
      v = v.replace(/\./g, '');
      v = v.replace(',', '.');
      el.value = v;
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input.js-currency, input[name*="amount"], input[name*="_amount"]').forEach(attach);
    console.log('Rupiah formatter ready');
  });
})();