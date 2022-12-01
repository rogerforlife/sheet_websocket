<?php

namespace App\Http\Controllers\Api;

use App\Exports\IssuesExport;
use App\Http\Controllers\Controller;
use App\Models\Sheet;
use App\Trait\Api\OtherSystemApi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SheetDownloadController extends Controller
{
    use OtherSystemApi;

    public function index(Request $request)
    {
        $sheet_id = $request->get('sheet_id') ?: null;
        $actions = $request->get('actions') ?: null;
        $public_status = $request->get('public_status') ?: null;

        if (!$sheet_id) {
            return $this->failure('lost query parameter');
        }
        settype($sheet_id, 'integer');

        $proj_dict = $this->fetchAllProject();
        $sheet_obj = Sheet::select(['name', 'proj_id'])->find($sheet_id);
        $sheet_name = $sheet_obj->name;
        $proj_name = $proj_dict['data'][$sheet_obj->proj_id]['name'];

        return Excel::download(
            new IssuesExport($sheet_id, $actions, $public_status),
            $proj_name . ' - ' . $sheet_name . ' - ' . date('Y-m-d') . '.xlsx'
        );
    }
}
