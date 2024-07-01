<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Services\ImageService;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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
        $categories = Category::when(request()->q, function($categories) {
            $categories = $categories->where('name', 'LIKE', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new CategoryResource(true, 'List Data Categories', $categories);
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
            'name'  => 'required|unique:categories',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $path = $this->imageService->uploadImage($request->file('image'), 'categories');

        $category = Category::create([
            'image' => $path,
            'name'  => $request->name,
            'slug'  => \Str::slug($request->name),
        ]);

        if ($category) {
            return new CategoryResource(true, 'Data Category Berhasil Disimpan!', $category);
        }

        return new CategoryResource(false, 'Data Cateogry Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::whereId($id)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data Category Tidak Ditemukan!',
            ], 404);
        }

        return new CategoryResource(true, 'Detail Data Category!', $category);
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
            $category = Category::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,jpg,png|max:2048',
                'name' => 'required|unique:categories,name,' . $category->id,
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
    
            $data = [
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
            ];
    
            // check image update
            if ($request->file('image')) {
    
                // remove image
                Storage::disk('local')->delete('public/categories/' . basename($category->image));
    
                // upload image
                $path = $this->imageService->uploadImage($request->file('image'), 'categories');
                $data['image'] = $path;
            }
    
            $category->update($data);
    
            if ($category) {
                return new CategoryResource(true, 'Data Category Berhasil Diupdate!', $category);
            }
    
            return new CategoryResource(false, 'Data Category Gagal Diupdate!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Category Tidak Ditemukan!',
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
            $category = Category::findOrFail($id);
    
            // Hapus gambar terkait
            Storage::disk('local')->delete('public/categories/' . basename($category->image));
    
            // Hapus kategori
            $category->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Data Category Berhasil Dihapus!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Category Tidak Ditemukan!',
            ], 404);
        }
    }
}
