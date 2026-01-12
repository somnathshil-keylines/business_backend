<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{

    public function products_list(){
        $products = Product::all();
        
        return response()->json([
             "status" => true,
             "products" => $products
        ], 200);
    }

        public function products_show($id){
        $product = Product::find($id);
        return response()->json([
             "status" => true,
             "product" => $product
        ], 200);
    }

    public function add(Request $request)
    {		
          $user = auth()->user();	
          
          if($user->role != 'seller'){
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to access this route'
                ], 422);
          }

        try {
            $validatedData = $request->validate([
                'category_id' => 'required|string',
                'sub_category_id' => 'required|string',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|string',
                'stock' => 'required|string',
                'unit' => 'required|string',
                'status' => 'nullable|string'
            ]);

            $request->validate([
                  'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            ]);
            if($request->hasFile('image')){
                    $imageName = time().'.'.$request->image->extension();  
                    $request->image->move(public_path('uploads/products'), $imageName);
                    $validatedData['image'] = 'uploads/products/'.$imageName;
            }

            $validatedData['seller_id'] = $user->id;
            Product::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Product listed successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
}
