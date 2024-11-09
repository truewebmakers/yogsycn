<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\video;
use App\Models\yoga_pose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * Get all videos.
     */
    public function getAllVideos()
    {
        try {
            $videos = video::all()->map(function ($video) {
                $relatedPoseIds = json_decode($video->related_poses, true);
                if (!is_null($relatedPoseIds) && is_array($relatedPoseIds)) {
                    $relatedPoses = yoga_pose::whereIn('id', $relatedPoseIds)
                        ->get(['id', 'name']);
                } else {
                    $relatedPoses = null;
                }
                $video->related_yoga_poses = $relatedPoses;
                $video->makeHidden('related_poses');

                return $video;
            });
            return response()->json([
                'status_code' => 200,
                'data' => $videos,
                'message' => 'Videos retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve videos'
            ], 500);
        }
    }

    /**
     * Add new video
     */
    public function addVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required',
            'related_yoga_poses' => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'status' => 'Fail',
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $video = new video();
            if ($request->filled('title')) {
                $video->title = $request->title;
            }
            if ($request->filled('description')) {
                $video->description = $request->description;
            }
            $video->url = $request->url;
            if ($request->has('related_yoga_poses')) {
                if (!empty($request->related_yoga_poses)) {
                    $filteredYogaPoses = [];
                    foreach ($request->related_yoga_poses as $pose_id) {
                        $pose = yoga_pose::find($pose_id);
                        if ($pose) {
                            $filteredYogaPoses[] = $pose->id;
                        }
                    }
                    $video->related_poses = !empty($filteredYogaPoses) ? json_encode($filteredYogaPoses) : null;
                }
            }
            $video->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Video added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add video'
            ], 500);
        }
    }

    /**
     * Update video
     */
    public function updateVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
            'related_yoga_poses' => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'status' => 'Fail',
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $video = video::findOrFail($request->video_id);
            if ($request->filled('url')) {
                $video->url = $request->url;
            }
            if ($request->has('title')) {
                $video->title = $request->title;
            }
            if ($request->has('description')) {
                $video->description = $request->description;
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
                    $video->related_poses = !empty($filteredYogaPoses) ? json_encode($filteredYogaPoses) : null;
                } else {
                    $video->related_poses = null;
                }
            }
            $video->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Video updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update video'
            ], 500);
        }
    }

    /**
     * Delete video
     */
    public function deleteVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'status' => 'Fail',
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $video = video::findOrFail($request->video_id);
            if ($video) {
                $video->delete();
            }
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'status' => 'Success',
                'message' => 'Video deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to delete video'
            ], 500);
        }
    }
}
