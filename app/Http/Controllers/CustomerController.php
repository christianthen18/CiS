<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $querybuilder = Customer::all(); // ini untuk pake model
        return view('customer.index', ['data' => $querybuilder]);
    }

    /**
     *
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Customer();
        $data->name = $request->get('cust_name');
        $data->address = $request->get('cust_address');
        $data->phone_number = $request->get('cust_phone');
        $data->email = $request->get('cust_email');
        $data->save();
        // dd($data);

        return redirect()->route("customer.index")->with('status', "Horray, Your new category data is already inserted");
    }


    public function storeNew(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required',
                'address' => 'required',
                'phone_number' => 'required',
                'email' => 'required'
            ]
        );


        $validated['status_active'] = true;


        Customer::create($validated);

        return redirect()->back()->with('success', 'Berhasil menambahkan customer baru!');
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
        $data = Customer::find($id);
        return view("customer.edit", compact('data'));
    }

    public function getEditForm(Request $request)
    {
        $id = $request->id;
        $data = Customer::find($id);
        // $data = Customer::find($id);

        return response()->json(
            array(
                'status' => 'oke',
                'msg' => view('customer.getEditForm', compact('data'))->render()
            ),
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $updatedData = $customer;
        $updatedData->name = $request->name;
        $updatedData->address = $request->address;
        $updatedData->phone_number = $request->phone_number;
        $updatedData->email = $request->email;
        $updatedData->status_active = $request->status_active;


        $updatedData->save();


        return redirect()->route("customer.index")->with('status', "Horray, Your customer data is already updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = $customer;
            $deletedData->delete();
            return redirect()->route('customer.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('customer.index')->with('status', $msg);
        }
    }
}
