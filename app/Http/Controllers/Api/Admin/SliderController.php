<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Slider;
use App\Http\Resources\SliderResource;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
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
        $sliders = Slider::latest()->paginate(5);

        return new SliderResource(true, 'Data retrieved successfully', $sliders);
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $path = $this->imageService->uploadImage($request->file('image'), 'sliders');

        $slider = Slider::create([
            'image' => $path,
            'link' => $request->link,
        ]);

        if ($slider) {
            return new SliderResource(true, 'Data added successfully', $slider);
        }

        return new SliderResource(false, 'Failed to add data', null);
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
            $slider = Slider::findOrFail($id);

            Storage::disk('local')->delete('public/sliders/' . basename($slider->image));

            $slider->delete();
            
            return new SliderResource(true, 'Data Slider Berhasil Dihapus!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Slider Tidak Ditemukan!',
            ], 404);
        }
    }
}
