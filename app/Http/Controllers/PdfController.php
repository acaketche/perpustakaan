<?php

namespace App\Http\Controllers;

use App\Models\DigitalBook;
use App\Models\ReadingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use setasign\Fpdi\Fpdi;

class PdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function serve(DigitalBook $digitalBook)
    {
        // Check if the book is published
        if ($digitalBook->status !== 'published') {
            abort(404);
        }

        // Log the reading activity
        ReadingLog::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'digital_book_id' => $digitalBook->id
            ],
            [
                'last_accessed_at' => now()
            ]
        );

        // Get the PDF file
        $path = Storage::disk('private')->path($digitalBook->pdf_path);

        // Check if watermarking is enabled in config
        if (config('app.pdf_watermark', true)) {
            return $this->serveWatermarkedPdf($path, Auth::user()->name);
        }

        // Serve the file with no-cache headers
        return Response::file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($digitalBook->pdf_path) . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
        ]);
    }

    private function serveWatermarkedPdf($path, $username)
    {
        // Create temporary file for watermarked PDF
        $tempFile = tempnam(sys_get_temp_dir(), 'watermarked_');

        // Create FPDI instance
        $pdf = new Fpdi();

        // Get page count
        $pageCount = $pdf->setSourceFile($path);

        // Add watermark to each page
        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);

            // Add watermark text
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(128, 128, 128); // Gray color
            $pdf->SetXY(10, 10);
            $watermarkText = $username . ' - ' . now()->format('Y-m-d H:i:s');
            $pdf->Write(0, $watermarkText);

            // Add diagonal watermark
            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor(255, 200, 200, 20); // Light red with transparency
            $pdf->SetXY(30, 120);
            $pdf->Rotate(45);
            $pdf->Write(0, $username);
            $pdf->Rotate(0);
        }

        // Save the watermarked PDF to the temporary file
        $pdf->Output($tempFile, 'F');

        // Serve the watermarked file
        return Response::file($tempFile, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
        ])->deleteFileAfterSend(true);
    }
}
