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
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $bank_id = $validated['bank_id'] ?? 0;
        $bank_name = $validated['bank_name'];
        $account_number = $validated['account_number'];
        $currency_id = $validated['currency_id'] ?? null;
        $destination_branch_code = $validated['destination_branch_code'] ?? null;
        $account_name = $user->name;

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

        $bank_account = BankAccount::create([
            'user_id' => $user->id,
            'bank_id' => $bank_id,
            'currency_id' => $currency_id,
            'bank_name' => $bank_name,
            'bank_code' => $bank_code,
            'account_number' => $account_number,
            'account_name' => $account_name,
            'destination_branch_code' => $destination_branch_code,
            'is_deleted' => 0,
        ]);

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
        // $account_name = $request->account_name;
        $account_name = $user->name;

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

        $bank_account->update([
            'bank_id' => $bank_id,
            'currency_id' => $currency_id,
            'bank_name' => $bank_name,
            'bank_code' => $bank_code,
            'account_number' => $account_number,
            'account_name' => $account_name,
            'destination_branch_code' => $destination_branch_code,
        ]);

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
