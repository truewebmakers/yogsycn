<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\product_category;
use App\Models\product_size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Add Product.
     */
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required',
            'veg' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            // 'sizes' => 'array'
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
            $existingProduct = product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->where('product_category_id', $category->_id)->first();

            if ($existingProduct) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Product already exists in ' . $category->name
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

            $product = new product();
            $product->name = $name;
            $product->veg = $request->veg;
            $product->price = $request->price;
            $product->description = $request->has('description') ? $request->description : null;
            $product->best_seller = $request->has('best_seller') ? $request->best_seller : 0;
            $product->disable = 0;
            $product->image = '';
            $product->product_category_id = $request->category_id;

            $product->save();

            $image = $request->file('image');
            $imageName = 'products_' . $product->_id . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/products', $imageName);

            // Update product image
            $product->image = 'storage/app/public/products/' . $imageName;
            $product->save();


            if ($request->has('sizes')) {
                if (is_string($request->sizes)) {
                    // Convert the string to an array
                    $sizeArray = json_decode($request->sizes, true);
                } else {
                    // Assume $input is already an array
                    $sizeArray = $request->sizes;
                }
                if (is_array($sizeArray) && !empty($sizeArray)) {
                    foreach ($sizeArray as $size) {
                        if (isset($size['size']) && isset($size['price'])) {
                            $newSize = new product_size();
                            $newSize->size = $size['size'];
                            $newSize->price = $size['price'];
                            $newSize->product_id = $product->_id;
                            $newSize->save();
                        }
                    }
                }
            }

            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product added successfully'
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
                'message' => 'Failed to add product.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update Product.
     */
    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'name' => 'required',
            'veg' => 'required',
            'price' => 'required',
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
            $product = product::find($request->product_id);
            if (!$product) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product not found'
                ];
                return response()->json($response, 404);
            }
            $category = product_category::find($request->category_id);
            if (!$category) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product category not found'
                ];
                return response()->json($response, 404);
            }

            $name = trim($request->name);
            $existingProduct = product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->where('_id', '!=', $request->product_id)->first();

            if ($existingProduct) {
                $response = [
                    'status_code' => 400,
                    'status' => 'Fail',
                    'message' => 'Product already exists in ' . $category->name
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
            $oldImage = $product->image;

            $product->name = $name;
            $product->veg = $request->veg;
            $product->price = $request->price;
            $product->product_category_id = $request->category_id;
            $product->description = $request->description;
            if ($request->has('best_seller')) {
                $product->best_seller = $request->best_seller;
            }
            if($request->hasFile('image'))
            {
                $image = $request->file('image');
                $imageName = 'products_' . $product->_id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('public/products', $imageName);
    
                $path = str_replace('storage/app/', '', $oldImage);
                if ($path !== $imagePath) {
                    if ($oldImage && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                // Update product image
                $product->image = 'storage/app/public/products/' . $imageName;
            }
            $product->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product updated successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to update product.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Delete Product.
     */
    public function deleteProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
                    'message' => 'Product not found'
                ];
                return response()->json($response, 404);
            }

            $product->disable = 1;
            $product->save();
            DB::commit();
            $response = [
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Product disabled successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to disable product.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Get all product by category admin.
     */
    public function getAllProductByCategoryAdmin(Request $request)
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
        try {
            $category = product_category::find($request->category_id);

            if (!$category) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product category not found'
                ];
                return response()->json($response, 404);
            }

            $products = product::where('product_category_id', $category->_id)->get()
                ->each(function ($product) {
                    $product->sizes = product_size::where('product_id', $product->_id)->get()->makeHidden('product_id');
                });

            $response = [
                'status_code' => 200,
                'data' => $products,
                'message' => 'Products retrieved successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve products.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Get all product by category customer.
     */
    public function getAllProductByCategoryCustomer(Request $request)
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
        try {
            $category = product_category::find($request->category_id);

            if (!$category) {
                $response = [
                    'status_code' => 404,
                    'message' => 'Product category not found'
                ];
                return response()->json($response, 404);
            }

            $products = product::where('product_category_id', $category->_id)->where('disable', 0)->get()
                ->each(function ($product) {
                    $product->sizes = product_size::where('product_id', $product->_id)->get()->makeHidden('product_id');
                })
                ->makeHidden('disable');

            $response = [
                'status_code' => 200,
                'data' => $products,
                'message' => 'Products retrieved successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve products.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Get best seller products.
     */
    public function getBestSeller()
    {
        try {
            $products = product::where('best_seller', 1)->where('disable', 0)->get()
                ->each(function ($product) {
                    $product->sizes = product_size::where('product_id', $product->_id)->get()->makeHidden('product_id');
                })
                ->makeHidden('disable');

            $response = [
                'status_code' => 200,
                'data' => $products,
                'message' => 'Best seller products retrieved successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve best seller products.'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Get best seller products.
     */
    public function searchProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
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
            $value = $request->value;
            $products = product::where('name', 'LIKE', "%$value%")->where('disable', 0)->get()
                ->each(function ($product) {
                    $product->sizes = product_size::where('product_id', $product->_id)->get()->makeHidden('product_id');
                })
                ->makeHidden('disable');

            $response = [
                'status_code' => 200,
                'data' => $products,
                'message' => 'Serached products retrieved successfully'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Failed to retrieve searched products.'
            ];
            return response()->json($response, 500);
        }
    }
}
