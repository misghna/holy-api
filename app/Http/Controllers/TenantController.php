<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Tenant::all();
      
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tenant_id' => 'required|integer',
            'tenant_name' => 'required|string',
            'updated_at' => 'required|integer',
            'updated_by' => 'required|string'
        ]);
        $tenant = Tenant::create($validatedData);
        return response()->json($tenant, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tenant = Tenant::find($id);
        if(!$tenant)
        {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        return response()->json($tenant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tenant = Tenant::find($id);
        if(!$tenant)
        {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        $validatedData = $request->validate([
            'tenant_id' => 'required|integer',
            'tenant_name' => 'required|string',
            'updated_at' => 'required|integer',
            'updated_by' => 'required|string'
        ]);
        $tenant->update($validatedData);
        return response()->json($tenant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::find($id);
        if(!$tenant)
        {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        $tenant->delete();
        return response()->json(['message' => 'Tenant deleted Successfully']);
    }
}
