<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Employe::with('user')->get();
        return view('employee.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get users who don't have an employee record yet
        $users = User::whereDoesntHave('employees')->get();
        return view('employee.create', ['users' => $users]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Employe();
        $data->name= $request->get('emp_name');
        $data->phone_number= $request->get('emp_phone');
        $data->address= $request->get('emp_address');
        $data->users_id= $request->get(key: 'user_id_emp');

        $data->save();
        // dd($data);

        return redirect()->route("employe.index")->with('status',"Horray, Your new category data is already inserted");
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
        $data = Employe::find($id);
        return view("employee.edit",compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employe $employe)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'status_active' => 'required|boolean',
        ]);
       
        // Update employee data
        $employe->name = $request->name;
        $employe->phone_number = $request->phone_number;
        $employe->address = $request->address;
        $employe->status_active = $request->status_active;
        $employe->save();
       
        // Update associated user's status_active
        if ($employe->users_id) {
            $user = User::find($employe->users_id);
            if ($user) {
                $user->status_active = $request->status_active;
                $user->save();
            }
        }
       
        return redirect()->route("employe.index")->with('status', "Employee data has been successfully updated.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employe $employe)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = $employe;
            $deletedData->delete();
            return redirect()->route('employe.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } 
        catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('employe.index')->with('status', $msg);
        }
    }
}
