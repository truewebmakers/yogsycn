<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\user_otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class OTPController extends Controller
{

    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        // $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }
    /**
     * send OTP.
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneno' => 'required',
            // 'phoneno' => 'required|regex:/[0-9]{10}/',
        ]);
        if ($validator->fails()) {
            $response = [
                'status_code' => 400,
                'status' => 'Fail',
                'message' => $validator->messages()
            ];
            return response()->json($response, 400);
        }
        DB::beginTransaction();
        try {
            user_otp::where('phoneno', $request->phoneno)->delete();
            $otp = rand(100000, 999999);
            $message = "Hi Your login OTP for Yours Second Wife Restaurent App is $otp";
            $this->twilio->messages->create($request->phoneno, [
                // 'from' => env('TWILIO_PHONE_NUMBER'),
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);
            $length = 6;
            do {
                $code = substr(str_shuffle('123456789abcdefghijklmnopqrstuvwxyz'), 1, $length);
            } while (user_otp::where('_id', $code)->exists());
            $user_otp = new user_otp();
            $user_otp->_id = $code;
            $user_otp->otp = $otp;
            $user_otp->phoneno = $request->phoneno;
            $user_otp->expire_time = now()->addSeconds(30);
            // $user_otp->expire_time = now()->addMinutes(30);
            $user_otp->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'OTP sent successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to send OTP.'
            ];
            return response()->json($response, 500);
        }
    }
}
