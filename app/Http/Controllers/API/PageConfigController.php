<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;

class PageConfigController extends Controller
{

    public function createPageConfig(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|string',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
            'updated_by' => 'required|string',
        ]);

        $pageConfig = PageConfig::create([
            'page_type' => $validatedData['page_type'],
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'img_link' => $validatedData['img_link'],
            'parent' => $validatedData['parent'],
            'header_img' => $validatedData['header_img'],
            'header_text' => $validatedData['header_text'],
            'updated_by' => $validatedData['updated_by'],
        ]);

        return response()->json([
            'pageConfig' => $pageConfig,
            'message' => 'Success'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        //echo "<pre>"; print_r($request); exit;
        $id = $request->input('id');
        $pageConfig = PageConfig::findOrFail($id);

        $validatedData = $request->validate([
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|string',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
            'updated_by' => 'required|string',
        ]);

        $pageConfig->fill($validatedData);
        $pageConfig->save();

        return response()->json([
            'pageConfig' => $pageConfig,
            'message' => 'Success, Page Config updated successfully'
        ], 200);

    }

    public function all(Request $request)
    {
        $page = $request->input("page");
        // $name = $request->input('name');
        $start = $request->input('start');
        $content = PageConfig::where("page_type", $page)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
    public function one(Request $request)
    {
        $page = $request->input("page");
        $id = $request->input('id');
        // $name = $request->input('name');
        $start = $request->input('start');
        $content = PageConfig::where("id", $id)
            // ->where("name", $name)
            ->where("page_type", $page)
            ->offset($start)
            ->limit(10)
            ->get();
        return $content;
    }
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = PageConfig::where('id', $id)->delete();
        if ($response)
            return "Page Config deleted successfully.";
        else return "Page Config not found";
    }
}
