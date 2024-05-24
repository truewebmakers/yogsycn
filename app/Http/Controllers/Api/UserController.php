<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\user_otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class UserController extends Controller
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
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
            $message = "Your OTP is $otp";
            $this->twilio->messages->create($request->phoneno, [
                'from' => env('TWILIO_PHONE_NUMBER'),
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

    /**
     * Verify OTP.
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneno' => 'required',
            'otp' => 'required'
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
            $otp = user_otp::where('phoneno', $request->phoneno)->latest()->first();

            if (!$otp) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'OTP not found.'
                ];
                return response()->json($response, 400);
            }

            // Check if the OTP has expired
            if (now()->greaterThan($otp->expire_time)) {
                $response = [
                    'status_code' => 401,
                    'status' => 'Fail',
                    'message' => 'OTP has expired.'
                ];
                return response()->json($response, 200);
            }

            if ($otp->otp !== $request->otp) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Invalid OTP.'
                ];
                return response()->json($response, 200);
            }

            $existingUser = User::where('phoneno', $request->phoneno)->first();
            if (!$existingUser) {
                $user = new User();
                $user->phoneno = $request->phoneno;
                $user->save();
            } else {
                $user = $existingUser;
            }
            $token = $user->createToken('auth_token')->accessToken;
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'data' => $user,
                'token' => $token,
                'message' => 'User logged in successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            print($e);
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to login user.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
