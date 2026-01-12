<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
class WishlistController extends Controller
{
    public function add(Request $request)
    {
        try{
            $user = auth()->user();
            $request->validate([
                'product_id' => 'required|integer|exists:products,id'
            ]);

                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }     

            $wishlist = Wishlist::create([
                'product_id' => $request->input('product_id'),
                'user_id' => $user->id,
            ]);
            if($wishlist){
                return response()->json([
                    'status' => true,
                    'message' => 'Product added to wishlist',
                    'data' => $wishlist,
                ], 201);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Product addition to wishlist failed',
                ], 500);                
            }
         } catch(ValidationException $e){
               return response()->json(['error' => $e->errors()], 422);
         }
    }

    public function list()
    {
        try{
            $user = auth()->user();
            if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }
            $wishlists = Wishlist::where('user_id', $user->id)->join('products', 'products.id', '=', 'wishlists.product_id')->get();
            $data['wishlists'] = $wishlists;
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve wishlist',
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try{
            $user = auth()->user();
           $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:wishlists,product_id'
            ]);

                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }     

            $wishlist = Wishlist::where('product_id', $validatedData)->where('user_id', $user->id)->first();

            if(!$wishlist){
                return response()->json([
                    'status' => false,
                    'message' => 'Wishlist item not found',
                ], 404);
            }
            $wishlist->delete();

            return response()->json([
                    'status' => true,
                    'message' => 'Product removed from wishlist',
                    'data' => $wishlist,
                ], 201);
         } catch(ValidationException $e){
               return response()->json(['error' => $e->errors()], 422);
         }
    }
}
