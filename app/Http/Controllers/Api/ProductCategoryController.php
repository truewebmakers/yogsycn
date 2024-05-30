<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\product_category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoryController extends Controller
{
    /**
     * Add Product Category.
     */
    public function addProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required'
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
            $name = trim($request->name);
            $existingCategory = product_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();

            if ($existingCategory) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Product category already exists'
                ];
                return response()->json($response, 200);
            }
            if (!$request->hasFile('image')) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Image field is required'
                ];
                return response()->json($response, 400);
            }

            $product_category = new product_category();
            $product_category->name = $name;
            $product_category->image = '';
            $product_category->save();

            $image = $request->file('image');
            $imageName = 'product_categories_' . $product_category->_id . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/product_categories', $imageName);

            // Update product category image
            $product_category->image = 'storage/app/public/product_categories/' . $imageName;
            $product_category->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product category added successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath) && Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to add product category.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update Product Category.
     */
    public function updateProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
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
            $category = product_category::find($request->category_id);
            if (!$category) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product category not found'
                ];
                return response()->json($response, 404);
            }
            $name = trim($request->name);
            $existingCategory = product_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->where('_id', '!=', $request->category_id)->first();

            if ($existingCategory) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Product category already exists'
                ];
                return response()->json($response, 200);
            }
            if (!$request->hasFile('image')) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Image field is required'
                ];
                return response()->json($response, 400);
            }
            $oldImage = $category->image;
            $category->name = $name;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'product_categories_' . $category->_id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('public/product_categories', $imageName);


                $path = str_replace('storage/app/', '', $oldImage);
                if ($path !== $imagePath) {
                    if ($oldImage && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }

                // Update product category image
                $category->image = 'storage/app/public/product_categories/' . $imageName;
            }
            $category->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product category updated successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update product category.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Delete Product Category.
     */
    public function deleteProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
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
            $category = product_category::find($request->category_id);
            if (!$category) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product category not found'
                ];
                return response()->json($response, 404);
            }

            $productCount = product::where('product_category_id', $request->category_id)->count();
            if ($productCount > 0) {
                $response = [
                    'status_code' => 400,
                    'message' => 'Product category cannot be delete because it has associated products. Please move the products to another category first.'
                ];
                return response()->json($response, 404);
            }
            $image = $category->image;
            $category->delete();
            $path = str_replace('storage/app/', '', $image);
            if ($image && Storage::exists($path)) {
                Storage::delete($path);
            }
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product category deleted successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to delete product category.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Get all product Categories.
     */
    public function getAllProductCategories()
    {
        try {
            $categories = product_category::whereHas('products', function (Builder $query) {
                $query->where('disable', 0);
            })->get();

            $response = [
                'status_code' => 200,
                'data' => $categories,
                'message' => 'Product categories retrieved successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve product categories.'
            ];
            return response()->json($response, 500);
        }
    }
}
