# Automated Withdrawal System - Implementation Guide

## Overview
The withdrawal system has been updated to automatically process withdrawals via Flutterwave API. When a withdrawal fails, it's marked as pending so admins can process it manually via CSV export.

---

## ✅ Backend Changes Completed

### 1. Database Updates
**New Migration:** `2026_06_10_130000_add_automated_withdrawal_fields.php`

**Bank Accounts Table:**
- `currency_id` (bigInteger, nullable) - Links to currencies table
- `bank_code` (string, nullable) - Flutterwave bank code for direct integration
- `destination_branch_code` (string, nullable) - Required for Ghana, Kenya

**Mobile Money Accounts Table:**
- `currency_id` (bigInteger, nullable) - Links to currencies table
- `network_code` (string, nullable) - Flutterwave network code (MTN, VDF, AIRTEL, etc.)

**Withdrawals Table:**
- `currency_id` (bigInteger, nullable) - Currency used for withdrawal
- `flutterwave_reference` (string, unique, nullable) - Unique transaction reference
- `flutterwave_response` (text, nullable) - JSON response from Flutterwave
- `failure_reason` (text, nullable) - Why auto-processing failed
- `auto_processed` (boolean, default true) - Whether this was auto-processed

### 2. Withdrawal Status IDs
- **1 = Pending**: Failed auto-processing, needs manual CSV export
- **2 = Processing**: Auto-processing in progress
- **3 = Completed**: Successfully processed
- **4 = Failed**: Permanently failed, needs investigation

### 3. FlutterwaveService Updates
**Enhanced Methods:**
```php
// Bank transfer with multi-currency support
initiateTransfer(
    string $account_number,
    string $bank_code,
    string $account_name,
    float $amount,
    string $currency = 'NGN', // Now supports multiple currencies
    string $narration = '',
    string $reference = null,
    ?string $destination_branch_code = null // For Ghana, Kenya
)

// New mobile money transfer method
initiateMobileMoneyTransfer(
    string $phone_number,
    string $network, // MTN, VDF, AIRTEL, etc.
    float $amount,
    string $currency,
    string $narration = '',
    string $reference = null
)
```

### 4. WithdrawalsController Updates
- Auto-processes withdrawal via Flutterwave on submission
- If successful: status = 3 (Completed)
- If failed: status = 1 (Pending) + stores failure_reason for manual processing
- Sends email notifications for both success and failure
- Admin email notification when manual processing needed
- Generates unique references: `WD_BT_{user_id}_{timestamp}_{random}` for bank, `WD_MM_{user_id}_{timestamp}_{random}` for mobile money

### 5. Admin Controller Updates
- New status filters: pending, processing, completed, failed
- `markProcessed()` now accepts status parameter (3=completed, 4=failed)
- Index view now has 5 tabs instead of 3

---

## 🔧 Frontend Updates Required

### 1. Bank Account Form (Add Bank Account)
**Location:** Frontend form where users add bank accounts

**Add These Fields:**
```typescript
{
  bank_id: number | string,
  bank_name: string,
  account_number: string,
  account_name: string, // Already exists
  currency_id: number, // ✅ NEW - Required
  bank_code?: string, // ✅ NEW - Optional (Flutterwave bank code if not using bank_id)
  destination_branch_code?: string // ✅ NEW - Optional (for Ghana, Kenya)
}
```

**UI Changes Needed:**
1. Add Currency dropdown (fetch from `/api/currencies` - you can create this endpoint)
2. Add optional "Bank Code" field (for Flutterwave direct integration)
3. Add optional "Branch Code" field (only show for Ghana, Kenya based on country_id)

**Example:**
```tsx
<select name="currency_id" required>
  <option value="">Select Currency</option>
  {currencies.map(c => (
    <option key={c.id} value={c.id}>
      {c.symbol} - {c.name}
    </option>
  ))}
</select>

<input 
  type="text" 
  name="bank_code" 
  placeholder="Bank Code (Optional)"
  help="Flutterwave bank code if applicable"
/>

{/* Show only for Ghana, Kenya */}
{['Ghana', 'Kenya'].includes(userCountry) && (
  <input 
    type="text" 
    name="destination_branch_code" 
    placeholder="Branch Code (Optional)"
  />
)}
```

### 2. Mobile Money Account Form
**Location:** Frontend form where users add mobile money accounts

**Add These Fields:**
```typescript
{
  provider: string,
  account_name: string,
  account_number: string, // Phone number
  currency_id: number, // ✅ NEW - Required
  network_code?: string // ✅ NEW - Optional (MTN, VDF, AIRTEL, etc.)
}
```

**UI Changes Needed:**
1. Add Currency dropdown
2. Add optional "Network Code" field with common options

**Example:**
```tsx
<select name="currency_id" required>
  <option value="">Select Currency</option>
  {currencies.map(c => (
    <option key={c.id} value={c.id}>
      {c.symbol} - {c.name}
    </option>
  ))}
</select>

<select name="network_code">
  <option value="">Auto-detect</option>
  <option value="MTN">MTN</option>
  <option value="VDF">Vodafone</option>
  <option value="AIRTEL">Airtel</option>
  <option value="TIGO">Tigo</option>
  <option value="ZAMTEL">Zamtel</option>
</select>
```

### 3. Withdrawal Request - No Changes Needed! ✅
The withdrawal request payload remains the same:
```typescript
// Bank Transfer
{
  amount: number,
  payment_method: 'bank_transfer',
  bank_id: number, // ID from bank_accounts table
  account_number: string,
  account_name: string
}

// Mobile Money
{
  amount: number,
  payment_method: 'mobile_money',
  mobile_money_account_id: number
}
```

### 4. Withdrawal Status Display
**Update withdrawal history to show new statuses:**
```typescript
const statusConfig = {
  1: { label: 'Pending', color: 'warning', icon: 'Clock' },
  2: { label: 'Processing', color: 'info', icon: 'Loader' },
  3: { label: 'Completed', color: 'success', icon: 'CheckCircle' },
  4: { label: 'Failed', color: 'danger', icon: 'XCircle' }
}
```

**Show Flutterwave Reference:**
```tsx
{withdrawal.flutterwave_reference && (
  <div className="text-xs text-gray-500">
    Ref: {withdrawal.flutterwave_reference}
  </div>
)}
```

**Show Failure Reason (if pending/failed):**
```tsx
{withdrawal.failure_reason && (
  <div className="text-xs text-red-500">
    Reason: {withdrawal.failure_reason}
  </div>
)}
```

---

## 📝 API Endpoints to Create

### 1. Currencies Endpoint (Optional but Recommended)
**Backend:** Create `CurrencyController.php`
```php
public function index()
{
    $currencies = Currency::where('active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'symbol', 'flag', 'country_id']);
    
    return response()->json($currencies);
}
```

**Route:**
```php
Route::get('/currencies', [CurrencyController::class, 'index']);
```

**Frontend API Client:**
```typescript
export const currenciesApi = {
  getAll: async (): Promise<Currency[]> => {
    const { data } = await apiClient.get<Currency[]>("/currencies");
    return data;
  }
};
```

### 2. Update BankAccountController Store Method
**Add currency_id to validation and store:**
```php
public function store(Request $request)
{
    $request->validate([
        'bank_id' => 'nullable|exists:banks,id',
        'bank_name' => 'required|string',
        'account_number' => 'required|string',
        'currency_id' => 'required|exists:currencies,id', // ✅ NEW
        'bank_code' => 'nullable|string', // ✅ NEW
        'destination_branch_code' => 'nullable|string', // ✅ NEW
    ]);
    
    $user = auth()->user();
    
    $bankAccount = BankAccount::create([
        'user_id' => $user->id,
        'bank_id' => $request->bank_id,
        'bank_name' => $request->bank_name,
        'account_number' => $request->account_number,
        'account_name' => $user->name,
        'currency_id' => $request->currency_id, // ✅ NEW
        'bank_code' => $request->bank_code, // ✅ NEW
        'destination_branch_code' => $request->destination_branch_code, // ✅ NEW
    ]);
    
    return response()->json($bankAccount);
}
```

### 3. Update MobileMoneyController Store Method
**Add currency_id and network_code:**
```php
public function store(Request $request)
{
    $request->validate([
        'provider' => 'required|string',
        'account_number' => 'required|string', // Phone number
        'account_name' => 'required|string',
        'currency_id' => 'required|exists:currencies,id', // ✅ NEW
        'network_code' => 'nullable|string', // ✅ NEW
    ]);
    
    $user = auth()->user();
    
    $mobileMoneyAccount = MobileMoneyAccount::create([
        'user_id' => $user->id,
        'provider' => $request->provider,
        'account_number' => $request->account_number,
        'account_name' => $request->account_name,
        'currency_id' => $request->currency_id, // ✅ NEW
        'network_code' => $request->network_code, // ✅ NEW
    ]);
    
    return response()->json($mobileMoneyAccount);
}
```

---

## 🔐 Environment Variables
**Add to `.env` if not already present:**
```bash
# Admin email for failure notifications
APP_ADMIN_EMAIL=admin@cadhance.com

# Flutterwave (should already exist)
FLUTTERWAVE_SECRET_KEY=your_secret_key
FLUTTERWAVE_PUBLIC_KEY=your_public_key
FLUTTERWAVE_BASE_URL=https://api.flutterwave.com/v3
```

**Update `config/app.php`:**
```php
'admin_email' => env('APP_ADMIN_EMAIL', 'admin@cadhance.com'),
```

---

## 🎯 User Flow

### Successful Withdrawal:
1. User requests withdrawal
2. Backend deducts balance
3. Flutterwave API call succeeds
4. Status = 3 (Completed)
5. User receives "Withdrawal Completed" email
6. Appears in "Completed" tab for admin

### Failed Withdrawal:
1. User requests withdrawal
2. Backend deducts balance
3. Flutterwave API call fails
4. Status = 1 (Pending)
5. Balance remains deducted
6. User receives "Withdrawal Processing" email
7. Admin receives "Manual Processing Required" email
8. Appears in "Pending" tab for admin
9. Admin exports CSV and processes in Flutterwave manually
10. Admin marks as completed (status = 3) or failed (status = 4)

---

## 🐛 Bug Fixes Included

### 1. Email Error Fixed
**Issue:** `An email must have a "To", "Cc", or "Bcc" header`

**Solution:** 
- Now checks if email exists and is valid before sending
- Uses `config('app.admin_email')` instead of `config('app.email')`
- Added proper validation: `filter_var($user->email, FILTER_VALIDATE_EMAIL)`

### 2. Migration Error Fixed
**Issue:** Duplicate column 'payment_method'

**Solution:** Deleted duplicate migration `2026_06_10_113958_add_processing_fields_to_withdrawals_table.php`

---

## ✅ Testing Checklist

### Backend Testing:
- [ ] Run migration: `php artisan migrate`
- [ ] Create test bank account with currency_id
- [ ] Create test mobile money account with currency_id
- [ ] Request withdrawal (should auto-process)
- [ ] Check withdrawal status in admin panel
- [ ] Test CSV export for each country
- [ ] Test marking as completed/failed

### Frontend Testing:
- [ ] Add bank account form includes currency dropdown
- [ ] Add mobile money form includes currency dropdown
- [ ] Withdrawal request works for both bank and mobile money
- [ ] Withdrawal history shows correct status
- [ ] Status updates in real-time

---

## 📚 Additional Notes

### Flutterwave Bank Codes
Common codes for reference:
- Nigeria: Check [Flutterwave Bank List](https://developer.flutterwave.com/docs/miscellaneous/banks/)
- Ghana, Kenya, etc.: Use Flutterwave API to get bank codes

### Mobile Money Network Codes
- MTN: `MTN`
- Vodafone: `VDF`
- Airtel: `AIRTEL`
- Tigo: `TIGO`
- Zamtel: `ZAMTEL`

### Currency Symbols
Ensure your currencies table has correct symbols:
- NGN: ₦
- GHS: GH₵
- KES: KSh
- UGX: USh
- TZS: TSh
- ZAR: R
- USD: $

---

## 🚀 Next Steps

1. **Run the migration**
2. **Update frontend forms** to include currency_id
3. **Create currencies API endpoint**
4. **Test with a small withdrawal**
5. **Monitor logs** for any Flutterwave API errors
6. **Update admin view** to show new tabs (Processing, Failed)

---

## Support
For issues or questions, check:
- Laravel logs: `storage/logs/laravel.log`
- Flutterwave dashboard: [https://dashboard.flutterwave.com](https://dashboard.flutterwave.com)
- Withdrawal controller logs for detailed error messages
