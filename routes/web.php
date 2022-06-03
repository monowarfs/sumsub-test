<?php

use App\ThirdParty\Sumsub\AppTokenGuzzlePhpExample;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/get-a-sumsub-access-token', function (){
    $externalUserId = Str::random();
    $levelName = 'basic-kyc-level';
    $testObject = new AppTokenGuzzlePhpExample();
    $accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
    Log::info($accessTokenStr);
    return $accessTokenStr;
});

Route::get('/', function () {
    return view('welcome');
});
