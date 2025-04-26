<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $querybuilder = Companies::all(); // ini untuk pake model
        return view('companies.index', ['data' => $querybuilder]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'comp_name' => 'required|string|max:255',
            'comp_phone' => 'required|string|max:20',
            'comp_address' => 'required|string',
            'comp_email' => 'required|email',
            'comp_logo' => 'nullable|string',
        ]);
        
        // Check if any company already exists
        $existingCompany = Companies::count();
        if($existingCompany > 0) {
            return redirect()->route('companies.index')->with('status', 'A company is already registered. You cannot add another one.');
        }
        
        $data = new Companies();
        $data->name = $request->get('comp_name');
        $data->phone_number = $request->get('comp_phone');
        $data->address = $request->get('comp_address');
        $data->email = $request->get('comp_email');
        $data->logo = $request->get('comp_logo');
        
        $data->save();

        return redirect()->route('companies.index')->with('status', 'Company information has been successfully registered');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $company = Companies::findOrFail($id);
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'comp_name' => 'required|string|max:255',
            'comp_phone' => 'required|string|max:20',
            'comp_address' => 'required|string',
            'comp_email' => 'required|email',
            'comp_logo' => 'nullable|string',
        ]);
        
        $company = Companies::findOrFail($id);
        $company->name = $request->get('comp_name');
        $company->phone_number = $request->get('comp_phone');
        $company->address = $request->get('comp_address');
        $company->email = $request->get('comp_email');
        $company->logo = $request->get('comp_logo');
        
        $company->save();

        return redirect()->route('companies.index')->with('status', 'Company information has been successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
