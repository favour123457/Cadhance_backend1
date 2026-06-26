<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MobileMoneyAccount;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class MobileMoneyController extends Controller
{
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $accounts = MobileMoneyAccount::where('user_id', $user->id)->get();
        return response()->json(['status' => 'success', 'data' => $accounts]);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request->validate([
            'provider'       => 'required|string|max:100',
            'network_code'   => 'required|string|max:50|in:MTN,VDF,AIRTEL,TIGO,MPESA,ORANGE',
            'account_name'   => 'required|string|max:200',
            'account_number' => 'required|string|max:50',
            'currency_id'    => 'nullable|integer',
        ]);

        $account = MobileMoneyAccount::create([
            'user_id'        => $user->id,
            'currency_id'    => $request->currency_id,
            'provider'       => $request->provider,
            'network_code'   => $request->network_code,
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'is_verified'    => false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Mobile money account saved.', 'data' => $account]);
    }

    public function destroy(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request->validate(['id' => 'required|integer']);

        $account = MobileMoneyAccount::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$account) {
            return response()->json(['status' => 'failed', 'message' => 'Account not found.'], 404);
        }

        $account->delete();
        return response()->json(['status' => 'success', 'message' => 'Account removed.']);
    }
}
