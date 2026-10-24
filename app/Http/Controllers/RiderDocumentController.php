<?php

namespace App\Http\Controllers;

use App\Models\RiderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiderDocumentController extends Controller
{
    public function show(Request $request, RiderProfile $rider, string $field)
    {
        abort_unless((int) $request->user()->id === (int) $rider->user_id
            || in_array($request->user()->user_type, ['super_admin', 'domestic_admin'], true), 403);
        abort_unless(in_array($field, [
            'citizenship_front_path', 'citizenship_back_path', 'selfie_photo_path',
            'driving_license_doc_path', 'vehicle_registration_doc_path',
        ], true), 404);
        $path = $rider->getAttribute($field);
        abort_unless(is_string($path) && str_starts_with($path, 'kyc/') && ! str_contains($path, '..'), 404);
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path, null, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
