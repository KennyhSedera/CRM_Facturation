<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfService
{
    public function generateQuotePdf($quote)
    {
        $pdf = Pdf::loadView('pdf.quote', compact('quote'));
        return $pdf->download('devis_'.$quote->id.'.pdf');
    }

    public function generateInvoicePdf($invoice)
    {
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        return $pdf->download('facture_'.$invoice->id.'.pdf');
    }

    public function streamQuotePdf($quote)
    {
        $pdf = Pdf::loadView('pdf.quote', compact('quote'));
        return $pdf->stream('devis_'.$quote->id.'.pdf');
    }
}
