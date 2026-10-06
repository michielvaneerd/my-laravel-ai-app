<?php

use App\Ai\Agents\GeneralChatAgent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // $response = (new GeneralChatAgent)->prompt('How are you?', model: 'gemma4:12b-mlx');
    // dd($response);
    return view('welcome');
});
