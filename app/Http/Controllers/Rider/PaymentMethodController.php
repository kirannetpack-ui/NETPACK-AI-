<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List payment methods
     */
    public function index()
    {
        $riderId = Auth::id();
        
        $paymentMethods = PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->orderBy('is_default', 'desc')
            ->get();

        return view('rider.payment-methods', compact('paymentMethods'));
    }

    /**
     * Show add payment method form
     */
    public function create()
    {
        return view('rider.payment-methods-add');
    }

    /**
     * Store payment method
     */
    public function store(Request $request)
    {
        $riderId = Auth::id();

        $request->validate([
            'method_type' => 'required|in:bank,esewa,khalti,connectips',
            'account_name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, remove default from others
        if ($request->has('is_default') && $request->is_default) {
            PaymentMethod::where('user_id', $riderId)
                ->where('user_type', 'rider')
                ->update(['is_default' => false]);
        }

        $data = [
            'user_id' => $riderId,
            'user_type' => 'rider',
            'method_type' => $request->method_type,
            'account_name' => $request->account_name,
            'is_default' => $request->has('is_default'),
            'is_verified' => false,
        ];

        // Bank specific
        if ($request->method_type === 'bank') {
            $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'branch' => 'nullable|string|max:255',
                'account_type' => 'nullable|in:savings,current',
            ]);
            $data['bank_name'] = $request->bank_name;
            $data['account_number'] = $request->account_number;
            $data['branch'] = $request->branch;
            $data['account_type'] = $request->account_type;
        }

        // eSewa
        if ($request->method_type === 'esewa') {
            $request->validate([
                'esewa_id' => 'required|string|max:50',
                'mobile_number' => 'required|string|max:15',
            ]);
            $data['esewa_id'] = $request->esewa_id;
            $data['mobile_number'] = $request->mobile_number;
        }

        // Khalti
        if ($request->method_type === 'khalti') {
            $request->validate([
                'khalti_id' => 'required|string|max:50',
                'mobile_number' => 'required|string|max:15',
            ]);
            $data['khalti_id'] = $request->khalti_id;
            $data['mobile_number'] = $request->mobile_number;
        }

        // ConnectIPS
        if ($request->method_type === 'connectips') {
            $request->validate([
                'connectips_id' => 'required|string|max:50',
            ]);
            $data['connectips_id'] = $request->connectips_id;
        }

        PaymentMethod::create($data);

        return redirect()->route('rider.payment-methods')
            ->with('success', 'Payment method added successfully! Please wait for verification.');
    }

    /**
     * Delete payment method
     */
    public function destroy($id)
    {
        $riderId = Auth::id();
        
        $method = PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->findOrFail($id);

        if ($method->is_default) {
            return redirect()->back()
                ->with('error', 'Cannot delete default payment method. Set another as default first.');
        }

        $method->delete();

        return redirect()->route('rider.payment-methods')
            ->with('success', 'Payment method deleted successfully.');
    }

    /**
     * Set default payment method
     */
    public function setDefault($id)
    {
        $riderId = Auth::id();
        
        $method = PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->findOrFail($id);

        PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->update(['is_default' => false]);

        $method->update(['is_default' => true]);

        return redirect()->route('rider.payment-methods')
            ->with('success', 'Default payment method updated successfully.');
    }
}