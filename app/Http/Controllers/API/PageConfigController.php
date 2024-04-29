<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;
use Illuminate\Support\Facades\Auth;

class PageConfigController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|string',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
        ]);

        $pageConfig = PageConfig::create([
            'page_type' => $validatedData['page_type'],
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'img_link' => $validatedData['img_link'],
            'parent' => $validatedData['parent'],
            'header_img' => $validatedData['header_img'],
            'header_text' => $validatedData['header_text'],
            'updated_by' => Auth::user()->id,
        ]);

        return response()->json([
            'pageConfig' => $pageConfig,
            'message' => 'Success'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
    
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|string',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
        ]);
        $id = $request->input('id');
        $pageConfig = PageConfig::findOrFail($id);
        $pageConfig->fill($validatedData);
        $pageConfig->save();

        return response()->json([
            'pageConfig' => $pageConfig,
            'message' => 'Success, Page Config updated successfully'
        ], 200);

    }

    public function all(Request $request)
    {
        $start = $request->input('start', 0);
        $limit = $request->input('limit', 10); 
        $content = PageConfig::offset($start)
            ->limit($limit)
            ->get();
        return $content;
    }
    public function one(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $content = PageConfig::where("id", $id)
            ->first();
        return $content;
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $response = PageConfig::where('id', $id)->delete();
        if ($response)
            return "Page Config deleted successfully.";
        else return "Page Config not found";
    }
}
