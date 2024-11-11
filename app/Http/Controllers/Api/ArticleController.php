<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\article;
use App\Models\article_category;
use App\Models\pose_category;
use App\Models\yoga_pose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ImageHandleTrait;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    use ImageHandleTrait;

    /**
     * Add Article
     */
    public function addArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'is_latest' => 'boolean',
            'is_expert_approved' => 'boolean',
            'category_id' => 'required|integer|min:1',
            'related_yoga_poses' => 'array',
            'draft' => 'boolean',
            'meta_tag' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages(),
                'req' => $request->all()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $category = article_category::findOrFail($request->category_id);
            $article = new article();
            $article->title = $request->title;
            $article->category_id = $category->id;
            if ($request->filled('short_description')) {
                $article->short_description = $request->short_description;
            }
            if ($request->filled('long_description')) {
                $article->long_description = $request->long_description;
            }
            if ($request->filled('author_name')) {
                $article->author_name = $request->author_name;
            }
            if ($request->filled('author_details')) {
                $article->author_details = $request->author_details;
            }
            if ($request->has('is_latest')) {
                $article->is_latest = $request->is_latest;
            }
            if ($request->has('draft')) {
                $article->draft = $request->draft;
            }
            if ($request->has('meta_tag')) {
                $article->meta_tag = $request->meta_tag;
            }


            if ($request->has('is_expert_approved')) {
                $article->is_expert_approved = $request->is_expert_approved;
            }
            if ($request->has('related_yoga_poses')) {
                if (!empty($request->related_yoga_poses)) {
                    $filteredYogaPoses = [];
                    foreach ($request->related_yoga_poses as $pose_id) {
                        $pose = yoga_pose::find($pose_id);
                        if ($pose) {
                            $filteredYogaPoses[] = $pose->id;
                        }
                    }
                    $article->related_poses = !empty($filteredYogaPoses) ? json_encode($filteredYogaPoses) : null;
                }
            }
            $article->save();
            if ($request->filled('image')) {
                $image = $this->decodeBase64Image($request->image);
                $imageName = 'artical_' . $article->id . '.' . $image['extension'];
                $imagePath = 'public/article/' . $imageName;
                Storage::put($imagePath, $image['data']);

                $article->image = 'storage/app/public/article/' . $imageName;
                $article->save();
            }
            if ($request->filled('author_image')) {
                $image = $this->decodeBase64Image($request->author_image);
                $imageName = 'author_' . $article->id . '.' . $image['extension'];
                $authorImagePath = 'public/author/' . $imageName;
                Storage::put($authorImagePath, $image['data']);

                $article->author_image = 'storage/app/public/author/' . $imageName;
                $article->save();
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Article added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath) && Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            if (isset($authorImagePath) && Storage::exists($authorImagePath)) {
                Storage::delete($authorImagePath);
            }
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add article'
            ], 500);
        }
    }

    /**
     * Update Article
     */
    public function updateArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'artical_id' => 'required',
            'is_latest' => 'boolean',
            'is_expert_approved' => 'boolean',
            'category_id' => 'integer',
            'related_yoga_poses' => 'array',
            'draft' => 'boolean',
            'meta_tag' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $article = article::findOrFail($request->artical_id);
            if ($request->filled('category_id')) {
                $category = article_category::findOrFail($request->category_id);
                $article->category_id = $category->id;
            }
            if ($request->has('title')) {
                $article->title = $request->title;
            }
            if ($request->has('short_description')) {
                $article->short_description = $request->short_description;
            }
            if ($request->has('long_description')) {
                $article->long_description = $request->long_description;
            }
            if ($request->has('author_name')) {
                $article->author_name = $request->author_name;
            }
            if ($request->has('author_details')) {
                $article->author_details = $request->author_details;
            }
            if ($request->has('draft')) {
                $article->draft = $request->draft;
            }
            if ($request->has('is_latest')) {
                $article->is_latest = $request->is_latest;
            }
            if ($request->has('is_expert_approved')) {
                $article->is_expert_approved = $request->is_expert_approved;
            }
            if ($request->has('meta_tag')) {
                $article->meta_tag = $request->meta_tag;
            }
            if ($request->has('related_yoga_poses')) {
                if (!empty($request->related_yoga_poses)) {
                    $filteredYogaPoses = [];
                    foreach ($request->related_yoga_poses as $pose_id) {
                        $pose = yoga_pose::find($pose_id);
                        if ($pose) {
                            $filteredYogaPoses[] = $pose->id;
                        }
                    }
                    $article->related_poses = !empty($filteredYogaPoses) ? json_encode($filteredYogaPoses) : null;
                } else {
                    $article->related_poses = null;
                }
            }
            $article->save();

            if ($request->has('image')) {
                $oldImage = parse_url($article->image, PHP_URL_PATH);
                $image = $this->decodeBase64Image($request->image);
                $imageName = 'artical_' . $article->id . '.' . $image['extension'];
                $imagePath = 'public/article/' . $imageName;
                Storage::put($imagePath, $image['data']);

                $path = str_replace('storage/app/', '', $oldImage);
                if ($path !== $imagePath) {
                    if ($oldImage && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                $article->image = 'storage/app/public/article/' . $imageName . '?timestamp=' . time();
                $article->save();
            }
            if ($request->filled('author_image')) {
                $oldImage = parse_url($article->author_image, PHP_URL_PATH);
                $image = $this->decodeBase64Image($request->author_image);
                $imageName = 'author_' . $article->id . '.' . $image['extension'];
                $authorImagePath = 'public/author/' . $imageName;
                Storage::put($authorImagePath, $image['data']);

                $path = str_replace('storage/app/', '', $oldImage);
                if ($path !== $authorImagePath) {
                    if ($oldImage && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                $article->author_image = 'storage/app/public/author/' . $imageName . '?timestamp=' . time();
                $article->save();
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Article updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update article'
            ], 500);
        }
    }

    /**
     * Delete Article
     */
    public function deleteArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'artical_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $article = article::findOrFail($request->artical_id);
            $image = parse_url($article->image, PHP_URL_PATH);
            $author_image = parse_url($article->author_image, PHP_URL_PATH);
            $article->delete();
            $path = str_replace('storage/app/', '', $image);
            if ($image && Storage::exists($path)) {
                Storage::delete($path);
            }
            $path = str_replace('storage/app/', '', $author_image);
            if ($author_image && Storage::exists($path)) {
                Storage::delete($path);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Article deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to delete article'
            ], 500);
        }
    }

    /**
     * Get All Articles Admin
     */
    public function getAllArticlesAdmin(Request $request)
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
        try {
            $category = article_category::findOrFail($request->category_id);
            $articles = article::where('category_id', $category->id)->get()->map(function ($article) {
                $relatedPoseIds = json_decode($article->related_poses, true);
                if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                    $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                        ->get(['id', 'name']);
                } else {
                    $relatedPoses = null;
                }
                $article->related_yoga_poses = $relatedPoses;
                $article->makeHidden('related_poses');

                return $article;
            });
            return response()->json([
                'status_code' => 200,
                'data' => $articles,
                'message' => 'Articles retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve articles'
            ], 500);
        }
    }

    /**
     * Get All Articles User
     */
    public function getAllArticlesUser(Request $request)
    {
        try {
            if($request->filled('category_id'))
            {
                $category=article_category::findOrFail($request->category_id);
                $related_articles=article::where('category_id',$category->id)->where('draft', 0)->get()->map(function ($article) {
                    $relatedPoseIds = json_decode($article->related_poses, true);
                    if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                        $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                            ->get()
                            ->map(function ($pose) {
                                // Get category name for each pose
                                $category = pose_category::find($pose->category_id);
                                $pose->category_name = $category ? $category->name : null;
                                return $pose;
                            })
                            ->makeHidden('category_id');
                    } else {
                        $relatedPoses = null;
                    }
                    $article->related_yoga_poses = $relatedPoses;
                    $article->makeHidden('related_poses');

                    return $article;
                });
                $articles = [
                    [
                        "id" => $category->id,
                        "name" => $category->name,
                        "articles" => $related_articles
                    ]
                ];
            }
            else
            {
                $articles = article_category::with(['articles' => function ($query) {
                    $query->where('draft', 0);
                }])
                ->whereHas('articles', function ($query) {
                    $query->where('draft', 0);
                })
                ->get()->map(function ($category) {
                        $category->articles = $category->articles->map(function ($article) {
                            $relatedPoseIds = json_decode($article->related_poses, true);
                            if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                                $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                                    ->get()
                                    ->map(function ($pose) {
                                        // Get category name for each pose
                                        $category = pose_category::find($pose->category_id);
                                        $pose->category_name = $category ? $category->name : null;
                                        return $pose;
                                    })
                                    ->makeHidden('category_id');
                            } else {
                                $relatedPoses = null;
                            }
                            $article->related_yoga_poses = $relatedPoses;
                            $article->makeHidden('related_poses');

                            return $article;
                        });
                        return $category;
                    });
            }

            return response()->json([
                'status_code' => 200,
                'data' => $articles,
                'message' => 'Articles retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve articles'
            ], 500);
        }
    }

    /**
     * Get All Latest Articles
     */
    public function getLatestArticles()
    {
        try {
            $articles = article::where('is_latest', 1)->where('draft',0)->orderBy('created_at', 'desc')->get()->map(function ($article) {
                $relatedPoseIds = json_decode($article->related_poses, true);
                if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                    $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                        ->get()
                        ->map(function ($pose) {
                            // Get category name for each pose
                            $category = pose_category::find($pose->category_id);
                            $pose->category_name = $category ? $category->name : null;
                            return $pose;
                        })
                        ->makeHidden('category_id');
                } else {
                    $relatedPoses = null;
                }
                $article->related_yoga_poses = $relatedPoses;
                $article->makeHidden('related_poses');

                return $article;
            })->makeHidden('is_latest');
            return response()->json([
                'status_code' => 200,
                'data' => $articles,
                'message' => 'Articles retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve articles'
            ], 500);
        }
    }

    /**
     * Get All Expert Approved Articles
     */
    public function getExpertApprovedArticles()
    {
        try {
            $articles = article::where('is_expert_approved', 1)->where('draft',0)->orderBy('created_at', 'desc')->get()->map(function ($article) {
                $relatedPoseIds = json_decode($article->related_poses, true);
                if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                    $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                        ->get()
                        ->map(function ($pose) {
                            // Get category name for each pose
                            $category = pose_category::find($pose->category_id);
                            $pose->category_name = $category ? $category->name : null;
                            return $pose;
                        })
                        ->makeHidden('category_id');
                } else {
                    $relatedPoses = null;
                }
                $article->related_yoga_poses = $relatedPoses;
                $article->makeHidden('related_poses');

                return $article;
            })->makeHidden('is_expert_approved');
            return response()->json([
                'status_code' => 200,
                'data' => $articles,
                'message' => 'Articles retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve articles'
            ], 500);
        }
    }

    public function getYogaPoseDeatailsById($id)
    {
        $articles = article::where('id', $id)->get();

        if($articles->isEmpty()){
            return response()->json([
                'status_code' => 404,
                'message' => 'Failed to retrieve articles'
            ], 404);
        }
        return response()->json([
            'status_code' => 200,
            'data' => $articles,
            'message' => 'Articles retrieved successfully'
        ], 200);
    }
}
