<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportExportService;

class ReportExportController extends Controller
{
    protected ReportExportService $exportService;

    public function __construct(ReportExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function exportOrders()
    {
        return $this->exportService->exportCorporateOrders();
    }

    public function exportReturns()
    {
        return $this->exportService->exportTaxReturns();
    }

    public function exportClients()
    {
        return $this->exportService->exportClients();
    }
}
