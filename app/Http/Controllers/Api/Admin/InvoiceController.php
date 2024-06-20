<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Invoice;
use App\Service\ImageService;
use App\Http\Resources\InvoiceResource;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->when(request()->q, function($invoices) {
            $invoices = $invoices->where('invoice', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new InvoiceResource(true, 'List Data Invoices', $invoices);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::with('orders.product', 'customer', 'city', 'province')->whereId($id)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data Invoice Tidak Ditemukan!',
            ], 404);
        }

        return new InvoiceResource(true, 'Detail Invoice!', $invoice);
    }
}
