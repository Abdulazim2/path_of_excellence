<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function recharge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:vodafone_cash,visa',
            // In a real app, we'd validate payment tokens/details here
        ]);

        $user = Auth::user();
        
        // Simulate payment processing delay or external API call
        // ...

        // Update Wallet
        $user->wallet_balance += $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Wallet recharged successfully via ' . str_replace('_', ' ', $request->method),
            'new_balance' => $user->wallet_balance
        ]);
    }
}
