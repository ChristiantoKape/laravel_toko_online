<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * 
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'order_id' => 'required',
            'product_id' => 'required',
            'rating' => 'required|min:1|max:5',
            'review' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), 422);
        }

        // Check review already
        $check_review = Review::where('order_id', $request->order_id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($check_review) {
            return response()->json($check_review, 409);
        }

        $review = Review::create([
            'rating'        => $request->rating,
            'review'        => $request->review,
            'product_id'    => $request->product_id,
            'order_id'      => $request->order_id,
            'customer_id'   => auth()->guard('api_customer')->user()->id
        ]);

        if ($review) {
            return new ReviewResource(true, 'Data Review Berhasil Disimpan!', $review);
        }

        return new ReviewResource(false, 'Data Review Gagal Disimpan!', null);
    }
}
