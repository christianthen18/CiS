<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        // Query with search filter if provided
        if ($search) {
            $querybuilder = Categories::where('name', 'LIKE', '%' . $search . '%')->get();
        } else {
            $querybuilder = Categories::all();
        }
        
        return view('categories.index', ['data' => $querybuilder, 'search' => $search]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Categories();
        $data->name = $request->get('cat_name');

        $data->save();
        // dd($data);

        return redirect()->route("categories.index")->with('status', "Horray, Your new category data is already inserted");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Categories::find($id);

        return view("categories.edit", compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Categories $categories)
    // {
    //     $updatedData = $categories;
    //     $updatedData->name = $request->name;

    //     $updatedData->save();


    //     return redirect()->route("categories.index")->with('status', "Horray, Your categories data is already updated");
    // }

    public function update(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $updatedData = Categories::find($id);

        // Pastikan data ditemukan sebelum melakukan update
        if ($updatedData) {
            $updatedData->name = $request->name;
            $updatedData->save();

            return redirect()->route("categories.index")->with('status', "Horray, Your categories data is already updated");
        } else {
            return redirect()->route("categories.index")->with('status', "Failed to update data, Category not found.");
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Categories $categories)
    // {
    //     try {
    //         //if no contraint error, then delete data. Redirect to index after it.
    //         $deletedData = $categories;
    //         $deletedData->delete();
    //         return redirect()->route('categories.index')->with('status', 'Horray ! Your data is successfully deleted !');
    //     } 
    //     catch (\PDOException $ex) {
    //         // Failed to delete data, then show exception message
    //         $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
    //         return redirect()->route('categories.index')->with('status', $msg);
    //     }
    // }

    public function destroy($id)
    {
        try {
            // Temukan data berdasarkan ID
            $deletedData = Categories::find($id);

            // Pastikan data ditemukan sebelum melakukan delete
            if ($deletedData) {
                $deletedData->delete();
                return redirect()->route('categories.index')->with('status', 'Horray! Your data is successfully deleted!');
            } else {
                return redirect()->route('categories.index')->with('status', 'Failed to delete data, Category not found.');
            }
        } catch (\PDOException $ex) {
            // Jika ada masalah pada penghapusan data
            $msg = "Failed to delete data! Make sure there is no related data before deleting it.";
            return redirect()->route('categories.index')->with('status', $msg);
        }
    }
}
