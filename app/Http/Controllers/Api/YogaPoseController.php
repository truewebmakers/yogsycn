<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\article;
use App\Models\pose_category;
use App\Models\video;
use App\Models\yoga_pose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ImageHandleTrait;
use Illuminate\Support\Facades\Storage;

class YogaPoseController extends Controller
{
    use ImageHandleTrait;
    /**
     * Add Yoga Pose Category.
     */
    public function addPoseCategory(Request $request)
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
            $existingCategory = pose_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
            if ($existingCategory) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Pose Category already exist'
                ], 400);
            }
            $category = new pose_category();
            $category->name = $name;
            $category->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Pose Category added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add pose category'
            ], 500);
        }
    }

    /**
     * Update Yoga Pose Category.
     */
    public function updatePoseCategory(Request $request)
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
            $category = pose_category::findOrFail($request->category_id);
            if ($request->filled('name')) {
                $name = trim($request->name);
                $existingCategory = pose_category::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->where('id', '!=', $request->category_id)->first();

                if ($existingCategory) {
                    return response()->json([
                        'status_code' => 400,
                        'message' => 'Pose Category already exist'
                    ], 400);
                }
                $category->name = $name;
            }
            $category->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Pose Category updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update pose category'
            ], 500);
        }
    }

    /**
     * Delete Yoga Pose Category.
     */
    public function deletePoseCategory(Request $request)
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
            $category = pose_category::findOrFail($request->category_id);
            $poses = yoga_pose::where('category_id', $category->id)->get();

            foreach ($poses as $pose) {
                $videos = video::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->get();
                foreach ($videos as $video) {
                    $relatedPoses = json_decode($video->related_poses, true);
                    // Remove the pose_id from the array
                    if (($key = array_search($pose->id, $relatedPoses)) !== false) {
                        unset($relatedPoses[$key]);
                    }
                    if (empty($relatedPoses)) {
                        $video->related_poses = null;
                    } else {
                        $video->related_poses = json_encode(array_values($relatedPoses));
                    }
                    $video->save();
                }
                $articles = article::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->get();
                foreach ($articles as $article) {
                    $relatedPoses = json_decode($article->related_poses, true);
                    // Remove the pose_id from the array
                    if (($key = array_search($pose->id, $relatedPoses)) !== false) {
                        unset($relatedPoses[$key]);
                    }
                    if (empty($relatedPoses)) {
                        $article->related_poses = null;
                    } else {
                        $article->related_poses = json_encode(array_values($relatedPoses));
                    }
                    $article->save();
                }
                if ($pose->image) {
                    $image = parse_url($pose->image, PHP_URL_PATH);
                    $path = str_replace('storage/app/', '', $image);
                    if ($image && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
            }
            yoga_pose::where('category_id', $category->id)->delete();
            $category->delete();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Pose Category and Related Yoga Poses deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to delete pose category'
            ], 500);
        }
    }

    /**
     * Get All Yoga Poses Categories
     */
    public function getAllPosesCategoriesUser()
    {
        try {
            $categories = pose_category::all();
            return response()->json([
                'status_code' => 200,
                'data' => $categories,
                'message' => 'Yoga Pose Categories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga pose categories'
            ], 500);
        }
    }

    /**
     * Get All Yoga Poses Categories
     */
    public function getAllPosesCategoriesAdmin()
    {
        try {
            $categories = pose_category::all();
            return response()->json([
                'status_code' => 200,
                'data' => $categories,
                'message' => 'Yoga Pose Categories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga pose categories'
            ], 500);
        }
    }

    /**
     * Add Yoga Pose
     */
    public function addYogaPose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required',
            'category_id' => 'required|integer|min:1',
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
            $category = pose_category::findOrFail($request->category_id);
            $pose = new yoga_pose();
            $pose->name = $request->name;
            $pose->category_id = $category->id;
            if ($request->filled('short_description')) {
                $pose->short_description = $request->short_description;
            }
            if ($request->filled('long_description')) {
                $pose->long_description = $request->long_description;
            }
            if ($request->filled('pose_type')) {
                $pose->pose_type = $request->pose_type;
            }
            if ($request->filled('sanskrit_meaning')) {
                $pose->sanskrit_meaning = $request->sanskrit_meaning;
            }
            if ($request->filled('benefits')) {
                $pose->benefits = $request->benefits;
            }
            if ($request->filled('targets')) {
                $pose->targets = $request->targets;
            }
            if ($request->filled('guidance')) {
                $pose->guidance = $request->guidance;
            }
            if ($request->filled('things_keep_in_mind')) {
                $pose->things_keep_in_mind = $request->things_keep_in_mind;
            }
            if ($request->has('draft')) {
                $pose->draft = $request->draft;
            }
            if ($request->has('meta_tag')) {
                $pose->meta_tag = $request->meta_tag;
            }


            $pose->save();
            if ($request->filled('image')) {
                $image = $this->decodeBase64Image($request->image);
                $imageName = 'yogapose_' . $pose->id . '.' . $image['extension'];
                $imagePath = 'public/yogapose/' . $imageName;
                Storage::put($imagePath, $image['data']);

                $pose->image = 'storage/app/public/yogapose/' . $imageName;
                $pose->save();
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Yoga Pose added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath) && Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add yoga pose'
            ], 500);
        }
    }

    /**
     * Update Yoga Pose
     */
    public function updateYogaPose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pose_id' => 'required',
            'category_id' => 'integer',
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
            $pose = yoga_pose::findOrFail($request->pose_id);
            if ($request->filled('category_id')) {
                $category = pose_category::findOrFail($request->category_id);
                $pose->category_id = $category->id;
            }
            if ($request->filled('name')) {
                $pose->name = $request->name;
            }
            if ($request->has('short_description')) {
                $pose->short_description = $request->short_description;
            }
            if ($request->has('long_description')) {
                $pose->long_description = $request->long_description;
            }
            if ($request->has('pose_type')) {
                $pose->pose_type = $request->pose_type;
            }
            if ($request->has('sanskrit_meaning')) {
                $pose->sanskrit_meaning = $request->sanskrit_meaning;
            }
            if ($request->has('benefits')) {
                $pose->benefits = $request->benefits;
            }
            if ($request->has('targets')) {
                $pose->targets = $request->targets;
            }
            if ($request->has('guidance')) {
                $pose->guidance = $request->guidance;
            }
            if ($request->has('things_keep_in_mind')) {
                $pose->things_keep_in_mind = $request->things_keep_in_mind;
            }
            if ($request->has('draft')) {
                $pose->draft = $request->draft;
            }
            if ($request->has('meta_tag')) {
                $pose->meta_tag = $request->meta_tag;
            }

            $pose->save();

            if ($request->filled('image')) {
                $oldImage = parse_url($pose->image, PHP_URL_PATH);
                $image = $this->decodeBase64Image($request->image);
                $imageName = 'yogapose_' . $pose->id . '.' . $image['extension'];
                $imagePath = 'public/yogapose/' . $imageName;
                Storage::put($imagePath, $image['data']);

                $path = str_replace('storage/app/', '', $oldImage);
                if ($path !== $imagePath) {
                    if ($oldImage && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                $pose->image = 'storage/app/public/yogapose/' . $imageName . '?timestamp=' . time();
                $pose->save();
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Yoga Pose updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update yoga pose' .$e
            ], 500);
        }
    }

    /**
     * Delete Yoga Pose
     */
    public function deleteYogaPose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pose_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $pose = yoga_pose::findOrFail($request->pose_id);
            // Fetch videos containing the pose_id in related_poses
            $videos = video::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->get();
            foreach ($videos as $video) {
                $relatedPoses = json_decode($video->related_poses, true);
                // Remove the pose_id from the array
                if (($key = array_search($pose->id, $relatedPoses)) !== false) {
                    unset($relatedPoses[$key]);
                }
                if (empty($relatedPoses)) {
                    $video->related_poses = null;
                } else {
                    $video->related_poses = json_encode(array_values($relatedPoses));
                }
                $video->save();
            }
            $articles = article::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->get();
            foreach ($articles as $article) {
                $relatedPoses = json_decode($article->related_poses, true);
                // Remove the pose_id from the array
                if (($key = array_search($pose->id, $relatedPoses)) !== false) {
                    unset($relatedPoses[$key]);
                }
                if (empty($relatedPoses)) {
                    $article->related_poses = null;
                } else {
                    $article->related_poses = json_encode(array_values($relatedPoses));
                }
                $article->save();
            }

            $image = parse_url($pose->image, PHP_URL_PATH);
            $pose->delete();
            $path = str_replace('storage/app/', '', $image);
            if ($image && Storage::exists($path)) {
                Storage::delete($path);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Yoga Pose deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to delete yoga pose'
            ], 500);
        }
    }

    /**
     * Get All Yoga Poses By Category Admin
     */
    public function getAllYogaPosesByCategory(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'category_id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status_code' => 400,
        //         'message' => $validator->messages()
        //     ], 400);
        // }
        try {
            // $category = pose_category::find($request->category_id);

            $category = pose_category::with('yogaPoses')->get();
            // if($category){
               //  $poses = yoga_pose::where('draft',0)->get();

              //  $poses = yoga_pose::where('category_id', $category->id)->where('draft',0)->get();
                return response()->json([
                    'status_code' => 200,
                    'data' => $category,
                    'message' => 'Yoga Poses retrieved successfully'
                ], 200);
            // }else{
            //     return response()->json([
            //         'status_code' => 404,
            //         'message' => 'No Pose Category found'
            //     ], 404);
            // }

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga poses'. $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get All Yoga Poses Admin
     */
    public function getAllYogaPosesAdmin()
    {
        try {
            // $poses = yoga_pose::where('draft',0)->get();
            $poses = yoga_pose::get();
            return response()->json([
                'status_code' => 200,
                'data' => $poses,
                'message' => 'Yoga Poses retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga poses' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get All Yoga Poses User
     */
    public function getAllYogaPosesUser(Request $request)
    {
        try {
            if ($request->filled('category_id')) {
                $category = pose_category::findOrFail($request->category_id);
                $related_poses = yoga_pose::where('category_id', $category->id)->get()
                    ->map(function ($pose) use ($category) {
                        $pose->category_name = $category->name;
                        return $pose;
                    });
                $poses = [
                    [
                        "id" => $category->id,
                        "name" => $category->name,
                        "yoga_poses" => $related_poses
                    ]
                ];
            } else {
                // $poses = pose_category::with(['yogaPoses'])
                //     ->whereHas('yogaPoses')
                //     ->get();
                $poses = pose_category::with(['yogaPoses'])
                    ->whereHas('yogaPoses')
                    ->get()
                    ->map(function ($category) {
                        // Append the category_name to each yoga pose
                        $category->yogaPoses->map(function ($pose) use ($category) {
                            $pose->category_name = $category->name;
                            return $pose;
                        });
                        return $category;
                    });
            }
            return response()->json([
                'status_code' => 200,
                'data' => $poses,
                'message' => 'Yoga Poses retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga poses' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Yoga Pose Details
     */

     public function getYogaPoseDetailsById(Request $request,$id)
     {

     }
    public function getYogaPoseDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pose_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        try {
            $pose = yoga_pose::findOrFail($request->pose_id);
            $category = pose_category::find($pose->category_id);
            $pose->category_name = $category ? $category->name : null;
            $pose->videos = video::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->get()->makeHidden('related_poses');
            $pose->articles = article::whereNotNull('related_poses')->whereRaw("JSON_CONTAINS(related_poses, $pose->id)")->where('draft', 0)->get()->map(function ($article) {
                $relatedPoseIds = json_decode($article->related_poses, true);
                if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                    $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                        ->get()->makeHidden('category_id');
                } else {
                    $relatedPoses = null;
                }
                $article->related_yoga_poses = $relatedPoses;

                return $article;
            })->makeHidden('related_poses');

            return response()->json([
                'status_code' => 200,
                'data' => $pose,
                'message' => 'Yoga Pose details retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve yoga pose details'
            ], 500);
        }
    }

    public function getArticleDeatailsById($id)
    {
        $yoga_pose = yoga_pose::where('id', $id)->get();

        if($yoga_pose->isEmpty()){
            return response()->json([
                'status_code' => 404,
                'message' => 'Failed to retrieve yoga pose'
            ], 404);
        }
        return response()->json([
            'status_code' => 200,
            'data' => $yoga_pose,
            'message' => 'yoga pose retrieved successfully'
        ], 200);
    }
}
