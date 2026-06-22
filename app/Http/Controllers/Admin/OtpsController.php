<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\Request;

class OtpsController extends Controller
{
    public function index()
    {
        $otps = Otp::with(['user', 'otp_type'])->latest()->get();
        return view('admin.otps.index', compact('otps'));
    }

    public function destroy(Otp $otp)
    {
        $otp->delete();
        flash()->success('OTP deleted successfully.');
        return redirect()->route('otps.index');
    }
}
