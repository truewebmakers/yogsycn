<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\user_otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
                $user->name = null;
                $user->email = null;
                $user->gender = null;
                $user->dob = null;
                $user->aniversary_date = null;
                $user->disable = 0;
                $user->save();
            } else {
                $user = $existingUser;
            }
            // $token = $user->createToken('UserToken', ['user'])->accessToken;
            // $token = $user->createToken('auth_token')->accessToken;
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'data' => $user,
                // 'token' => $token,
                'message' => 'User logged in successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to login user.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update Profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'email' => 'email',
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
            $user = User::where('_id', $request->user_id)->where('disable', 0)->first();

            if (!$user) {
                $response = [
                    'status_code' => 404,
                    'message' => 'User not found.'
                ];
                return response()->json($response, 404);
            }

            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('phoneno')) {
                $user->phoneno = $request->phoneno;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('dob')) {
                $user->dob = $request->dob;
            }
            if ($request->has('aniversary_date')) {
                $user->aniversary_date = $request->aniversary_date;
            }
            if ($request->has('gender')) {
                $user->gender = $request->gender;
            }
            if ($request->has('image')) {
                $oldImage = $user->image;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = 'users_' . $user->_id . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('public/users', $imageName);

                    // Update product image
                    $user->image = 'storage/app/public/users/' . $imageName;
                    $path = str_replace('storage/app/', '', $oldImage);
                    if ($path !== $imagePath) {
                        if ($oldImage && Storage::exists($path)) {
                            Storage::delete($path);
                        }
                    }
                } else {
                    $path = str_replace('storage/app/', '', $oldImage);
                    if ($path) {
                        if ($oldImage && Storage::exists($path)) {
                            Storage::delete($path);
                        }
                    }
                    $user->image = null;
                }
            }
            $user->save();

            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'data' => $user,
                'message' => 'User profile updated successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            print($e);
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update user profile.'
            ];
            return response()->json($response, 500);
        }
    }
}
