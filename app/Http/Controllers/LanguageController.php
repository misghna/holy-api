<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Language;
use App\Models\API\Dictionary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use Log;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{   
    $tenantId = $request->header('tenant_id', 0);
    $languages = Language::select(
            'languages.id',
            'languages.lang_id',
            'languages.lang_name',
            'tenants.tenant_name',
            'users.name as updated_by',
            'languages.updated_at',
            
        )
        ->leftJoin('tenants', 'languages.tenant_id', '=', 'tenants.id')
        ->leftJoin('users', 'languages.updated_by', '=', 'users.id')
        ->where('languages.tenant_id', $tenantId)
        ->get();

    return response()->json($languages);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'lang_id' => 'required|string|unique:languages',
        'lang_name' => 'required|string',
    ]);

    $tenantId = $request->header('tenant_id', 0); 
    $validatedData['tenant_id'] = $tenantId;

    $validatedData['updated_by'] = Auth::id();

    $language = Language::create($validatedData);

    return response()->json($language, 201);
}


    /**
     * Display the specified resource.
     */
    public function show(Request $request)
{
    $id = $request->input('id');
    
    $language = Language::select(
            'languages.id',
            'languages.lang_id',
            'languages.lang_name',
            'tenants.tenant_name',
            'users.name as updated_by',
            'languages.updated_at',
            
        )
        ->leftJoin('tenants', 'languages.tenant_id', '=', 'tenants.id')
        ->leftJoin('users', 'languages.updated_by', '=', 'users.id')
        ->where('languages.id', $id)
        ->first();
    
    if (!$language) {
        return response()->json(['error' => 'Language not found'], 404);
    }

    return response()->json($language);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
{
    $validatedData = $request->validate([
        'id' => 'required|integer',
        'lang_id' => 'required|string',
        'lang_name' => 'required|string',
    ]);
    
    $tenantId = $request->header('tenant_id', 0); 
    $validatedData['tenant_id'] = $tenantId;

    $language = Language::find($validatedData['id']);
    
    if (!$language) {
        return response()->json(['error' => 'Language not found'], 404);
    }

    $validatedData['updated_by'] = Auth::id();

    $language->update($validatedData);

    return response()->json($language);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $language = Language::find($id);
        if(!$language)
        {
            return response()->json(['error' => 'Language not found'], 404);
        }
        $language->delete();
        return response()->json(['message' => 'Language deleted Successfully']);
    }


    
    public function geOneDict(Request $request)
    {
       $key = $request->input('key');
       if(is_null($key)) return response()->json(['message' => 'Missing Key!'], 400);

       $tenantId = $request->header('tenant_id',0); 
       $languages = Language::select('lang_id', 'lang_name')->get();
       $dictionary = Dictionary::select('key','language','value')
                    ->where([['tenant_id',$tenantId],['key',$key]])->get();
       $keys=[];$langDict=[];
       foreach($dictionary as $row){
            if(!in_array($row['key'],$keys)) $keys[]=$row['key'];
            $dataKey = $row['key'].'-'.$row['language'];
            $langDict[$dataKey] = $row['value'];
        }

        $row=[];

        $row['Key']=$key;
        foreach($languages as $lang){
            $langName=$lang['lang_name'];
            $dataKey = $key . '-' . $lang['lang_id'];
            $dicVal = isset($langDict[$dataKey])? $langDict[$dataKey] : 'Not Set';
            $row[$langName]=$dicVal;             
        }
        $rows[]=$row;

       return response()->json($row);
}

    public function getDict(Request $request)
{


    $tenantId = $request->header('tenant_id', 0); 
    $languages = Language::select('lang_id', 'lang_name')->get();

    
    $dictionary = Dictionary::select(
            'dictionary.key',
            'dictionary.language',
            'dictionary.value',
            'users.name as updated_by',
            'tenants.tenant_name'
        )
        ->leftJoin('users', 'dictionary.updated_by', '=', 'users.id')
        ->leftJoin('tenants', 'dictionary.tenant_id', '=', 'tenants.id')
        ->where('dictionary.tenant_id', $tenantId)
        ->get();

    $keys = [];
    $langDict = [];
    $updatedBy = [];
    $tenantName = '';

    foreach ($dictionary as $row) {
        if (!in_array($row['key'], $keys)) {
            $keys[] = $row['key'];
        }
        $dataKey = $row['key'] . '-' . $row['language'];
        $langDict[$dataKey] = $row['value'];
        $updatedBy[$row['key']] = $row['updated_by'];
        $tenantName = $row['tenant_name'];
    }



    $counter = 1;
    $row = [];
    $rows = [];

    foreach ($keys as $key) {
        $row['id'] = $counter;
        $row['Key'] = $key;
        $row['updated_by'] = isset($updatedBy[$key]) ? $updatedBy[$key] : 'Unknown';
        $row['tenant_name'] = $tenantName;

        foreach ($languages as $lang) {
            $langName = $lang['lang_name'];
            $dataKey = $key . '-' . $lang['lang_id'];
            $dicVal = isset($langDict[$dataKey]) ? $langDict[$dataKey] : 'Not Set';
            $row[$langName] = $dicVal;             
        }
        $counter++;
        $rows[] = $row;
    }

    return response()->json($rows);
}

    public function updateDict(Request $request)
{
    $tenantId = $request->header('tenant_id', 0); 
    $valRules = ['key' => 'required|string'];
    $validatedData = json_decode($request->getContent(), true);
    
    $validator = Validator::make($validatedData, $valRules);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $key = $validatedData['key'];  

    $dictionary = Dictionary::select('key', 'language', 'value')
        ->where([['tenant_id', $tenantId], ['key', $key]])
        ->get()
        ->keyBy('language'); 

    $languages = Language::select('lang_id', 'lang_name')->get();

    foreach ($languages as $lang) {
        $langName = $lang['lang_name'];
        $langId = $lang['lang_id'];
        
        if (isset($validatedData[$langName]) && !is_null($validatedData[$langName]) && $validatedData[$langName] != "Not Set") {
            if (isset($dictionary[$langId])) {
                if ($dictionary[$langId]['value'] != $validatedData[$langName]) {
                    Dictionary::where([['tenant_id', $tenantId], ['key', $key], ['language', $langId]])
                        ->update(['value' => $validatedData[$langName], 'updated_by' => Auth::user()->id]);
                }
            } else {  
                Dictionary::create([
                    'key' => $key,
                    'language' => $langId,
                    'tenant_id' => $tenantId,
                    'value' => $validatedData[$langName],
                    'updated_by' => Auth::user()->id
                ]);
            }
        }
    }

    $updatedDictionary = Dictionary::select('key', 'language', 'value')
        ->where('tenant_id', $tenantId)
        ->where('key', $key)
        ->get()
        ->keyBy('language');

    $response = [
        'Key' => $key
    ];

    foreach ($languages as $lang) {
        $langName = $lang['lang_name'];
        $langId = $lang['lang_id'];
        $response[$langName] = isset($updatedDictionary[$langId]) ? $updatedDictionary[$langId]['value'] : 'Not Set';
    }

    return response()->json($response);
}


}
