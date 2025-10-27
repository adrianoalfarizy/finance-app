<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeCurrencyInputs
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Keys yang dianggap sebagai nominal
        $keys = ['amount', 'principal_amount', 'target_amount', 'paid_amount'];

        $all = $request->all();

        foreach ($all as $key => $value) {
            // normalisasi value tunggal
            if (is_string($value) && self::looksLikeCurrencyKey($key)) {
                $all[$key] = self::normalize($value);
                continue;
            }
            // normalisasi array sederhana: amount: [ ... ]
            if (is_array($value) && self::looksLikeCurrencyKey($key)) {
                foreach ($value as $k => $v) {
                    if (is_string($v)) {
                        $value[$k] = self::normalize($v);
                    }
                }
                $all[$key] = $value;
            }
        }

        $request->merge($all);
        return $next($request);
    }

    protected static function looksLikeCurrencyKey(string $key): bool
    {
        return preg_match('/amount|_amount|principal_amount|target_amount|paid_amount/i', $key) === 1;
    }

    protected static function normalize(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        // buang semua char kecuali digit, koma, titik, minus
        $v = preg_replace('/[^\d,.\-]/', '', $v);

        if (strpos($v, ',') !== false && strpos($v, '.') !== false) {
            // titik = ribuan, koma = desimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif (strpos($v, ',') !== false) {
            // hanya koma → koma = desimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            // hanya titik → buang titik ribuan berlebih (sisakan titik desimal terakhir)
            $parts = explode('.', $v);
            if (count($parts) > 2) {
                $last = array_pop($parts);
                $v = implode('', $parts) . '.' . $last;
            }
        }
        return $v;
    }
}