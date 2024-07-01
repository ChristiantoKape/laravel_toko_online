<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('category')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->when(request()->q, function($products) {
                        $products->where('title', 'like', '%'.request()->q.'%');
                    })->latest()->paginate(10);

        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * Display the specified resource.
     * 
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $product = Product::with('category', 'reviews.customer')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('slug', $slug)->first();

        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data Product Tidak Ditemukan!',
            ], 404);
        }

        return new ProductResource(true, 'Detail Data Product!', $product);
    }
}
