<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escrow;
use App\Models\EscrowHistory;
use App\Http\Resources\EscrowResource;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CustomizationMilestone;
use App\Models\CustomizationRequest;


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
        $validated = $request->validate([
            'customization_request_id' => 'required|integer|exists:customization_requests,id',
            'amount'                   => 'required|numeric|min:0.01',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $wallet = $user->wallet;
        $amount = (float) $validated['amount'];

        if ($wallet->balance < $amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $customizationRequest = CustomizationRequest::find($validated['customization_request_id']);

        if (!$customizationRequest || $customizationRequest->user_id != $user->id) {
            return response()->json(['error' => 'Invalid customization request'], 403);
        }

        $escrow = new Escrow();
        $escrow->user_id = $user->id;
        $escrow->customization_request_id = $validated['customization_request_id'];
        $escrow->amount = $amount;
        $escrow->save();

        $wallet->decrement('balance', $amount);

        EscrowHistory::create([
            'escrow_id' => $escrow->id,
            'amount' => $escrow->amount,
            'description' => 'Escrow created with amount: ' . showMoney($escrow->amount),
        ]);

        return response()->json(new EscrowResource($escrow));
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'escrow_id' => 'required|integer|exists:escrows,id',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $escrow = Escrow::findOrFail($validated['escrow_id']);
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
        $validated = $request->validate([
            'escrow_id' => 'required|integer|exists:escrows,id',
            'customization_milestone_id' => 'required|integer|exists:customization_milestones,id',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $escrow = Escrow::findOrFail($validated['escrow_id']);
        $customization_request = $escrow->customization_request;

        if ($customization_request->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($customization_request->customization_status_id != 3) {
            return response()->json(['error' => 'Only accepted escrows can be debited'], 400);
        }

        $customization_milestone = CustomizationMilestone::findOrFail($validated['customization_milestone_id']);
        $customization_milestone_price = (float) $customization_milestone->price;

        if ($escrow->amount < $customization_milestone_price) {
            return response()->json([
                'error' => 'Escrow balance is insufficient to cover this milestone',
                'escrow_balance' => $escrow->amount,
                'milestone_price' => $customization_milestone_price,
            ], 400);
        }

        // Debit the escrow amount
        $escrow->amount -= $customization_milestone_price;
        $escrow->save();

        // Credit the designer's wallet
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
