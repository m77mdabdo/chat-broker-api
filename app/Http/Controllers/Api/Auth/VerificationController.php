<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerfiyMail;
use App\Models\User;
use App\Models\VerificationCode;
use App\Repositories\Otp\OtpRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    private $OTPRepository;

    public function __construct(OtpRepositoryInterface $OTPRepository)
    {
        $this->OTPRepository = $OTPRepository;

    }

    public function OTPGentrator(Request $request){
        if(auth()->user()->email){
            $generateOTP = $this->OTPRepository->generateOtp();
            if($generateOTP ==  false){
                return response()->json(["message"=>'You have already sent OTP'],400);
            }
            Mail::to(auth()->user()->email)->send(new VerfiyMail($generateOTP->otp));
            return response()->json(["message"=>'We send email to you witn otp'],200);
        }else{
            return response()->json(["message"=>'Email is required'],401);
        }

    }
    public function verfiy(Request $request){
        $request->validate([
            'otp' => 'required'
        ]);
        #Validation Logic
        $verificationCode = VerificationCode::where('user_id', auth()->user()->id)->where('otp', $request->otp)->first();

        $now = Carbon::now();
        if (!$verificationCode) {
            return response()->json(['message'=>'Your OTP is not correct'],401);
        }elseif($verificationCode && $now->isAfter($verificationCode->expire_at)){
            return response()->json(['message'=>'Your OTP is expired'],401);
        }
        auth()->user()->is_verified = 1;
        auth()->user()->save();
        $verificationCode->delete();

        return response()->json(['message'=>'Thank you for validation'],200);

    }
}
