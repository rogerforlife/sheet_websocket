<?php

namespace App\Http\Controllers;

use App\Constant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    //用於生成 JSON 字串
    public function makeJson($status, $data, $msg)
    {
        //轉 JSON 時確保中文不會變成 Unicode
        return response()->json(['status' => $status, 'data' => $data, 'message' => $msg])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function success($result, $msg = Constant::SUCCESS_MESSAGE)
    {
        return response()->json(['status' => 1, 'result' => $result, 'message' => $msg])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function failure($msg = Constant::Failure_MESSAGE, $code = 200)
    {
        return response()->json(['status' => 0, 'result' => null, 'message' => $msg], $code)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function upload($file, $path, $originPath = '')
    {
        $uploadPath = $file->store($path, 'public');
        if (isset($originPath) && str_starts_with($originPath, '/storage')) {
            Storage::disk('public')->delete(str_replace('/storage', '', $originPath));
        }

        return "/storage/{$uploadPath}";
    }
}
