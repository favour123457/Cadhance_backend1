<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileMoneyAccountResource;
use App\Models\MobileMoneyAccount;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class MobileMoneyController extends Controller
{
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $accounts = MobileMoneyAccount::where('user_id', $user->id)->get();
        return response()->json([
            'status' => 'success',
            'data' => MobileMoneyAccountResource::collection($accounts),
        ]);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validated = $request->validate([
            'provider'       => 'required|string|max:100',
            'network_code'   => 'required|string|max:50|in:MTN,VDF,AIRTEL,TIGO,MPESA,ORANGE',
            'account_name'   => 'required|string|max:200',
            'account_number' => 'required|string|max:50',
            'currency_id'    => 'nullable|integer',
            'recipient_address' => 'nullable|string|max:500',
            'recipient_email'   => 'nullable|email|max:200',
            'recipient_country' => 'nullable|string|max:100',
        ]);

        $account = MobileMoneyAccount::create([
            'user_id'           => $user->id,
            'currency_id'       => $request->currency_id,
            'provider'          => $request->provider,
            'network_code'      => $request->network_code,
            'account_name'      => $request->account_name,
            'account_number'    => $request->account_number,
            'recipient_address' => $request->recipient_address,
            'recipient_email'   => $request->recipient_email,
            'recipient_country' => $request->recipient_country,
            'is_verified'       => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mobile money account saved.',
            'data' => new MobileMoneyAccountResource($account),
        ]);
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
