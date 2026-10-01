<?php

namespace App\Controllers;

use App\Models\DocumentReceivedModel;
use App\Models\DocumentSentModel;
use App\Models\DocumentRequisitionModel;

/**
 * แดชบอร์ด — พอร์ตจาก CI3 Dashboard (สถิติวันนี้/เดือน/ปี + แยกประเภท + ล่าสุด)
 */
class Dashboard extends BaseController
{
    public function index()
    {
        $recv = new DocumentReceivedModel();
        $sent = new DocumentSentModel();
        $req  = new DocumentRequisitionModel();

        $currentBe  = (int) date('Y') + 543;
        $selectedBe = (int) ($this->request->getGet('year') ?: $currentBe);
        $selectedBe = max(min($selectedBe, $currentBe), $currentBe - 4);
        $ce         = $selectedBe - 543;

        $today      = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');
        $yearStart  = $ce . '-01-01';
        $yearEnd    = $ce . '-12-31';

        $data = [
            'title'           => 'แดชบอร์ด',
            'selected_year'   => $selectedBe,
            'current_year'    => $currentBe,
            'available_years' => range($currentBe, $currentBe - 4),

            'received_today' => $recv->countByPeriod($today, $today),
            'received_month' => $recv->countByPeriod($monthStart, $monthEnd),
            'received_year'  => $recv->countByPeriod($yearStart, $yearEnd),
            'sent_today'     => $sent->countByPeriod($today, $today),
            'sent_month'     => $sent->countByPeriod($monthStart, $monthEnd),
            'sent_year'      => $sent->countByPeriod($yearStart, $yearEnd),
            'requisition_today' => $req->countByPeriod($today, $today),
            'requisition_month' => $req->countByPeriod($monthStart, $monthEnd),
            'requisition_year'  => $req->countByPeriod($yearStart, $yearEnd),

            'received_by_type' => $recv->countByType($yearStart, $yearEnd),
            'sent_by_type'     => $sent->countByType($yearStart, $yearEnd),

            'recent_received'    => $recv->getRecent(5),
            'recent_sent'        => $sent->getRecent(5),
            'recent_requisition' => $req->getRecentByPeriod($yearStart, $yearEnd, 5),
        ];

        return $this->render('dashboard/index', $data);
    }

    /** หน้า placeholder สำหรับเมนูที่ยังไม่ได้พัฒนา */
    public function soon(?string $name = null)
    {
        return $this->render('dashboard/soon', ['title' => 'อยู่ระหว่างพัฒนา', 'module' => $name]);
    }
}
