<?php
namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function generate()
    {
        $data = [
            'title' => 'Rapport Laravel 12',
            'content' => 'Ceci est un exemple de PDF généré avec DomPDF sur Laravel 12.'
        ];

        $pdf = Pdf::loadView('pdf.rapport', $data);
        // return $pdf->download('rapport-laravel12.pdf');
        return $pdf->stream('rapport-laravel12.pdf');

    }
}
