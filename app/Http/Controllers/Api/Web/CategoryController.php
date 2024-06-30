<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return new CategoryResource(true, 'List Data Categories', $categories);
    }

    /**
     * Display the specified resource.
     * 
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $category = Category::with('products.category')
            ->with('products', function ($query) {
                $query->withCount('reviews');
                $query->withAvg('reviews', 'rating');
            })->where('slug', $slug)->first();

        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data Category Tidak DItemukan!',
            ], 404);
        }

        return new CategoryResource(true, 'Data Product By Category : '.$category->name.'', $category);
    }
}
