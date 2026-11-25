<?php

if (! function_exists('rupiah')) {
    function rupiah($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (! function_exists('qty_fmt')) {
    function qty_fmt($qty, string $satuan): string
    {
        $q = (float) $qty;
        // ml -> tampil Liter jika >= 1000
        if ($satuan === 'ml' && abs($q) >= 1000) {
            return rtrim(rtrim(number_format($q / 1000, 1, ',', '.'), '0'), ',') . ' L';
        }
        if ($satuan === 'gr' && abs($q) >= 1000) {
            return rtrim(rtrim(number_format($q / 1000, 1, ',', '.'), '0'), ',') . ' kg';
        }
        $formatted = $q == (int) $q ? number_format($q, 0, ',', '.') : number_format($q, 2, ',', '.');
        return $formatted . ' ' . $satuan;
    }
}

if (! function_exists('tgl_indo')) {
    function tgl_indo(?string $datetime): string
    {
        if (! $datetime) return '-';
        $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $ts = strtotime($datetime);
        return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

if (! function_exists('shift_aktif')) {
    /** Ambil shift terbuka milik user yang sedang login */
    function shift_aktif(): ?array
    {
        $shiftId = session()->get('shift_id');
        if (! $shiftId) return null;
        $shift = (new \App\Models\CashierShiftModel())->find($shiftId);
        return ($shift && $shift['status'] === 'open') ? $shift : null;
    }
}

if (! function_exists('badge_stok')) {
    function badge_stok(float $stok, float $min): string
    {
        if ($stok <= 0) return 'bg-danger';
        if ($stok <= $min) return 'bg-warning text-dark';
        return 'bg-success';
    }
}
