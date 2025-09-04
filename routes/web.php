<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/bc",function(){
broadcast(new App\Events\Tutorial());
});