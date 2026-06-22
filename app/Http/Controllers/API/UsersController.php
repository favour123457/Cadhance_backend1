<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use App\Mail\GeneralMail;
use App\Models\DeletedUser;
use App\Models\NotificationSetting;
use App\Models\Otp;
use App\Models\User;
use App\Models\AffiliateCommission;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $auth = Auth::attempt([
            'email' => $email,
            'password' => $password,
        ]);

        $auth = Auth::attempt([
            filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username' => $email,
            'password' => $password,
        ]);



        if ($auth) {
            $checkDelete = DeletedUser::where('user_id', auth()->user()->id)->first();
            if ($checkDelete) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid account!'
                ], 403);
            }

            $user = auth()->user();

            //generate jwt token
            $token = JWTAuth::fromUser($user);

            // OTP
            $otp = mt_rand(100000, 999999);

            Otp::create([
                'user_id' => $user->id,
                'user' => $user->email,
                'otp_type_id' => 1,
                'code' => $otp,
            ]);

            //send **email otp to user
            // sendSMS($user->phone, 'Login OTP', 'Your login OTP is '.$otp);
            // sendSmsOTp($user->phone,$otp);
            //send otp to email
            // Mail::send(new GeneralMail($user->first_name, $email, 'Sign In OTP', 'Enter the OTP to access your app. <br/> OTP: ' . $otp));


            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' =>  new UsersResource($user),
                'otp' => $otp,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Incorrect credentials, check your credentials and try again!',
            ], 403);
        }
    }

    public function register(Request $request)
    {
        $name = $request->name;
        $account_type_id = $request->account_type_id;
        $email    = $request->email;
        $password = $request->password;
        $user_type_id = 1; // default to regular user
        $offer_type_id = $request->offer_type_id ?? 4; // 4 = "None" (default for clients)
        $incomingReferralCode = trim((string) $request->referral_code);

        //let's check if the email is already taken
        $check_email = User::where('email', $email)->first();
        if ($check_email) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email Already register with another user!'
            ], 403);
        }

        //let's check if the phone is already taken
        // $check_phone = User::where('phone', $phone)->first();
        // if ($check_phone) {
        //     return response()->json([
        //         'status' => 'failed',
        //         'message' => 'Phone Already register with another user!'
        //     ], 403);
        // }

        $referrer = null;
        if ($incomingReferralCode !== '') {
            $referrer = User::where('referral_code', $incomingReferralCode)->first();

            if (!$referrer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid referral code supplied!'
                ], 403);
            }
        }


        $user = User::create([
            'role_id' => 2,
            'user_type_id' => $user_type_id,
            'name' => $name,
            'account_type_id' => $account_type_id,
            'email' => $email,
            'password' => Hash::make($password),
            'profile_picture' => 'users/default.png',
            'offer_type_id' => $offer_type_id,
            'referred_by_user_id' => $referrer?->id,
            'referral_code' => $this->generateUniqueReferralCode(),
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        //for notification
        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type_id' => 1,
            'status' => 1,
        ]);
        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type_id' => 2,
            'status' => 1,
        ]);
        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type_id' => 3,
            'status' => 1,
        ]);


        //generate jwt token
        $token = JWTAuth::fromUser($user);

        // OTP
        $otp = mt_rand(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'user' => $user->email,
            'otp_type_id' => 2,
            'code' => $otp,
        ]);



        //send detailed welcome email to user
        $msg = "Hi $name, welcome to our platform! We're excited to have you on board.";
        $subject = 'Welcome to our platform!';


        Mail::send(new GeneralMail($name, $email, $subject, $msg));


        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' =>  new UsersResource($user),
            'otp' => $otp,
        ]);
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function my_referrals()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $referrals = User::where('referred_by_user_id', $user->id)
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        $totalCommission = AffiliateCommission::where('referrer_user_id', $user->id)
            ->sum('commission_amount');

        $pendingCommission = AffiliateCommission::where('referrer_user_id', $user->id)
            ->whereNull('paid_at')
            ->sum('commission_amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'referral_code'      => $user->referral_code,
                'total_referrals'    => $referrals->count(),
                'referrals'          => $referrals,
                'total_commission'   => $totalCommission,
                'pending_commission' => $pendingCommission,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();


        $input = $request->except('password', 'password_confirmation', 'image', 'email');
        if (!$request->filled('password')) {
            $user->fill($input)->save();
        } else {
            $user->password = bcrypt($request->password);
            $user->fill($input)->save();
        }


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('users', $fileName, 'r2');

            $complete_path = 'users/' . $fileName;
            $user->update([
                'profile_picture' => $complete_path,
            ]);
        } else {
            $complete_path = $user->profile_picture;
        }


        return response()->json([
            'status' => 'success',
            'image' => $complete_path,
            'user' => new UsersResource($user)
        ]);
    }

}
