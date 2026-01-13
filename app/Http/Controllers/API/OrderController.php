<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {   
        $user = auth()->user(); 

                 if(($user->role == 'admin') || ($user->role == 'seller')){
                    return response()->json([
                        'status' => false,
                        'message' => 'This action is not allowed for admin or seller users',
                    ], 403);
                 }

                $request->validate([
                    'product_items' => 'required|array|min:1',
                    'product_items.*.product_id' => 'required|integer|min:1',
                    'product_items.*.quantity' => 'required|integer|min:1',
                    'payment_method' => 'required|string',
                    'shipping_address' => 'required|string',
                    'billing_address' => 'required|string',
                    'notes' => 'nullable|string',
                ]);
                 
                $productIds = $request->input('product_items');
                $subtotal = 0;
                foreach($productIds as $productId){
                    $product = Product::find($productId['product_id']);
                    $quantity = $productId['quantity'] ?? 1;
                    if($product){
                        $subtotal += $product->price * $quantity;
                    }
                }
                $tax_amount = $request->input('tax_amount')/100 * $subtotal;
                $shipping_charge = $request->input('shipping_charge', 0);
                $discount_amount = $request->input('discount_amount', 0);
                $total_amount = $subtotal + $tax_amount + $shipping_charge - $discount_amount;
                $user = auth()->user();
                $orderData = [
                    'user_id' => $user->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax_amount,
                    'shipping_charge' => $shipping_charge,
                    'discount_amount' => $discount_amount,
                    'total_amount' => $total_amount,
                    'payment_method' => $request->input('payment_method'),                    
                    'payment_status' => $request->input('payment_status', 'pending'),
                    'order_status' => $request->input('order_status', 'pending'),
                    'shipping_address' => $request->input('shipping_address'),
                    'billing_address' => $request->input('billing_address'),
                    'notes' => $request->input('notes', ''),

                ];
                // $order = Order::create($orderData);

                DB::beginTransaction();

                try {
                    $order = Order::create($orderData);

                    foreach ($productIds as $productId) {
                        $product = Product::findOrFail($productId['product_id']);
                        $quantity = $productId['quantity'] ?? 1;
                        $product_id = $productId['product_id'];
                        $total = $product->price * $quantity;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_name' => $product->name,
                            'product_id' => $product_id,
                            'quantity' => $quantity,
                            'price' => $product->price,
                            'total' => $total,
                        ]);

                        $product->decrement('stock', $quantity);
                        if ($product->stock < $quantity) {
                            throw new Exception("Insufficient stock");
                        }
                    }

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed successfully',
                    ], 201);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return response()->json([
                        'status' => false,
                        'message' => 'Order failed',
                        'error' => $e->getMessage(),   
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                    ], 500);
                }


    }

    public function myOrders()
    {
      try{
            $user = auth()->user();

            if($user->role == 'admin' || $user->role == 'seller'){
                return response()->json([
                    'status' => false,
                    'message' => 'This action is not allowed for admin or seller users',
                ], 403);
            }
            $orders = Order::where('user_id', $user->id)->get();
            $orderId = $orders->pluck('id');
            $orderItems = OrderItem::whereIn('order_id', $orderId)->get();
            $data = [];
            // $data['orders'] = $orders;
            // $data['order_items'] = $orderItems;
            return response()->json([
                'status' => true,
                'orders' => $orders,
                'orderItems' => $orderItems,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Order failed',
                'id' => $user->id,
                'error' => $e->getMessage(),   
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}
