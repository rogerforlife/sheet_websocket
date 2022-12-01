<?php

use Illuminate\Support\Facades\Route;

// Route::group([
//     'middleware' => ['cas.auth', 'log.route'],
//     'namespace' => 'App\Http\Controllers\Api'], function () {
//     Route::apiResource('issue', 'IssueController');
// });

// Route::group([
//     'middleware' => ['cas.auth'],
//     'namespace' => 'App\Http\Controllers\Api'], function () {
//     Route::get('sheet_download', 'SheetDownloadController@index');
// });

// Route::group([
//     'middleware' => ['auth.apikey', 'log.openapi'],
//     'namespace' => 'App\Http\Controllers\OpenApi'], function () {
//     Route::get('fetch_data', 'FetchDataController@index');
// });

# demo
Route::group([
    'middleware' => ['log.route'],
    'namespace' => 'App\Http\Controllers\Api'], function () {
    Route::apiResource('issue', 'IssueController');
});

Route::group([
    'middleware' => [],
    'namespace' => 'App\Http\Controllers\Api'], function () {
    Route::get('sheet_download', 'SheetDownloadController@index');
});

Route::group([
    'middleware' => ['log.openapi'],
    'namespace' => 'App\Http\Controllers\OpenApi'], function () {
    Route::get('fetch_data', 'FetchDataController@index');
});
