<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Tenant::all();
        $tenants = Tenant::select('id', 'tenant_id', 'tenant_name', 'updated_at', 'updated_by')->get();
        return response()->json($tenants);
      
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tenant_name' => 'required|string'
        ]);

        $validatedData['tenant_id']=$request->header('tenant_id',0); 
        $validatedData['updated_by'] = Auth::user()->id;  

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
    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'required|integer',
            'tenant_name' => 'required|string',
        ]);

        $tenant = Tenant::find($validatedData['id']);
        if(!$tenant)
        {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $validatedData['tenant_id']=$tenant['tenant_id']; 
        $validatedData['updated_by'] = Auth::user()->id;  

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
