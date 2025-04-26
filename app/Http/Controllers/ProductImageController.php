<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Product_Image;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $querybuilder = Product_Image::all(); // ini untuk pake model
        return view('product.index', ['data' => $querybuilder]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('image.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $file = $request->file("file_photo");
        $folder = 'images';
        $filename = time() . "_" . $file->getClientOriginalName();
        $file->move($folder, $filename);
        $product = Product::find($request->product_image_id);
        $product->image = $filename;
        $product->save();
        return redirect()->route('product.index')->with('status', 'Image uploaded');
    }

    public function simpanPhoto(Request $request)
    {
        // Validate the file
        $request->validate([
            'file_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the uploaded file
        $file = $request->file("file_photo");

        // Define the folder where the image will be stored
        $folder = 'images';

        // Generate a filename
        $filename = time() . "_" . $file->getClientOriginalName();

        // Move the uploaded file to the designated folder
        $file->move(public_path($folder), $filename);

        // Create a new image record
        $productImage = new Product_Image();
        $productImage->name = $filename;
        $productImage->save(); // Save the image first to get the ID

        // Now associate the image with the product
        $product = Product::find($request->product_id); // Ensure you are using the correct product ID
        if ($product) {
            $product->product_image_id = $productImage->id; // Set the product_image_id
            $product->save(); // Save the product to update the product_image_id
        }

        // Redirect back to the create page with the uploaded image name
        return redirect()->route('product.index')->with('status', 'Image uploaded successfully')->with('uploaded_image', $filename);
    }


    public function uploadPhoto($id)
    {
        // Cari produk berdasarkan ID
        $product = Product::find($id);

        // Validasi apakah produk ditemukan
        if (!$product) {
            return redirect()->route('product.index')->with('error', 'Product not found.');
        }

        // Tampilkan halaman form unggah foto dengan data produk
        return view('image.formUploadPhoto', compact('product'));
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
        $data = Product_Image::find($id);
        return view("image.edit", compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product_Image $image)
    {
        $updatedData = $$image;
        $updatedData->name = $request->name;

        $updatedData->save();


        return redirect()->route("image.index")->with('status', "Horray, Your image is already updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product_Image $image)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = $image;
            $deletedData->delete();
            return redirect()->route('product.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('product.index')->with('status', $msg);
        }
    }
}
