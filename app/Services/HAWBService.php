<?php

namespace App\Services;

use App\Models\Shipment;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Milon\Barcode\DNS1D;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HAWBService
{
    protected $barcodeGenerator;
    
    public function __construct()
    {
        $this->barcodeGenerator = new DNS1D();
    }
    
    public function generateQRCode($hawbNumber, $trackingUrl)
    {
        try {
            $qrCode = new QrCode(
                data: $trackingUrl,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 220,
                margin: 10,
            );
            $png = (new PngWriter())->write($qrCode)->getString();
            
            if (!Storage::disk('public')->exists('qr_codes')) {
                Storage::disk('public')->makeDirectory('qr_codes');
            }
            
            $filename = "qr_codes/{$hawbNumber}.png";
            Storage::disk('public')->put($filename, $png);
            
            return Storage::url($filename);
        } catch (\Exception $e) {
            Log::error('QR Code failed: ' . $e->getMessage());
            return null;
        }
    }
    
    public function generateBarcode($hawbNumber)
    {
        try {
            if (!Storage::disk('public')->exists('barcodes')) {
                Storage::disk('public')->makeDirectory('barcodes');
            }
            
            $barcodeData = $this->barcodeGenerator->getBarcodePNG($hawbNumber, 'C128', 2, 50);
            
            $filename = "barcodes/{$hawbNumber}.png";
            Storage::disk('public')->put($filename, base64_decode($barcodeData));
            
            return Storage::url($filename);
        } catch (\Exception $e) {
            Log::error('Barcode failed: ' . $e->getMessage());
            return null;
        }
    }
    
    public function generateHAWBPDF(Shipment $shipment)
    {
        // Create directories
        foreach (['qr_codes', 'barcodes', 'hawb'] as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
        }
        
        $trackingUrl = route('tracking.show', $shipment->tracking_number);
        $qrUrl = $this->generateQRCode($shipment->hawb_number, $trackingUrl);
        $barcodeUrl = $this->generateBarcode($shipment->hawb_number);
        
        $boxes = json_decode($shipment->boxes, true) ?? [];
        
        $pdf = Pdf::loadView('pdf.hawb', [
            'shipment' => $shipment,
            'qrUrl' => $qrUrl,
            'barcodeUrl' => $barcodeUrl,
            'boxes' => $boxes,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
        
        $pdf->setPaper('a6', 'portrait');
        
        $pdfFilename = "hawb/{$shipment->hawb_number}.pdf";
        Storage::disk('public')->put($pdfFilename, $pdf->output());
        
        return [
            'pdf' => $pdf,
            'url' => Storage::url($pdfFilename),
            'qr_url' => $qrUrl,
            'barcode_url' => $barcodeUrl
        ];
    }
}
