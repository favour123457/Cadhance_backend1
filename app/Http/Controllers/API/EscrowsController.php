<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escrow;
use App\Models\EscrowHistory;
use App\Http\Resources\EscrowResource;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CustomizationMilestone;


class EscrowsController extends Controller
{
    public function index()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $escrows = $user->escrows;

        return response()->json(EscrowResource::collection($escrows));
    }

    public function store(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $wallet = $user->wallet;
        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $escrow = new Escrow();
        $escrow->user_id = $user->id;
        $escrow->customization_request_id = $request->customization_request_id;
        $escrow->amount = $request->amount;
        $escrow->save();

        $wallet->decrement('balance', $request->amount);

        EscrowHistory::create([
            'escrow_id' => $escrow->id,
            'amount' => $escrow->amount,
            'description' => 'Escrow created with amount: ' . showMoney($escrow->amount),
        ]);

        return response()->json(new EscrowResource($escrow));
    }

    public function cancel(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $escrow = Escrow::findOrFail($request->escrow_id);
        $customization_request = $escrow->customization_request;

        if ($customization_request->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($customization_request->customization_status_id != 1) {
            return response()->json(['error' => 'Only pending escrows can be cancelled'], 400);
        }

        
        $wallet = $user->wallet;
        $wallet->increment('balance', $escrow->amount);


        EscrowHistory::create([
            'escrow_id' => $escrow->id,
            'amount' => $escrow->amount,
            'description' => 'Escrow cancelled and amount refunded: ' . showMoney($escrow->amount),
        ]);

        $escrow->amount = 0;
        $escrow->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Escrow cancelled successfully and amount refunded',
            'data' => new EscrowResource($escrow)
        ]);
    }

    public function debitEscrow(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $customization_milestone_id = $request->customization_milestone_id;

        $escrow = Escrow::findOrFail($request->escrow_id);
        $customization_request = $escrow->customization_request;

        if ($customization_request->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($customization_request->customization_status_id != 3) {
            return response()->json(['error' => 'Only accepted escrows can be debited'], 400);
        }

        $customization_milestone = CustomizationMilestone::findOrFail($customization_milestone_id);
        $customization_milestone_price = $customization_milestone->price;

        //debit the escrow amount 
        $escrow->amount -= $customization_milestone_price;
        $escrow->save();

        //credit the designer's wallet
        $designer = $customization_request->designer;
        $designer_wallet = $designer->wallet;
        $designer_wallet->increment('balance', $customization_milestone_price);


        EscrowHistory::create([
            'escrow_id' => $escrow->id,
            'amount' => $customization_milestone_price,
            'description' => 'Escrow debited with amount: ' . showMoney($customization_milestone_price).""." for milestone: ".$customization_milestone->title,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Escrow debited successfully',
            'data' => new EscrowResource($escrow)
        ]);
    }
}
