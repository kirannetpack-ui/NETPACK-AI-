<?php

namespace App\Helpers;

class QRCodeHelper
{
    public static function generate($url, $size = 200)
    {
        // Use Google Charts API (free, no dependencies)
        $encodedUrl = urlencode($url);
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedUrl}&choe=UTF-8";
    }

    public static function generateHtml($url, $size = 200)
    {
        $src = self::generate($url, $size);
        return '<img src="' . $src . '" alt="QR Code" style="width: ' . $size . 'px; height: ' . $size . 'px;">';
    }
}