<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KYCController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user();
        $requiredDocs = $user->getRequiredDocuments();
        return view('kyc.upload', compact('user', 'requiredDocs'));
    }

    public function upload(Request $request)
    {
        $user = Auth::user();
        
        $rules = [];
        $requiredDocs = $user->getRequiredDocuments();
        
        foreach (array_keys($requiredDocs) as $doc) {
            $rules[$doc] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }
        
        $request->validate($rules);
        
        // Store documents
        foreach ($requiredDocs as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('kyc/' . $user->id, 'private');
                $user->$field = $path;
                $user->save();
            }
        }
        
        // Uploading documents is not verification. Approval remains an
        // explicit back-office action after a reviewer has checked them.
        $user->kyc_verified = false;
        $user->kyc_verified_at = null;
        $user->save();
        
        return redirect()->route('dashboard')
            ->with('success', 'KYC documents uploaded successfully!');
    }
}
