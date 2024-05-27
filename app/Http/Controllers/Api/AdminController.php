<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\admin;
use App\Traits\GenerateCodeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use GenerateCodeTrait;
    /**
     * Admin Registration.
     */
    public function adminRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
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
            $existingUser = admin::where('username', $request->username)->first();
            if ($existingUser) {
                $response = [
                    'status_code' => 400,
                    'message' => 'User already exist'
                ];
                return response()->json($response, 200);
            }
            $code = $this->generatSmallLettersCode(6);
            $admin = new admin();
            $admin->_id = $code;
            $admin->username = $request->username;
            $admin->password = $request->password;
            $admin->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'user'=>$admin,
                'code'=>$code,
                
                'message' => 'User registered successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to Login.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Admin Login.
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'status_code' => 400,
                'status' => 'Fail',
                'message' => $validator->messages()
            ];
            return response()->json($response, 400);
        }
        try {
            $user = admin::where('username', $request->username)->first();
            if (!$user) {
                $response = [
                    'status_code' => 404,
                    'message' => 'User not found'
                ];
                return response()->json($response, 200);
            }
            if ($request->password === $user->password) {
                $response = [
                    'status_code' => 200,
                    'data' => $user,
                    'message' => 'Login successfully'
                ];
                return response()->json($response, 200);
            } else {
                $response = [
                    'status_code' => 401,
                    'message' => 'Invalid password',
                ];
                return response()->json($response, 200);
            }
            // $code=$this->generatSmallLettersCode(6);
            // DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Logged in successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to Login.'
            ];
            return response()->json($response, 500);
        }
    }
}
