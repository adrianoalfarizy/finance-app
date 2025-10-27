# Currency formatting & normalization changes

- Added `resources/js/currency.js` and imported in `resources/js/app.js`.
- Converted money inputs to `type="text" inputmode="numeric"` with class `js-currency`.
- Added middleware `NormalizeCurrencyInputs` and registered it in web group to normalize inputs server-side.
- Build assets with `npm run dev` or `npm run build`.

## Files modified:
- resources/js/currency.js: Add currency.js (auto-format Rupiah + guard for number inputs)
- resources/views/debts/create.blade.php: Normalize currency inputs to text + inputmode + js-currency
- resources/views/debts/index.blade.php: Normalize currency inputs to text + inputmode + js-currency
- resources/views/savings/create.blade.php: Normalize currency inputs to text + inputmode + js-currency
- resources/views/savings/index.blade.php: Normalize currency inputs to text + inputmode + js-currency
- resources/views/transactions/create.blade.php: Normalize currency inputs to text + inputmode + js-currency
- app/Http/Middleware/NormalizeCurrencyInputs.php: Add NormalizeCurrencyInputs middleware
- app/Http/Kernel.php: Kernel.php not found to register middleware (skipped)
