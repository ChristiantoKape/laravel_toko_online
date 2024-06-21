<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Services\ImageService;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('category')->when(request()->q, function($products) {
            $products = $products->where('title', 'LIKE', '%'. request()->q . '%');
        })->latest()->paginate(5);

        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|unique:products',
            'category_id' => 'required',
            'description' => 'required',
            'weight' => 'required',
            'price' => 'required',
            'stock' => 'required',
            'discount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $path = $this->imageService->uploadImage($request->file('image'), 'products');

        $product = Product::create([
            'image'         => $path,
            'title'         => $request->title,
            'slug'          => Str::slug($request->title, '-'),
            'category_id'   => $request->category_id,
            'user_id'       => auth()->guard('api_admin')->user()->id,
            'description'   => $request->description,
            'weight'        => $request->weight,
            'price'         => $request->price,
            'stock'         => $request->stock,
            'discount'      => $request->discount
        ]);

        if ($product) {
            return new ProductResource(true, 'Data Product Berhasil Disimpan!', $product);
        }

        return new ProductResource(false, 'Data Product Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::whereId($id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data Product Tidak Ditemukan!',
            ], 404);
        }

        return new ProductResource(true, 'Detail Data Product!', $product);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,jpg,png|max:2048',
                'title' => 'required|unique:products,title,' . $product->id,
                'category_id' => 'required',
                'description' => 'required',
                'weight' => 'required',
                'price' => 'required',
                'stock' => 'required',
                'discount' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
    
            $data = [
                'title'         => $request->title,
                'slug'          => Str::slug($request->title, '-'),
                'category_id'   => $request->category_id,
                'user_id'       => auth()->guard('api_admin')->user()->id,
                'description'   => $request->description,
                'weight'        => $request->weight,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'discount'      => $request->discount
            ];
    
            // check image update
            if ($request->file('image')) {
    
                // remove image
                Storage::disk('local')->delete('public/products/' . basename($product->image));
    
                // upload image
                $path = $this->imageService->uploadImage($request->file('image'), 'products');
                $data['image'] = $path;
            }
    
            $product->update($data);
            
            if ($product) {
                return new ProductResource(true, 'Data Product Berhasil Diupdate!', $product);
            }

            return new ProductResource(false, 'Data Product Gagal Diupdate!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Product Tidak Ditemukan!',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Temukan kategori berdasarkan ID yang diterima sebagai parameter
            $product = Product::findOrFail($id);

            // Hapus gambar
            Storage::disk('local')->delete('public/products/' . basename($product->image));

            // Hapus product
            $product->delete();

            return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Product Tidak Ditemukan!',
            ], 404);
        }
    }
}
