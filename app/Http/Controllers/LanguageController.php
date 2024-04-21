<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       //return Language::all();
       $languages = Language::select('lang_id', 'lang_name')->get();
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
    public function update(Request $request, string $id)
    {
        $language = Language::find($id);
        if(!$language)
        {
            return response()->json(['error' => 'Language not found'], 404);
        }
        $validatedData = $request->validate([
            'lang_id' => 'required|string',
            'lang_name' => 'required|string',
        ]);
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
