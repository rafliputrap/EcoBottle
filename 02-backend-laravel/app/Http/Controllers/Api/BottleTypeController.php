<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BottleType;

class BottleTypeController extends Controller
{
    public function index()
    {
        $bottles = BottleType::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $bottles
        ]);
    }
}