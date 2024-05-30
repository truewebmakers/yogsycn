<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\product_size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductSizeController extends Controller
{
    /**
     * Add Product Size.
     */
    public function addProductSize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required',
            'price' => 'required',
            'product_id' => 'required',
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
            $product = product::find($request->product_id);
            if (!$product) {
                $response = [
                    'status_code' => 404,
                    'message' => 'product not found.'
                ];
                return response()->json($response, 404);
            }
            $name = trim($request->size);
            $existingSize = product_size::whereRaw('LOWER(TRIM(size)) = ?', [strtolower($name)])->where('product_id', $product->_id)->first();
            if ($existingSize) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'This product size already exist in ' . $product->name
                ];
                return response()->json($response, 200);
            }
            $product_size = new product_size();
            $product_size->size = $name;
            $product_size->price = trim($request->price);
            $product_size->product_id = $product->_id;
            $product_size->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product size added successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to add product size.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update Product Size.
     */
    public function updateProductSize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required',
            'price' => 'required',
            'size_id' => 'required',
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
            $product_size = product_size::find($request->size_id);
            if (!$product_size) {
                $response = [
                    'status_code' => 404,
                    'message' => 'product size not found.'
                ];
                return response()->json($response, 404);
            }
            $name = trim($request->size);
            $existingSize = product_size::whereRaw('LOWER(TRIM(size)) = ?', [strtolower($name)])->where('_id', '!=', $product_size->_id)->where('product_id', $product_size->product_id)->first();
            if ($existingSize) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'This product size already exist'
                ];
                return response()->json($response, 200);
            }

            $product_size->size = $name;
            $product_size->price = trim($request->price);
            $product_size->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product size updated successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update product size.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Delete Product Size.
     */
    public function deleteProductSize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size_id' => 'required',
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
            $product_size = product_size::find($request->size_id);
            if (!$product_size) {
                $response = [
                    'status_code' => 404,
                    'message' => 'product size not found.'
                ];
                return response()->json($response, 404);
            }

            $product_size->delete();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product size deleted successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update delete product size.'
            ];
            return response()->json($response, 500);
        }
    }
}
