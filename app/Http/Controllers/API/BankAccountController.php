<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class BankAccountController extends Controller
{
    protected FlutterwaveService $flutterwave;

    public function __construct(FlutterwaveService $flutterwave)
    {
        $this->flutterwave = $flutterwave;
    }
    public function index()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $bank_accounts = $user->bank_accounts()->where('is_deleted', 0)->get();

        return response()->json(BankAccountResource::collection($bank_accounts));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_id'                => 'required_without:bank_code|integer|exists:banks,id',
            'bank_code'              => 'required_without:bank_id|string|max:50',
            'bank_name'              => 'required|string|max:200',
            'account_number'         => 'required|string|max:50',
            'currency_id'            => 'nullable|integer',
            'destination_branch_code'=> 'nullable|string|max:50',
            'recipient_email'        => 'nullable|email|max:200',
            'recipient_address'      => 'nullable|string|max:500',
            'recipient_city'         => 'nullable|string|max:100',
            'recipient_country'      => 'nullable|string|max:100',
            'recipient_phone'        => 'nullable|string|max:50',
            'account_type'           => 'nullable|string|max:50',
            'routing_number'         => 'nullable|string|max:50',
            'swift_code'             => 'nullable|string|max:50',
            'postal_code'            => 'nullable|string|max:50',
            'bank_branch'            => 'nullable|string|max:200',
            'beneficiary_country'    => 'nullable|string|max:100',
            'sender_id_type'         => 'nullable|string|max:100',
            'sender_id_number'       => 'nullable|string|max:100',
            'transfer_purpose_code'  => 'nullable|string|max:100',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $bank_id = $validated['bank_id'] ?? 0;
        $bank_name = $validated['bank_name'];
        $account_number = $validated['account_number'];
        $currency_id = $validated['currency_id'] ?? null;
        $destination_branch_code = $validated['destination_branch_code'] ?? null;
        $account_name = $validated['account_name'] ?? $user->name;

        $extraFields = [
            'recipient_email'       => $validated['recipient_email'] ?? null,
            'recipient_address'     => $validated['recipient_address'] ?? null,
            'recipient_city'        => $validated['recipient_city'] ?? null,
            'recipient_country'     => $validated['recipient_country'] ?? null,
            'recipient_phone'       => $validated['recipient_phone'] ?? null,
            'account_type'          => $validated['account_type'] ?? null,
            'routing_number'        => $validated['routing_number'] ?? null,
            'swift_code'            => $validated['swift_code'] ?? null,
            'postal_code'           => $validated['postal_code'] ?? null,
            'bank_branch'           => $validated['bank_branch'] ?? null,
            'beneficiary_country'   => $validated['beneficiary_country'] ?? null,
            'sender_id_type'        => $validated['sender_id_type'] ?? null,
            'sender_id_number'      => $validated['sender_id_number'] ?? null,
            'transfer_purpose_code' => $validated['transfer_purpose_code'] ?? null,
        ];

        // Resolve bank code from Bank model or request
        $bank_code = null;
        if ($bank_id) {
            $bank = Bank::find($bank_id);
            if ($bank) {
                $bank_code = $bank->code;
                if (!$bank_name) {
                    $bank_name = $bank->name;
                }
            }
        }
        if (!$bank_code) {
            $bank_code = $validated['bank_code'];
        }

        // Verify account details before saving
        $verification = $this->verifyBankAccount($account_number, $bank_code, $user->country_id);
        if ($verification !== true) {
            return $verification;
        }

        $bank_account = BankAccount::create(array_merge([
            'user_id' => $user->id,
            'bank_id' => $bank_id,
            'currency_id' => $currency_id,
            'bank_name' => $bank_name,
            'bank_code' => $bank_code,
            'account_number' => $account_number,
            'account_name' => $account_name,
            'destination_branch_code' => $destination_branch_code,
            'is_deleted' => 0,
        ], $extraFields));

        return response()->json(new BankAccountResource($bank_account));
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $bank_account_id = $request->bank_account_id;
        $bank_id = $request->bank_id ?? 0;
        $bank_name = $request->bank_name;
        $account_number = $request->account_number;
        $currency_id = $request->currency_id;
        $destination_branch_code = $request->destination_branch_code;
        $account_name = $request->account_name ?? $user->name;

        $bank_account = BankAccount::find($bank_account_id);

        if (!$bank_account) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Bank account!'
            ], 400);
        }

        if ($user->id != $bank_account->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 400);
        }

        // Resolve bank code and name
        $bank_code = null;
        if ($bank_id) {
            $bank = Bank::find($bank_id);
            if ($bank) {
                $bank_code = $bank->code;
                if (!$bank_name) {
                    $bank_name = $bank->name;
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid Bank!'
                ], 400);
            }
        }
        if (!$bank_code) {
            $bank_code = $request->bank_code ?? $bank_account->bank_code;
        }

        // Verify account details before saving
        $verification = $this->verifyBankAccount($account_number, $bank_code, $user->country_id);
        if ($verification !== true) {
            return $verification;
        }

        $bank_account->update(array_merge([
            'bank_id' => $bank_id,
            'currency_id' => $currency_id,
            'bank_name' => $bank_name,
            'bank_code' => $bank_code,
            'account_number' => $account_number,
            'account_name' => $account_name,
            'destination_branch_code' => $destination_branch_code,
        ], [
            'recipient_email'       => $request->recipient_email ?? $bank_account->recipient_email,
            'recipient_address'     => $request->recipient_address ?? $bank_account->recipient_address,
            'recipient_city'        => $request->recipient_city ?? $bank_account->recipient_city,
            'recipient_country'     => $request->recipient_country ?? $bank_account->recipient_country,
            'recipient_phone'       => $request->recipient_phone ?? $bank_account->recipient_phone,
            'account_type'          => $request->account_type ?? $bank_account->account_type,
            'routing_number'        => $request->routing_number ?? $bank_account->routing_number,
            'swift_code'            => $request->swift_code ?? $bank_account->swift_code,
            'postal_code'           => $request->postal_code ?? $bank_account->postal_code,
            'bank_branch'           => $request->bank_branch ?? $bank_account->bank_branch,
            'beneficiary_country'   => $request->beneficiary_country ?? $bank_account->beneficiary_country,
            'sender_id_type'        => $request->sender_id_type ?? $bank_account->sender_id_type,
            'sender_id_number'      => $request->sender_id_number ?? $bank_account->sender_id_number,
            'transfer_purpose_code' => $request->transfer_purpose_code ?? $bank_account->transfer_purpose_code,
        ]));

        return response()->json(new BankAccountResource($bank_account));
    }

    public function destroy(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $bank_account_id = $request->bank_account_id;

        $bank_account = BankAccount::find($bank_account_id);

        if (!$bank_account) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Bank account!'
            ], 400);
        }

        if ($user->id != $bank_account->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 400);
        }

        $bank_account->update([
            'is_deleted' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bank account deleted successfully!',
        ]);
    }

    /**
     * Verify a bank account via Flutterwave before saving.
     * Currently enforced for Nigerian accounts (country_id 161) when a bank code is available.
     * Returns true on success or a JsonResponse on failure.
     */
    private function verifyBankAccount(string $account_number, ?string $bank_code, ?int $country_id)
    {
        if ($country_id != 161 || empty($bank_code)) {
            return true;
        }

        $resolve = $this->flutterwave->resolveAccount($account_number, $bank_code);

        if (!isset($resolve['status']) || $resolve['status'] !== 'success') {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Invalid account number or bank!',
            ], 400);
        }

        return true;
    }
}
