<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TestController extends Controller
{

    public function handle(Request $request) 
    {
        file_put_contents(storage_path('logs/info.txt'), print_r($request->all(), true));
        return response()->json(['success' => true], 200);
    }

}