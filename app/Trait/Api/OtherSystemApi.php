<?php

namespace App\Trait\Api;

use App\Constant;
use Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

trait OtherSystemApi
{
    public function fetchAllProject()
    {
        $result = [
            'success' => true,
            'data' => null,
            'message' => '',
        ];

        if (Cache::has('all_projects')) {
            $result['data'] = Cache::get('all_projects');
        } else {
            $limit = 100;
            $page = 0;
            $projects = [];
            $url = config('values.OtherSystem_url') . 'api/projects';
            try {
                $response = Http::withHeaders([
                    'X-Authorization' => config('values.OtherSystem_token'),
                ])->get($url, [
                    'deleted_at' => 'NULL',
                ])->json();
                $projects = array_merge($projects, $response);

                $projs_dict = [];
                foreach ($projects as $proj_obj) {
                    $projs_dict[$proj_obj['id']] = $proj_obj;
                }
            } catch (ConnectionException $e) {
                $result['success'] = false;
                $result['message'] = Constant::Failure_MESSAGE;
            }

            $result['data'] = $projs_dict;
            //快取 5 分鐘
            Cache::put('all_projects', $projs_dict, 60 * 5);
        }

        return $result;
    }
}
