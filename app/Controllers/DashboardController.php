<?php

namespace App\Controllers;

use App\Models\CashierShiftModel;
use App\Models\OutletModel;
use App\Models\StockBatchModel;
use App\Models\StockTransferModel;
use App\Models\StockWasteModel;
use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $outletId     = $this->currentOutletId();
        $trxModel     = new TransactionModel();
        $batchModel   = new StockBatchModel();
        $transferModel= new StockTransferModel();
        $wasteModel   = new StockWasteModel();

        $role = session()->get('role');
        $isKonsolidasi = $role === 'owner' && ! $this->request->getGet('outlet_id');

        $data = [
            'title'        => 'Dashboard',
            'stats'        => $trxModel->getStatsHarian($outletId),
            'expiring'     => $batchModel->getExpiringSoon($outletId, 2),   // FR-08
            'lowStock'     => $batchModel->getLowStock($outletId),
            'transferIn'   => $transferModel->countMasukPending($outletId),
            'rugiHariIni'  => $wasteModel->getTotalRugi($outletId, date('Y-m-d'), date('Y-m-d')),
            'outlets'      => (new OutletModel())->where('status_aktif', 1)->findAll(),
            'outletId'     => $outletId,
            'shift'        => (new CashierShiftModel())->getOpenShift($outletId, (int) session()->get('user_id')),
            'konsolidasi'  => $isKonsolidasi ? $trxModel->getLabaRugi(date('Y-m-d'), date('Y-m-d')) : null,
        ];
        return view('dashboard/index', $data);
    }

    /** JSON data grafik 7 hari */
    public function chartData()
    {
        $outletId = $this->currentOutletId();
        $rows = (new TransactionModel())->getGrafik7Hari($outletId);

        $labels = []; $omzet = []; $laba = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($tgl));
            $found = array_filter($rows, static fn($r) => $r['tgl'] === $tgl);
            $row = $found ? reset($found) : null;
            $omzet[] = $row ? (float) $row['omzet'] : 0;
            $laba[]  = $row ? (float) $row['laba'] : 0;
        }
        return $this->response->setJSON(['labels' => $labels, 'omzet' => $omzet, 'laba' => $laba]);
    }
}
