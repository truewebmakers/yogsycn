<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\onboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    /**
     * Get Onboarding Sliders.
     */
    public function getOnboardings()
    {
        try {
            $onboardings = onboarding::all();
            $response = [
                'status_code' => 200,
                'data' => $onboardings,
                'message' => 'Onboardings retrieved successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve onboarding sliders.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Add Onboarding Slider.
     */
    public function addOnboarding(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required'
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
            $title = trim(strtolower($request->title));
            $existingOnboarding = onboarding::whereRaw('LOWER(TRIM(title)) = ?', [$title])->first();
            if ($existingOnboarding) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Onboarding with this title already exist.'
                ];
                return response()->json($response, 200);
            }
            $onboarding = new onboarding();
            $onboarding->title = $request->title;
            $onboarding->description = $request->description;
            $onboarding->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Onboarding slider added successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to add onboarding slider.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update Onboarding Slider.
     */
    public function updateOnboarding(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'slider_id' => 'required'
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
            $slider = onboarding::find($request->slider_id);
            if (!$slider) {
                $response = [
                    'status_code' => 404,
                    'status' => 'Not Found',
                    'message' => 'Onboarding slider not found.'
                ];
                return response()->json($response, 404);
            }
            $title = trim(strtolower($request->title));
            $existingOnboarding = onboarding::whereRaw('LOWER(TRIM(title)) = ?', [$title])->where('_id', '!=', $request->slider_id)->first();

            if ($existingOnboarding) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Onboarding slider already exists'
                ];
                return response()->json($response, 200);
            }

            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Onboarding slider updated successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update onboarding slider.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Delete Onboarding Slider.
     */
    public function deleteOnboarding(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slider_id' => 'required'
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
            $slider = onboarding::find($request->slider_id);
            if (!$slider) {
                $response = [
                    'status_code' => 404,
                    'status' => 'Not Found',
                    'message' => 'Onboarding slider not found.'
                ];
                return response()->json($response, 404);
            }

            $slider->delete();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Onboarding slider deleted successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to delete onboarding slider.'
            ];
            return response()->json($response, 500);
        }
    }
}
