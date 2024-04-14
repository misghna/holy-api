<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\ContentConfig;

class ContentConfigController extends Controller
{
    public function createContentConfig(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|string',
            'content_text' => 'required|string',
            'media_link' => 'required|string',
            'content_category' => 'required|string',
            'lang' => 'required|string',
            'is_original' => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'is_draft' => 'required|boolean',
            'updated_by' => 'required|string',
        ]);

        $contentConfig = ContentConfig::create([
            'type' => $validatedData['type'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'background_image' => $validatedData['background_image'],
            'content_text' => $validatedData['content_text'],
            'media_link' => $validatedData['media_link'],
            'content_category' => $validatedData['content_category'],
            'lang' => $validatedData['lang'],
            'is_original' => $validatedData['is_original'],
            'auto_translate' => $validatedData['auto_translate'],
            'is_draft' => $validatedData['is_draft'],
            'updated_by' => $validatedData['updated_by'],

        ]);

        return response()->json([
            'contentConfig' => $contentConfig,
            'message' => 'Success'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $contentConfig = ContentConfig::findOrFail($id);

        $validatedData = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'background_image' => 'required|string',
            'content_text' => 'required|string',
            'media_link' => 'required|string',
            'content_category' => 'required|string',
            'lang' => 'required|string',
        ]);

        $contentConfig->fill($validatedData);
        $contentConfig->save();

        return response()->json([
            'contentConfig' => $contentConfig,
            'message' => 'Success, Content Config updated successfully'
        ], 200);

    }

    public function all(Request $request)
    {
        $content = $request->input("page");
        $start = $request->input('start');
        $data = ContentConfig::where("type", $content)
            ->offset($start)
            ->limit(10)
            ->get();
        return $data;
    }
    public function one(Request $request)
    {
        $page = $request->input("page");
        $id = $request->input('id');
        $start = $request->input('start');
        $content = ContentConfig::where("id", $id)
            ->where("content_type", $page)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = ContentConfig::where('id', $id)->delete();
        if ($response)
            return "Content Config deleted successfully.";
        else return "Content Config not found";
    }
}
