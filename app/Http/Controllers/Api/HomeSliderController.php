<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\home_slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeSliderController extends Controller
{
    /**
     * Get all sliders for customer.
     */
    public function getSliders()
    {
        try {
            $sliders = home_slider::all();
            $response = [
                'status_code' => 200,
                'data' => $sliders,
                'message' => 'Sliders retrieved successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'message' => 'Failed to retrieve sliders.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * add Slider.
     */
    public function addSlider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
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
            $slider = new home_slider();
            $slider->slider = '';
            $slider->save();
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'home_slider_' . $slider->_id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('public/home_sliders', $imageName);

                // Update the slider with the image path
                $slider->slider = 'storage/app/public/home_sliders/' . $imageName;
                $slider->save();
            }
            else
            {
                DB::rollBack();
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' =>'Image field must contain valid image'
                ];
                return response()->json($response, 400);
            }
            DB::commit();
            $response = [
                'status_code' => 200,
                'message' => 'Slider added successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath) && Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            $response = [
                'status_code' => 500,
                'message' => 'Failed to add slider.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * delete Slider.
     */
    public function deleteSlider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slider_id' => 'required',
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
            $slider = home_slider::find($request->slider_id);
            if(!$slider)
            {
                $response = [
                    'status_code' => 404,
                    'message' => 'Slider not found',
                ];
                return response()->json($response, 404);
            }
            $image = $slider->slider;
            $slider->delete();
            $path = str_replace('storage/app/', '', $image);
            if ($image && Storage::exists($path)) {
                Storage::delete($path);
            }
            DB::commit();
            $response = [
                'status_code' => 200,
                'message' => 'Slider deleted successfully.'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'message' => 'Failed to delete slider.'
            ];
            return response()->json($response, 500);
        }
    }
}
