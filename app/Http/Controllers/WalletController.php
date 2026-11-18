<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $wallet = $request->user()->wallet()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['balance' => 0, 'currency' => 'NGN']
        );

        return view('wallet.index', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->paginate(15),
        ]);
    }
}
