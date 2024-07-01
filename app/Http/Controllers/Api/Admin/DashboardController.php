<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Displayt a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pending = Invoice::where('status', 'pending')->count();
        $success = Invoice::where('status', 'success')->count();
        $expired = Invoice::where('status', 'expired')->count();
        $failed = Invoice::where('status', 'failed')->count();

        // year and month
        $year = date('Y');

        // chart
        $transactions = DB::table('invoices')
            ->select(
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('MONTHNAME(created_at) as month_name')
            )
            ->whereYear('created_at', '=', $year)
            ->where('status', 'success')
            ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();
        
        $month_name = [];
        $grand_total = [];

        if(count($transactions)) {
            foreach ($transactions as $result) {
                $month_name[]    = $result->month_name;
                $grand_total[]   = (int)$result->grand_total;
            }
        } else {
            $month_name[]   = "";
            $grand_total[]  = "";
        } 

        return response()->json([
            'success' => true,
            'message' => 'Statistik Data',  
            'data'    => [
                'count' => [
                    'pending'   => $pending,
                    'success'   => $success,
                    'expired'   => $expired,
                    'failed'    => $failed
                ],
                'chart' => [
                    'month_name'    => $month_name,
                    'grand_total'   => $grand_total
                ]
            ]  
        ], 200);
    }
}
