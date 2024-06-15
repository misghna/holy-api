<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\Content;
use App\Models\API\File;
use App\Models\FileMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class ContentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('tenant_id');
        $valRules = [
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|int',
            'content_text' => 'required|string',
            // 'media_link' => 'required|array',
            'content_category' => 'required|string',
            'lang' => 'required|string',
            'is_original' => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'is_draft' => 'required|boolean',
            'tenant_id' => 'required|integer|exists:tenants,id'
        ];
        $data = $request->all();
        $data['tenant_id'] = $tenantId;
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        // Extract media_link from data
        $mediaLink = $data['media_link'];
        unset($data['media_link']);

        $data['updated_by'] = Auth::user()->id;
        $content = Content::create($data);



        $mediaLinkData = array_map(function ($fileId) use ($content, $data) {
            return [
                'ref_id' => $content->id,
                'ref_type' => 'content_media_link',
                'file_id' => $fileId,
                'updated_by' => $data['updated_by'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at']
            ];
        }, $mediaLink);

        // Bulk insert background_image data into FileMapper
        FileMapper::insert($mediaLinkData);

        $contentArray = $content->toArray();
        $contentArray['media_link'] = $mediaLink;

        return response()->json([
            'content' => $contentArray,
            'message' => 'Success'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {

        $tenantId = $request->header('tenant_id');
        $valRules = [
            'id' => 'required|integer',
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|int',
            'content_text' => 'required|string',
            // 'media_link' => 'required|array',
            'content_category' => 'required|string',
            'lang' => 'required|string',
            'is_original' => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'is_draft' => 'required|boolean',
            'tenant_id' => 'required|integer|exists:tenants,id',
        ];
        $data = $request->all();
        $data['tenant_id'] = $tenantId;
        $data['created_at'] = gmdate('Y-m-d H:i:s');
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $data['updated_by'] = Auth::user()->id;

        // Extract background_image from data
        // $backgroundImg = $data['background_image'];
        // unset($data['background_image']);
        $mediaLink = $data['media_link'];
        unset($data['media_link']);

        $id = $request->input('id');
        $content = Content::findOrFail($id);
        $content->fill($data);
        $content->save();

        // Fetch existing media_link entries
        $existingBgImages = FileMapper::where('ref_id', $content->id)
            ->where('ref_type', 'content_media_link')
            ->pluck('file_id')
            ->toArray();

        // Determine entries to add and remove
        $imagesToAdd = array_diff($mediaLink, $existingBgImages);
        $imagesToRemove = array_diff($existingBgImages, $mediaLink);

        // Delete obsolete media_link entries if there are any
        if (count($imagesToRemove) > 0) {
            FileMapper::where('ref_id', $content->id)
                ->where('ref_type', 'content_media_link')
                ->whereIn('file_id', $imagesToRemove)
                ->delete();
        }
        // Add new media_link entries if there are any
        if (count($imagesToAdd) > 0) {
            foreach ($imagesToAdd as $fileId) {
                FileMapper::create([
                    'ref_id' => $content->id,
                    'ref_type' => 'content_media_link',
                    'file_id' => $fileId,
                    'updated_by' => $data['updated_by'],
                ]);
            }
        }


        $contentArray = $content->toArray();
        $contentArray['media_link'] = $mediaLink;

        return response()->json([
            'contentConfig' => $contentArray,
            'message' => 'Success, Content Config updated successfully'
        ], 200);
    }

    public function all(Request $request)
    {
        $tenantId = $request->header('tenant_id');
        // Validate the tenant_id
        $validator = Validator::make(['tenant_id' => $tenantId], [
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $request->validate([
            'content_category' => 'required|string',
            'lang' => 'required|string',
        ]);

        $content_category = $request->input("content_category");
        $lang = $request->input('lang', 'english');
        $start = $request->input('start', 0); // Default to 0 if not provided
        $limit = $request->input('limit', 10); // Default to 10 if not provided

        $content = Content::select(
            'content.id',
            'content.lang',
            'content.type',
            'content.title',
            'content.description',
            'content.content_text',
            'f.file_id as background_image',
            'content.content_category',
            DB::raw('UNIX_TIMESTAMP(content.created_at)*1000 AS release_date_time'),
            'fc.file_id as m_link',
            'fc.file_type',
        )
            ->leftJoin('file_mapper AS fmml', function ($query) {
                $query->on('fmml.ref_id', '=', 'content.id');
                $query->where('fmml.ref_type', '=', 'content_media_link')
                    ->leftJoin('files AS fc', function ($query1) {
                        $query1->on('fc.id', '=', 'fmml.file_id');
                    });
            })
            ->leftJoin('files AS f', function ($join) {
                $join->on('f.id', '=', 'content.background_image');
            })
            ->where([["content_category", $content_category], ['lang', $lang], ['content.tenant_id', $tenantId]])
            ->offset($start)
            ->limit($limit)
            ->get();
        $response = [];
        foreach ($content as $c) {
            if ($c->background_image)
                $c->background_image = asset('/storage/uploaded/' . $c->background_image);
            $arr = json_decode(json_encode($c), true);

            if ($c->file_type != 'url') {
                if (key_exists($c->id, $response)) {
                    $arr['media_link'][] = asset('/storage/uploaded/' . $response[$c->id]['m_link']);
                }
                $arr['media_link'][] = asset('/storage/uploaded/' . $c->m_link);
            }
            $response[$c->id] = $arr;
        }
        return array_values($response);
    }

    public function one(Request $request)
    {
        $tenantId = $request->header('tenant_id');
        $headerValidator = Validator::make(['tenant_id' => $tenantId], [
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        if ($headerValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $headerValidator->errors()
            ], 422);
        }

        $id = $request->input('id');

        $request->validate([
            'id' => 'required|integer',
            'content_category' => 'required|string',
            'lang' => 'required|string',
        ]);
        $content_category = $request->input("content_category");
        $id = $request->input('id');
        $lang = $request->input('lang');
        $tenantId = $request->header('tenant_id', 0);

        $content = Content::select(
            'content.id',
            'content.lang',
            'content.type',
            'content.title',
            'content.description',
            'content.content_text',
            'f.file_id as background_image',
            'content.content_category',
            DB::raw('UNIX_TIMESTAMP(content.created_at)*1000 AS release_date_time'),
            'fc.file_id as m_link',
            'fc.file_type',
        )
            ->leftJoin('file_mapper AS fmml', function ($query) {
                $query->on('fmml.ref_id', '=', 'content.id');
                $query->where('fmml.ref_type', '=', 'content_media_link')
                    ->leftJoin('files AS fc', function ($query1) {
                        $query1->on('fc.id', '=', 'fmml.file_id');
                    });
            })
            ->leftJoin('files AS f', function ($join) {
                $join->on('f.id', '=', 'content.background_image');
            })
            ->where([["content.id", $id], ["content_category", $content_category], ['lang', $lang], ['content.tenant_id', $tenantId]])
            ->get();
        // $content = Content::with('media_link')
        //     ->select('content.id', 'content.lang', 'content.type', 'content.title', 'content.media_link', 'content.description', 'content.content_category', DB::raw('UNIX_TIMESTAMP(content.created_at)*1000 AS release_date_time'), DB::raw('concat("[",COALESCE(GROUP_CONCAT(file_mapper.file_id), ""),"]") as background_image'))
        //     ->leftJoin('file_mapper', function ($join) {
        //         $join->on('file_mapper.ref_id', '=', 'content.id');
        //     })
        //     ->where([["content.id", $id], ["content_category", $content_category], ['lang', $lang], ['tenant_id', $tenantId]])
        //     ->groupBy("content.id")
        //     ->first();

        $response = [];
        foreach ($content as $c) {
            if ($c->background_image)
                $c->background_image = asset('/storage/uploaded/' . $c->background_image);
            $arr = json_decode(json_encode($c), true);
            if ($c->file_type != 'url') {
                if (key_exists($c->id, $response)) {
                    $arr['media_link'][] = asset('/storage/uploaded/' . $response[$c->id]['m_link']);
                }
                $arr['media_link'][] = asset('/storage/uploaded/' . $c->m_link);
            }
            $response[$c->id] = $arr;
        }
        if (isset($response[$id]))
            return $response[$id];
        return null;
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $response = Content::where('id', $id)->delete();
        if ($response)
            return "Content deleted successfully.";
        else return "Content not found";
    }
}
