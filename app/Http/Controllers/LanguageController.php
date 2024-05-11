<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Language;
use App\Models\API\Dictionary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

       $languages = Language::select('id','lang_id', 'lang_name','tenant_id','updated_at')->get();
       return response()->json($languages);
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
        Log::info($langDict);

        $counter=1;$row=[];$rows=[];

        $row['Key']=$key;
        foreach($languages as $lang){
            $langName=$lang['lang_name'];
            $dataKey = $key . '-' . $lang['lang_id'];
            Log::info($dataKey);
            $dicVal = isset($langDict[$dataKey])? $langDict[$dataKey] : 'Not Set';
            $row[$langName]=$dicVal;             
        }
        $rows[]=$row;

       return response()->json($row);
    }

    public function getDict(Request $request)
    {

       $tenantId = $request->header('tenant_id',0); 
       $languages = Language::select('lang_id', 'lang_name')->get();
       $dictionary = Dictionary::select('key','language','value')->where([['tenant_id',$tenantId]])->get();
       $keys=[];$langDict=[];
       foreach($dictionary as $row){
            if(!in_array($row['key'],$keys)) $keys[]=$row['key'];
            $dataKey = $row['key'].'-'.$row['language'];
            $langDict[$dataKey] = $row['value'];
        }
        Log::info($langDict);

        $counter=1;$row=[];$rows=[];
        foreach($keys as $key){
            $row['id']=$counter;
            $row['Key']=$key;
            foreach($languages as $lang){
                $langName=$lang['lang_name'];
                $dataKey = $key . '-' . $lang['lang_id'];
                Log::info($dataKey);
                $dicVal = isset($langDict[$dataKey])? $langDict[$dataKey] : 'Not Set';
                $row[$langName]=$dicVal;             
            }
            $counter++;
            $rows[]=$row;
        }

       return response()->json($rows);
    }

    public function updateDict(Request $request)
    {

        $tenantId = $request->header('tenant_id',0); 
        $validatedData = $request->validate([
            'key' => 'required|string'
        ]);
        $key = $validatedData['key'];

        $dictionary = Dictionary::select('key','language','value')
        ->where([['tenant_id',$tenantId],['key',$key]])->get();
        $langDict=[];
        foreach($dictionary as $row){
             $lang = $row['language'];
             $langDict[$lang] = $row;
         }

        $languages = Language::select('lang_id', 'lang_name')->get();
        foreach($languages as $lang){
            $langName=$lang['lang_name'];
            if(isset($validatedData[$langName]) && !is_null($validatedData[$langName]) && $validatedData[$langName] !="Not Set"){
                $lang = $lang['lang_id'];
                if(isset($langDict[$lang]) && $langDict[$lang]['value'] != $validatedData[$langName]){ // update
                    $row = $langDict[$lang];
                    $row['value']=$validatedData[$langName];
                    $language->update($validatedData);
                
                }else if (!isset($langDict[$lang])){  // add new
                    $newRow =["key"=>$key,"language",$lang['lang_id'],"tenant_id",tenantId, "value"=>$validatedData[$langName],"updated_by"=>Auth::user()->id];
                    $language->post($validatedData);
                }
            }

            $dataKey = $lang['lang_id'];
            Log::info($dataKey);
            $dicVal = isset($langDict[$dataKey])? $langDict[$dataKey] : 'Not Set';
            $row[$langName]=$dicVal;             
        }

        $language->update($validatedData);
        return response()->json($language);
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
        $tenantId = $request->header('tenant_id',0); 
        $validatedData['tenant_id']=$tenantId;

        $language = Language::create($validatedData);
        return response()->json($language, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $language = Language::find($id);
        if(!$language)
        {
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

        $tenantId = $request->header('tenant_id',0); 
        $validatedData['tenant_id']=$tenantId;

        $language = Language::find( $validatedData['id']);
        if(!$language)
        {
            return response()->json(['error' => 'Language not found'], 404);
        }
        $language->update($validatedData);
        return response()->json($language);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $language = Language::find($id);
        if(!$language)
        {
            return response()->json(['error' => 'Language not found'], 404);
        }
        $language->delete();
        return response()->json(['message' => 'Language deleted Successfully']);
    }
}
