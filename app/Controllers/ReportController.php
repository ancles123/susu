<?php

namespace App\Controllers;

use App\Models\OutletModel;
use App\Models\StockBatchModel;
use App\Models\TransactionModel;

class ReportController extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('reports/laba-rugi'));
    }

    /** FR-14: Laba rugi konsolidasi per outlet + breakdown rasa/ukuran */
    public function labaRugi()
    {
        $dari   = $this->request->getGet('dari')   ?: date('Y-m-01');
        $sampai = $this->request->getGet('sampai') ?: date('Y-m-d');
        $outletId = session()->get('role') === 'owner'
            ? ($this->request->getGet('outlet_id') ? (int) $this->request->getGet('outlet_id') : null)
            : $this->currentOutletId();

        $trxModel = new TransactionModel();
        return view('reports/laba_rugi', [
            'title' => 'Laporan Laba Rugi',
            'dari' => $dari, 'sampai' => $sampai, 'outletId' => $outletId,
            'perOutlet' => $trxModel->getLabaRugi($dari, $sampai, $outletId),
            'perRasa' => $trxModel->getLabaPerVarian($dari, $sampai, $outletId, 'pv.varian_rasa'),
            'perUkuran' => $trxModel->getLabaPerVarian($dari, $sampai, $outletId, 'pv.ukuran_cup'),
            'outlets' => (new OutletModel())->where('status_aktif', 1)->findAll(),
        ]);
    }

    /** Stok per outlet untuk laporan */
    public function stok()
    {
        $outletId = session()->get('role') === 'owner' && $this->request->getGet('outlet_id')
            ? (int) $this->request->getGet('outlet_id') : $this->currentOutletId();
        return view('reports/stok', [
            'title' => 'Laporan Stok',
            'batches' => (new StockBatchModel())->getAllWithMaterial($outletId),
            'outlets' => (new OutletModel())->where('status_aktif', 1)->findAll(),
            'outletId' => $outletId,
        ]);
    }

    /** Export CSV laba rugi per outlet */
    public function exportCsv()
    {
        $dari   = $this->request->getGet('dari')   ?: date('Y-m-01');
        $sampai = $this->request->getGet('sampai') ?: date('Y-m-d');
        $rows = (new TransactionModel())->getLabaRugi($dari, $sampai, null);

        $csv = "Outlet,Trx,Omzet,HPP,Laba Kotor\n";
        foreach ($rows as $r) {
            $csv .= implode(',', [
                '"' . $r['nama_kios'] . '"', $r['jml_trx'],
                $r['omzet'], $r['hpp'], $r['laba_kotor'],
            ]) . "\n";
        }
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="laba-rugi-' . $dari . '_' . $sampai . '.csv"')
            ->setBody($csv);
    }
}
