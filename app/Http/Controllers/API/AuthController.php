<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use App\Http\Resources\WalletHistorysResource;
use App\Mail\GeneralMail;
use App\Jobs\SendEmail;
use App\Models\DeletedUser;
use App\Models\Notification;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function fetchAllUsers()
    {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'users' =>  UsersResource::collection($users),
        ]);
    }
    
    public function recoverPasswordOne(Request $request)
    {
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user!'
            ], 403);
        }

        $checkDelete = DeletedUser::where('user_id', $user->id)->first();
        if ($checkDelete) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid account!'
            ], 403);
        }

        // OTP
        $otp = mt_rand(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'user' => $email,
            'otp_type_id' => 3,
            'code' => $otp,
        ]);

        //send **email otp to user
        Mail::send(new GeneralMail($user->first_name, $email, 'Forget Password OTP', 'Your Reset Password OTP code is '.$otp));

        return response()->json([
            'status' => 'success',
            'message' => 'An OTP has been sent to your email'
        ]);
    }

    public function recoverPasswordTwo(Request $request)
    {
        $email = $request->email;
        $otp = $request->otp;
        $password = $request->password;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user!'
            ], 403);
        }

        $checkOtp = Otp::where('user_id', $user->id)
            ->where('otp_type_id', 3)
            ->where('code', $otp)
            ->first();

        if (!$checkOtp) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid OTP, please check your email and enter the correct OTP'
            ], 403);
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        $checkOtp->update([
            'used' => 1,
        ]);
        //send **email otp to user
        // sendMail($email, 'Reset Password Notification', 'Your password has been reset successfully!');
        // Mail::send(new GeneralMail('Reset Password Notification', 'Your password has been reset successfully!', $user));

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Reset Password Notification',
            'message' => 'Your password has been reset successfully!',
        ]);

        //generate jwt token
        // $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been reset successfully!'
        ]);
    }

    public function sendEmailVerificationCode(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email already verified!'
            ], 403);
        }

        // Generate OTP
        $otp = mt_rand(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'user' => $user->email,
            'otp_type_id' => 4, // Email verification type
            'code' => $otp,
        ]);

        // Send email with OTP
        Mail::send(new GeneralMail(
            $user->first_name, 
            $user->email, 
            'Email Verification Code', 
            'Please verify your email address to complete your registration. Your verification code is: <br/><strong style="font-size: 24px; color: #3b82f6;">' . $otp . '</strong><br/><br/>This code will expire in 10 minutes.'
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code has been sent to your email'
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();
        $code = $request->code;

        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email already verified!'
            ], 403);
        }

        // Check OTP
        $checkOtp = Otp::where('user_id', $user->id)
            ->where('otp_type_id', 4)
            ->where('code', $code)
            ->where('used', 0)
            ->first();

        if (!$checkOtp) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid verification code. Please check your email and try again.'
            ], 403);
        }

        // Check if OTP is expired (10 minutes)
        if ($checkOtp->created_at->addMinutes(10)->isPast()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Verification code has expired. Please request a new code.'
            ], 403);
        }

        // Update user email_verified_at
        $user->update([
            'email_verified_at' => now(),
        ]);

        // Mark OTP as used
        $checkOtp->update([
            'used' => 1,
        ]);

        // Send notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Email Verified',
            'message' => 'Your email has been successfully verified!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully!',
            'user' => new UsersResource($user->fresh())
        ]);
    }

    public function refresh(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        return response()->json([
            'user' =>  new UsersResource($user),
        ]);
    }

    public function chnagePassword(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $confirm_password = $request->confirm_password;
        $email = $user->email;

        $auth = Auth::attempt([
            filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username' => $email,
            'password' => $old_password,
        ]);


        if (!$auth) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Your old password is incorrect!',
            ], 403);
        }

        if ($new_password != $confirm_password) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Password mismatch!',
            ], 403);
        }

        $user->update([
            'password' => Hash::make($new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully!',
        ]);
    }

    public function chnagePin(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $old_pin = $request->old_pin;
        $new_pin = $request->new_pin;
        $confirm_pin = $request->confirm_pin;

        if ($new_pin != $confirm_pin) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Pin mismatch!',
            ], 403);
        }

        $user->update([
            'transaction_pin' => $new_pin,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pin changed successfully!',
        ]);
    }

    public function deleteUser(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        if ($user) {
            DeletedUser::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function smsVerificationOne(Request $request)
    {

        $phone = $request->phone;

        // OTP
        $otp = mt_rand(100000, 999999);

        if ($phone == '+2348091558652' || $phone == '+2348145951901') {
            $otp = 123456;
        }

        Otp::create([
            'user_id' => 0,
            'otp_type_id' => 2,
            'code' => $otp,
            'user' => $phone,
        ]);

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user!',
            ], 403);
        }

        $msg = 'Your ' . config('app.name') . ' Login code is: ' . $otp . '. Please, do not share with another person.';
        // try {
        //     sendNewOtp($user->phone, $msg);
        // } catch (\Throwable $th) {
        // }
        Mail::send(new GeneralMail($user->first_name, $user->email, config('app.name') . ' Sign In OTP', 'Enter the OTP to access your app. <br/> OTP: ' . $otp . ' Please, do not share with another person.'));


        return response()->json([
            'status' => 'success',
            'message' => 'OTP has been sent successfully!',
            'code' => $otp
        ]);
    }

    public function smsVerificationTwo(Request $request)
    {
        $phone = $request->phone;
        $code = $request->code;

        // OTP
        $otp = Otp::where('user', $phone)->where('code', $code)->where('used', 0)->first();

        if (!$otp) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid OTP!'
            ], 403);
        }

        $otp->update([
            'used' => 1,
        ]);

        $user = User::where('phone', $phone)->first();

        if ($user) {
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid user!',
            ], 403);
        }

        //generate jwt token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' =>  new UsersResource($user),
            'otp' => $otp,
        ]);
    }

    public function updateGeoLocation(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $longitude = $request->longitude;
        $latitude = $request->latitude;

        $user->update([
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Geo location updated successfully!'
        ]);
    }

    public function getUserByPhoneNumber(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $phone = $request->phone;

        $duser = User::where('phone', $phone)->first();

        if (!$duser) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No user with this phone number'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'user' =>  new UsersResource($duser),
        ]);
    }
}
