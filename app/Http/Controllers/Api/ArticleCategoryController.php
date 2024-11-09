<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\article;
use App\Models\article_category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArticleCategoryController extends Controller
{
    /**
     * Add Article Category
     */
    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $name = trim($request->name);
            $existingCategory = article_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
            if ($existingCategory) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Article Category already exist'
                ], 400);
            }
            $category = new article_category();
            $category->name = $name;
            $category->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Article Category added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add article category'
            ], 500);
        }
    }

    /**
     * Update Article Category
     */
    public function updateCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $category = article_category::findOrFail($request->category_id);
            if ($request->filled('name')) {
                $name = trim($request->name);
                $existingCategory = article_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->where('id', '!=', $request->category_id)->first();

                if ($existingCategory) {
                    return response()->json([
                        'status_code' => 400,
                        'message' => 'Article Category already exist'
                    ], 400);
                }
                $category->name = $name;
            }
            $category->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Article Category updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update article category'
            ], 500);
        }
    }

    /**
     * Delete Article Category
     */
    public function deleteCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $category = article_category::findOrFail($request->category_id);
            $articles = article::where('category_id', $category->id)->get();

            // Delete associated images
            foreach ($articles as $article) {
                if ($article->image) {
                    $image = parse_url($article->image, PHP_URL_PATH);
                    $path = str_replace('storage/app/', '', $image);
                    if ($image && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                if ($article->author_image) {
                    $image = parse_url($article->author_image, PHP_URL_PATH);
                    $path = str_replace('storage/app/', '', $image);
                    if ($image && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
            }
            article::where('category_id', $category->id)->delete();
            $category->delete();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Article Category and Related Articles deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to delete article category'
            ], 500);
        }
    }

    /**
     * Get All Article Categories for admin
     */
    public function getAllCategoriesAdmin()
    {
        try {
            $categories = article_category::all();
            return response()->json([
                'status_code' => 200,
                'data' => $categories,
                'message' => 'Article Categories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve article categories'
            ], 500);
        }
    }

    /**
     * Get All Article Categories for user
     */
    public function getAllCategoriesUser()
    {
        try {
            $categories = article_category::all();
            return response()->json([
                'status_code' => 200,
                'data' => $categories,
                'message' => 'Article Categories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve article categories'
            ], 500);
        }
    }
}
