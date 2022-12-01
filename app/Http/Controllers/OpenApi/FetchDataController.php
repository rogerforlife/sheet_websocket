<?php

namespace App\Http\Controllers\OpenApi;

use App\Http\Controllers\Controller;
use App\Models\Sheet;
use Carbon\Carbon;

class FetchDataController extends Controller
{

    public function index()
    {
        $next_month = Carbon::now()->startOfMonth()->addMonth(+1);
        $today_month = Carbon::now()->startOfMonth();

        $sheet_qs = Sheet::
            whereBetween('updated_at', [$today_month, $next_month])
            ->get()
            ->groupby('proj_id');

        return $this->success($sheet_qs);
    }
}
