<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\API\PageConfig;
use Illuminate\Support\Facades\Auth;
use Validator;
use Log;

class PageConfigController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $valRules = [
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|integer',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
            'tenant_id' => 'required|integer',
            'seq_no' => 'required|integer',
            'language' => 'required|string'
        ];
        
        $data = json_decode($request->getContent(), true);
        $data['tenant_id'] = $request->header('tenant_id',0); 
        $data['created_at'] = gmdate('Y-m-d H:i:s'); 
        $data['updated_at'] = gmdate('Y-m-d H:i:s'); 

        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            dd($validator->errors()->all());
        }

        $data['updated_by'] = Auth::user()->id;     
        Log::info($data); 
        $pageConfig = PageConfig::create($data);

        return response()->json([
            'data' => $pageConfig,
            'message' => 'Success'
        ], 200);

    }

    public function update(Request $request): JsonResponse
    {  
        $valRules = [
            'id' => 'required|integer',
            'page_type' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'img_link' => 'required|string',
            'parent' => 'required|integer',
            'header_img' => 'required|string',
            'header_text' => 'required|string',
            'tenant_id' => 'required|integer',
            'seq_no' => 'required|integer',
            'language' => 'required|string'
        ];

        $data = json_decode($request->getContent(), true);
        $data['tenant_id'] = $request->header('tenant_id',0); 
        $data['updated_at'] = gmdate('Y-m-d H:i:s');

        $validator = Validator::make($data, $valRules);

        if (!$validator->passes()) {
            dd($validator->errors()->all());
        }

        $pageConfig = PageConfig::findOrFail($data['id']);
        $pageConfig->fill($data);
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
        $tenantId = $request->header('tenant_id',0); 
        $content = PageConfig::where([["tenant_id", $tenantId]])
            ->offset($start)
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
        $tenantId = $request->header('tenant_id',0); 
        $content = PageConfig::where([["tenant_id", $tenantId],["id",$id]])
            ->first();
        return $content;
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $tenantId = $request->header('tenant_id',0); 
        $response = PageConfig::where([["tenant_id",$tenantId],["id", $id]])->delete();
        if ($response)
            return "Page Config deleted successfully.";
        else return "Page Config not found";
    }
}
