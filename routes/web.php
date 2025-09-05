<?php

use App\Events\Tutorial;
use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Route::get("/bc",function(){
// broadcast(new Tutorial('hii'));
// return response()->json(['status' => 'Event broadcasted']);
// });

Route::get('/random', function () {
    return view('random');
});

Route::post('/send-random', function () {
    $randomNumber = request()->input('number');
    \Illuminate\Support\Facades\Log::info('Broadcasting random number: ' . $randomNumber);
    broadcast(new Tutorial('Random number: ' . $randomNumber));
    return response()->json(['status' => 'Random number broadcasted']);
});

Route::get('/graph', function () {
    return view('graph');
});


// Route::post('/update-price', function () {
//     $price = request()->input('price');
//     \Illuminate\Support\Facades\Log::info('Broadcasting price: ' . $price);
//     broadcast(new Tutorial('Price: ' . $price));
//     return response()->json(['status' => 'Price broadcasted']);
// });

Route::post('/update-price', [PriceController::class, 'updatePrice']);
Route::post('/send-random', [PriceController::class, 'sendRandom']);

Route::get('/prices', [PriceController::class, 'getPrices']);

Route::get('/chartcontroller',function (){
    return view ('charts');
});

Route::post('/update-chart-data', function (Request $request) {
    $type = $request->input('type'); // 'company' or 'country'
    $data = $request->input('data');
    
    if ($type === 'company' && isset($data['company'], $data['amount'])) {
        $message = json_encode(['type' => 'company', 'data' => [
            'company' => $data['company'],
            'amount' => (int)$data['amount']
        ]]);
        broadcast(new Tutorial($message))->toOthers();
        Log::info("Broadcasting company data: $message");
        return response()->json(['status' => 'Company data broadcasted']);
    } elseif ($type === 'country' && isset($data['country'], $data['investors'])) {
        $message = json_encode(['type' => 'country', 'data' => [
            'country' => $data['country'],
            'investors' => (int)$data['investors']
        ]]);
        broadcast(new Tutorial($message))->toOthers();
        Log::info("Broadcasting country data: $message");
        return response()->json(['status' => 'Country data broadcasted']);
    }
    
    return response()->json(['status' => 'Invalid data'], 400);
});