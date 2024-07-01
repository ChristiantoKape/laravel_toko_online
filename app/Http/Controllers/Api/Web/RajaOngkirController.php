<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Province;
use App\Models\City;
use App\Http\Resources\RajaOngkirResource;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * get Provinces
     * 
     * @return void
     */
    public function getProvinces()
    {
        $provinces = Province::all();

        return new RajaOngkirResource(true, 'Data provinsi berhasil diambil', $provinces);
    }

    /**
     * get Cities
     * 
     * @param mixed $request
     * @return void
     */
    public function getCities(Request $request)
    {
        $province = Province::where('id', $request->province_id)->first();
        $cities = City::where('province_id', $request->province_id)->get();

        return new RajaOngkirResource(true, 'List Data City By Province : '.$province->name.'', $cities);
    }

    /**
     * checkOngkir
     * 
     * @param mixed $request
     * @return void
     */
    public function checkOngkir(Request $request)
    {
        $response = Http::withHeaders([
            //api key rajaongkir
            'key'          => config('services.rajaongkir.key')
        ])->post('https://api.rajaongkir.com/starter/cost', [

            //send data
            'origin'      => 113, // ID kota Demak
            'destination' => $request->destination,
            'weight'      => $request->weight,
            'courier'     => $request->courier    
        ]);

        //return with Api Resource
        return new RajaOngkirResource(true, 'List Data Biaya Ongkos Kirim : '.$request->courier.'', $response['rajaongkir']['results'][0]['costs']);
    }
}
