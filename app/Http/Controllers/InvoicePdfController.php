<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function downloadPdf($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);
        $company = CompanySetting::first();
        $invoiceSetting = InvoiceSetting::first();

        $pdf = Pdf::loadView('pdf.invoice', [
            'sale' => $sale,
            'company' => $company,
            'invoiceSetting' => $invoiceSetting,
        ]);

        return $pdf->stream("Invoice_{$sale->invoice_number}.pdf");
    }
}
