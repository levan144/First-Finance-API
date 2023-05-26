<?php

use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/nova');
});

Route::get('/test', function () {
    $client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://api.complyadvantage.com',
    ]);
    
    $response = $client->request('POST', '/kyb/v1/search?api_key=' . env('COMPLY_ADVANTAGE_API_KEY'), [
            'name' => 'Example Inc',
            'incorporation_jurisdiction_code' => 'GE'
        
    ]);
    
    $data = json_decode($response->getBody()->getContents(), true);
    
    print_r($data);
});