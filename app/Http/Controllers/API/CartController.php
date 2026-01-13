<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Cart;

class CartController extends Controller
{
    public function list(Request $request)
    {
        $user = auth()->user();
        if($user->role === 'admin' || $user->role === 'seller'){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }
        // $carts = Cart::where('user_id', $user->id)->get();   
        //    $carts = Cart::with('product')
        // ->where('user_id', $user->id)
        // ->get();      
       $carts =  Cart::join('products', 'carts.product_id', '=', 'products.id')
                ->where('carts.user_id', $user->id)
                ->select(
                    'carts.id',
                    'carts.product_id',
                    'carts.quantity',
                    'products.name',
                    'products.price',
                    'products.image'
                )
                ->get();
        return response()->json([
            'status' => true,
            'message' => 'Cart items fetched successfully',
            'carts' => $carts,
        ]);
    }

    public function add(Request $request)
    {
        try {
                $validated = $request->validate([
                    'product_id' => 'required|integer|exists:products,id',
                    'quantity' => 'required|integer|min:1',
                ]);
                $user = auth()->user();
                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }
                $user_id = $user->id;
                $validated['user_id'] = $user_id;
                Cart::create($validated);
                return response()->json([
                    'status' => true,
                    'message' => 'Item has been added to cart',
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

    public function edit(Request $request)
    {
        $cart_id = $request->cart_id;
        try {
                $validated = $request->validate([
                    'quantity' => 'required|string',
                ]);
                $user = auth()->user();
                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }
                 $cart = Cart::find($cart_id);
                 $user_id = $user->id;
                 
                 if($cart->user_id != $user_id){
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not authorized to delete this item',
                    ], 403);
                 }

                Cart::where('id', $cart_id)->update($validated);
                return response()->json([
                    'status' => true,
                    'message' => 'Item has been updated in cart',
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

    public function remove(Request $request)
    {
        try {
                $user = auth()->user();
           $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id'
            ]);                
                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }
                
                 $cart = Cart::where('product_id', $validatedData)->where('user_id', $user->id)->first();

                 if($cart->user_id != $user->id){
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not authorized to delete this item',
                    ], 403);
                 }
                // Cart::where('id', $cart_id)->delete();

            if(!$cart){
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found',
                ], 404);
            }
            $cart->delete();                
                return response()->json([
                    'status' => true,
                    'message' => 'Item has been removed from cart',
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


