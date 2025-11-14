<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function generate()
    {
        // 1. Authenticated user
        $user = Auth::user();

        // 2. Public URL with user ID
        $publicUrl = config('app.url') . '/menu/' . $user->id;

        // 3. Generate SVG QR (force string!)
        $svgQrCode = (string) QrCode::size(250)
            ->format('svg')
            ->generate($publicUrl);
            

        // 4. JSON response
        return response()->json([
            'url' => $publicUrl,
            'qr_code_svg' => $svgQrCode,
            'message' => 'QR Code successfully generated.'
        ]);
    }
}
