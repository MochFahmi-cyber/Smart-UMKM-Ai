<?php

namespace App\Http\Controllers;

use App\Models\CustomerCashierToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrKasirController extends Controller
{
    public function index()
    {
        $tokens = CustomerCashierToken::where('store_id', auth()->user()->store_id)->latest()->get();
        return view('qr-kasir.index', compact('tokens'));
    }

    public function generate()
    {
        $token = CustomerCashierToken::create([
            'store_id' => auth()->user()->store_id,
            'user_id' => auth()->id(),
            'token' => Str::random(32),
            'is_active' => true,
        ]);

        return redirect()->route('qr-kasir.index')->with('success', 'QR Kasir Token berhasil dibuat.');
    }

    public function toggleActive($token)
    {
        $cashierToken = CustomerCashierToken::where('store_id', auth()->user()->store_id)->where('token', $token)->firstOrFail();
        $cashierToken->update(['is_active' => !$cashierToken->is_active]);

        return back()->with('success', 'Status QR Kasir berhasil diubah.');
    }
}
