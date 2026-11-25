<?php

namespace App\Services;

/**
 * Formatter struk thermal ESC/POS & teks WhatsApp (FR-12).
 * Mendukung lebar 58mm (32 kolom) dan 80mm (48 kolom).
 */
class ReceiptService
{
    public function strukText(array $trx, string $namaToko, string $alamatToko, int $width = 32): string
    {
        $line = str_repeat('=', $width) . "\n";
        $dash = str_repeat('-', $width) . "\n";
        $out  = $line;
        $out .= $this->center($namaToko, $width) . "\n";
        $out .= $this->center($alamatToko, $width) . "\n";
        $out .= $this->center('Susu Kambing Segar', $width) . "\n";
        $out .= $line;
        $out .= 'No  : ' . $trx['no_invoice'] . "\n";
        $out .= 'Tgl : ' . date('d/m/Y H:i', strtotime($trx['created_at'])) . "\n";
        $out .= 'Ksr : ' . ($trx['nama_kasir'] ?? '-') . "\n";
        if (! empty($trx['nama_pelanggan'])) {
            $out .= 'Plg : ' . $trx['nama_pelanggan'] . "\n";
        }
        $out .= $dash;

        foreach ($trx['details'] as $d) {
            $nama = $d['nama_menu'];
            $out .= wordwrap($nama, $width, "\n", true) . "\n";
            $qtyHarga = "  {$d['qty_jual']} x " . $this->rp((float) $d['harga_jual_unit']);
            $out .= $this->twoCol($qtyHarga, $this->rp((float) $d['subtotal_omzet']), $width) . "\n";
        }

        $out .= $dash;
        $out .= $this->twoCol('TOTAL', $this->rp((float) $trx['total_omzet']), $width) . "\n";
        $out .= $this->twoCol('Bayar (' . strtoupper($trx['metode_bayar']) . ')', $this->rp((float) $trx['uang_diterima']), $width) . "\n";
        $out .= $this->twoCol('Kembali', $this->rp((float) $trx['uang_kembali']), $width) . "\n";
        $out .= $line;
        $out .= $this->center('Terima kasih!', $width) . "\n";
        $out .= $this->center('Susu sehat, hati kuat', $width) . "\n\n\n";
        return $out;
    }

    /** Payload ESC/POS untuk Web Bluetooth / RawBT */
    public function strukEscPos(array $trx, string $namaToko, string $alamatToko): string
    {
        $ESC = "\x1B"; $GS = "\x1D";
        $out  = $ESC . '@';                    // init
        $out .= $ESC . 'a' . chr(1);           // center
        $out .= $GS . '!' . chr(0x11);         // double size
        $out .= $namaToko . "\n";
        $out .= $GS . '!' . chr(0x00);
        $out .= $alamatToko . "\n";
        $out .= $ESC . 'a' . chr(0);           // left
        $out .= $this->strukText($trx, $namaToko, $alamatToko, 32);
        $out .= $GS . 'V' . chr(66) . chr(3);  // partial cut
        return $out;
    }

    /** Teks ringkasan untuk WhatsApp (wa.me) */
    public function waText(array $trx, string $namaToko): string
    {
        $t  = "*{$namaToko}*\n";
        $t .= "Nota: {$trx['no_invoice']}\n";
        $t .= 'Tgl : ' . date('d/m/Y H:i', strtotime($trx['created_at'])) . "\n\n";
        foreach ($trx['details'] as $d) {
            $t .= "• {$d['nama_menu']} x{$d['qty_jual']} — " . $this->rp((float) $d['subtotal_omzet']) . "\n";
        }
        $t .= "\n*TOTAL: " . $this->rp((float) $trx['total_omzet']) . "*\n";
        $t .= 'Bayar: ' . strtoupper($trx['metode_bayar']) . "\n\n";
        $t .= "Terima kasih sudah beli susu kambing segar! 🥛";
        return $t;
    }

    private function rp(float $n): string
    {
        return 'Rp' . number_format($n, 0, ',', '.');
    }

    private function center(string $s, int $w): string
    {
        $s = trim($s);
        if (strlen($s) >= $w) return substr($s, 0, $w);
        $pad = intdiv($w - strlen($s), 2);
        return str_repeat(' ', $pad) . $s;
    }

    private function twoCol(string $l, string $r, int $w): string
    {
        $space = $w - strlen($l) - strlen($r);
        if ($space < 1) $space = 1;
        return $l . str_repeat(' ', $space) . $r;
    }
}
