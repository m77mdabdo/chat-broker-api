<?php

namespace App\Repositories\Otp;

use App\Models\User;
use App\Models\user\Detail;
use App\Models\VerificationCode;
use App\Repositories\Otp\OtpRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtpRepository implements OtpRepositoryInterface
{
    public function generateOtp(){
        $user = User::where('email', auth()->user()->email)->first();
        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::where('user_id', $user->id)->latest()->first();
        $now = Carbon::now();
        if($verificationCode && $now->isBefore($verificationCode->expire_at)){
            return false;
        }

        // Create a New OTP
        return VerificationCode::create([
            'user_id' => $user->id,
            'otp' => rand(123456, 999999),
            'expire_at' => Carbon::now()->addMinutes(10)
        ]);
    }

}
