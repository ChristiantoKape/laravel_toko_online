<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cart;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::with('product')
                    ->where('customer_id', auth()->guard('api_customer')->user()->id)
                    ->latest()
                    ->get();

        return new CartResource(true, 'List Data Carts : '.auth()->guard('api_customer')->user()->name.'', $carts);
    }

    /**
     * Store a newly created resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $item = Cart::where('product_id', $request->product_id)->where('customer_id', auth()->guard('api_customer')->user()->id);

        if ($item->count()) {
            $item->increment('qty');
            $item = $item->first();

            $price = $request->price * $item->qty;
            $weight = $request->weight * $item->qty;

            $item->update([
                'price'     => $price,
                'weight'    => $weight
            ]);

        } else {
            $item = Cart::create([
                'product_id'    => $request->product_id,
                'customer_id'   => auth()->guard('api_customer')->user()->id,
                'qty'           => $request->qty,
                'price'         => $request->price,
                'weight'        => $request->weight
            ]);
        }
     
        return new CartResource(true, 'Success Add To Cart', $item);
    }

    /**
     * getCartPrice
     * 
     * @return void
     */
    public function getCartPrice()
    {
        $totalPrice = Cart::with('product')
                        ->where('customer_id', auth()->guard('api_customer')->user()->id)
                        ->sum('price');
        
        //return with Api Resource
        return new CartResource(true, 'Total Cart Price', $totalPrice);
    }

    /**
     * getCartWeight
     * 
     * @return void
     */
    public function getCartWeight()
    {
        $totalWeight = Cart::with('product')
                        ->where('customer_id', auth()->guard('api_customer')->user()->id)
                        ->sum('weight');

        //return with Api Resource
        return new CartResource(true, 'Total Cart Weight', $totalWeight);
    }

    /**
     * removeCart
     * 
     * @param mixed $request
     * @return void
     */
    public function removeCart(Request $request)
    {
        $cart = Cart::with('product')
                    ->whereId($request->cart_id)
                    ->first();
        
        if(!$cart){
            return response()->json([
                'success' => false,
                'message' => 'Cart Not Found!',
            ], 404);
        }

        $cart->delete();

        return new CartResource(true, 'Success Remove Cart', $cart);
    }
}
