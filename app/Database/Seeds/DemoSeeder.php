<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // ═══ 1. OUTLETS ═══
        $this->db->table('outlets')->insertBatch([
            ['id' => 1, 'kode_cabang' => 'PST', 'nama_kios' => 'Gudang Pusat', 'alamat' => 'Jl. Peternakan No. 1', 'is_pusat' => 1, 'status_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'kode_cabang' => 'KIOS-A', 'nama_kios' => 'Kios A - Alun-alun', 'alamat' => 'Jl. Alun-alun No. 5', 'is_pusat' => 0, 'status_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'kode_cabang' => 'KIOS-B', 'nama_kios' => 'Kios B - Kampus', 'alamat' => 'Jl. Kampus Raya No. 12', 'is_pusat' => 0, 'status_aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ═══ 2. USERS (password: password123, PIN owner: 123456) ═══
        $pass = password_hash('password123', PASSWORD_BCRYPT);
        $pin  = password_hash('123456', PASSWORD_BCRYPT);
        $this->db->table('users')->insertBatch([
            ['id' => 1, 'outlet_id' => null, 'nama' => 'Owner Susupreneur', 'username' => 'owner',  'password_hash' => $pass, 'role' => 'owner',  'pin_otorisasi' => $pin, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'outlet_id' => 1,    'nama' => 'Admin Pusat',       'username' => 'admin',  'password_hash' => $pass, 'role' => 'admin',  'pin_otorisasi' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'outlet_id' => 2,    'nama' => 'Kasir Kios A',      'username' => 'kasir',  'password_hash' => $pass, 'role' => 'kasir',  'pin_otorisasi' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'outlet_id' => 3,    'nama' => 'Kasir Kios B',      'username' => 'kasirb', 'password_hash' => $pass, 'role' => 'kasir',  'pin_otorisasi' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ═══ 3. RAW MATERIALS ═══
        $materials = [
            [1, 'SUSU',  'Susu Kambing Segar',       'Susu',              'ml',  2000],
            [2, 'KURMA', 'Sirup Kurma',              'Perasa',            'ml',  300],
            [3, 'JAHE',  'Sari Jahe Merah',          'Perasa',            'ml',  300],
            [4, 'COKLT', 'Sirup Cokelat Premium',    'Perasa',            'ml',  300],
            [5, 'STROB', 'Sirup Stroberi',           'Perasa',            'ml',  300],
            [6, 'KOPI',  'Ekstrak Kopi Arabika',     'Perasa',            'ml',  300],
            [7, 'GULA',  'Gula Aren Cair',           'Pemanis',           'ml',  500],
            [8, 'CUP-S', 'Gelas Cup 12oz',           'Cup',               'pcs', 50],
            [9, 'CUP-M', 'Gelas Cup 14oz',           'Cup',               'pcs', 50],
            [10, 'CUP-L','Gelas Cup 16oz',           'Cup',               'pcs', 50],
            [11, 'LID-S','Tutup Cup 12oz',           'Lid',               'pcs', 50],
            [12, 'LID-M','Tutup Cup 14oz',           'Lid',               'pcs', 50],
            [13, 'LID-L','Tutup Cup 16oz',           'Lid',               'pcs', 50],
            [14, 'SDTN', 'Sedotan Plastik',          'Plastik_Pendukung', 'pcs', 100],
            [15, 'KANTG','Kantong Plastik Tentengan','Plastik_Pendukung', 'pcs', 50],
        ];
        foreach ($materials as $m) {
            $this->db->table('raw_materials')->insert([
                'id' => $m[0], 'kode_bahan' => $m[1], 'nama_item' => $m[2], 'kategori' => $m[3],
                'satuan_kecil' => $m[4], 'stok_minimum' => $m[5], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ═══ 4. STOCK BATCHES (2 batch susu Kios A utk bukti FIFO AC#2) ═══
        // [id, outlet, material, no_batch, qty_awal, qty_sisa, hpp, expired(+hari)]
        $batches = [
            [1,  2, 1,  'B-SUSU-001', 10000, 5000,  28.00, 2],
            [2,  2, 1,  'B-SUSU-002', 10000, 10000, 32.00, 5],
            [3,  1, 1,  'B-PST-SUSU-001', 100000, 100000, 28.00, 3],
            [4,  1, 2,  'B-PST-KURMA-001', 5000, 5000, 120.00, 180],
            [5,  1, 3,  'B-PST-JAHE-001', 5000, 5000, 90.00, 180],
            [6,  1, 4,  'B-PST-COKLT-001', 5000, 5000, 110.00, 180],
            [7,  1, 5,  'B-PST-STROB-001', 5000, 5000, 100.00, 180],
            [8,  1, 6,  'B-PST-KOPI-001', 5000, 5000, 130.00, 180],
            [9,  1, 7,  'B-PST-GULA-001', 10000, 10000, 40.00, 365],
            [10, 1, 8,  'B-PST-CUPS-001', 2000, 2000, 450.00, null],
            [11, 1, 9,  'B-PST-CUPM-001', 3000, 3000, 500.00, null],
            [12, 1, 10, 'B-PST-CUPL-001', 2000, 2000, 550.00, null],
            [13, 1, 11, 'B-PST-LIDS-001', 2000, 2000, 200.00, null],
            [14, 1, 12, 'B-PST-LIDM-001', 3000, 3000, 250.00, null],
            [15, 1, 13, 'B-PST-LIDL-001', 2000, 2000, 300.00, null],
            [16, 1, 14, 'B-PST-SDTN-001', 5000, 5000, 50.00, null],
            [17, 1, 15, 'B-PST-KANTG-001', 3000, 3000, 150.00, null],
            [18, 2, 2,  'B-A-KURMA-001', 1000, 1000, 125.00, 150],
            [19, 2, 3,  'B-A-JAHE-001',  1000, 1000, 95.00, 150],
            [20, 2, 4,  'B-A-COKLT-001', 1000, 1000, 115.00, 150],
            [21, 2, 5,  'B-A-STROB-001', 1000, 1000, 105.00, 150],
            [22, 2, 6,  'B-A-KOPI-001',  1000, 1000, 135.00, 150],
            [23, 2, 7,  'B-A-GULA-001',  2000, 2000, 42.00, 300],
            [24, 2, 8,  'B-A-CUPS-001',  500, 500, 460.00, null],
            [25, 2, 9,  'B-A-CUPM-001',  500, 500, 510.00, null],
            [26, 2, 10, 'B-A-CUPL-001',  500, 500, 560.00, null],
            [27, 2, 11, 'B-A-LIDS-001',  500, 500, 210.00, null],
            [28, 2, 12, 'B-A-LIDM-001',  500, 500, 260.00, null],
            [29, 2, 13, 'B-A-LIDL-001',  500, 500, 310.00, null],
            [30, 2, 14, 'B-A-SDTN-001',  800, 800, 55.00, null],
            [31, 2, 15, 'B-A-KANTG-001', 500, 500, 160.00, null],
            [32, 3, 1,  'B-B-SUSU-001', 8000, 8000, 30.00, 3],
            [33, 3, 2,  'B-B-KURMA-001', 500, 500, 125.00, 150],
            [34, 3, 9,  'B-B-CUPM-001',  200, 200, 510.00, null],
            [35, 3, 12, 'B-B-LIDM-001',  200, 200, 260.00, null],
            [36, 3, 14, 'B-B-SDTN-001',  300, 300, 55.00, null],
            [37, 3, 15, 'B-B-KANTG-001', 200, 200, 160.00, null],
        ];
        foreach ($batches as $b) {
            $this->db->table('stock_batches')->insert([
                'id' => $b[0], 'outlet_id' => $b[1], 'raw_material_id' => $b[2], 'no_batch' => $b[3],
                'qty_awal' => $b[4], 'qty_sisa' => $b[5], 'hpp_satuan' => $b[6],
                'expired_date' => $b[7] === null ? null : date('Y-m-d', strtotime("+{$b[7]} days")),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ═══ 5. PRODUCT VARIANTS (6 rasa x 3 ukuran = 18) ═══
        // Harga jual diset ~40% di atas HPP (HPP M rasa ≈ Rp11.250)
        $harga    = ['S' => 14000, 'M' => 16000, 'L' => 19000];
        $hargaOri = ['S' => 12000, 'M' => 14000, 'L' => 17000];
        $warna = [
            'Original' => '#8E8E93', 'Kurma' => '#A2845E', 'Jahe Merah' => '#FF3B30',
            'Cokelat' => '#6B4F3A', 'Stroberi' => '#FF2D55', 'Kopi' => '#4A3428',
        ];
        $rasas = ['Original', 'Kurma', 'Jahe Merah', 'Cokelat', 'Stroberi', 'Kopi'];
        $vid = 1;
        $variantMap = [];
        foreach ($rasas as $rasa) {
            foreach (['S', 'M', 'L'] as $uk) {
                $h = $rasa === 'Original' ? $hargaOri[$uk] : $harga[$uk];
                $this->db->table('product_variants')->insert([
                    'id' => $vid, 'nama_menu' => "Susu Kambing {$rasa} {$uk}",
                    'varian_rasa' => $rasa, 'ukuran_cup' => $uk, 'harga_jual' => $h,
                    'warna_tema' => $warna[$rasa], 'is_active' => 1,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $variantMap["{$rasa}|{$uk}"] = $vid;
                $vid++;
            }
        }

        // ═══ 6. PRODUCT RECIPES (BOM) ═══
        $susuMl = ['S' => 200, 'M' => 250, 'L' => 300];
        $rasaMl = ['S' => 20,  'M' => 25,  'L' => 30];
        $cupId  = ['S' => 8, 'M' => 9, 'L' => 10];
        $lidId  = ['S' => 11, 'M' => 12, 'L' => 13];
        $rasaId = ['Kurma' => 2, 'Jahe Merah' => 3, 'Cokelat' => 4, 'Stroberi' => 5, 'Kopi' => 6];
        $totalResep = 0;

        foreach ($rasas as $rasa) {
            foreach (['S', 'M', 'L'] as $uk) {
                $pv = $variantMap["{$rasa}|{$uk}"];
                $rows = [
                    [$pv, 1, $susuMl[$uk], 2.00],
                    [$pv, $rasa === 'Original' ? 7 : $rasaId[$rasa], $rasa === 'Original' ? 15 : $rasaMl[$uk], 0],
                    [$pv, $cupId[$uk], 1, 0],
                    [$pv, $lidId[$uk], 1, 0],
                    [$pv, 14, 1, 0],
                    [$pv, 15, 1, 0],
                ];
                foreach ($rows as $r) {
                    $this->db->table('product_recipes')->insert([
                        'product_variant_id' => $r[0], 'raw_material_id' => $r[1],
                        'qty_needed' => $r[2], 'waste_pct' => $r[3],
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $totalResep++;
                }
            }
        }

        echo "DemoSeeder OK: 3 outlet, 4 user, 15 bahan, 37 batch, 18 varian, {$totalResep} resep.\n";
        echo "Login: owner|admin|kasir|kasirb - pass: password123 - PIN owner: 123456\n";
    }
}

