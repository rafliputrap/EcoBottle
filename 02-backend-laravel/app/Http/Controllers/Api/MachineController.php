<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;

class MachineController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Machine::all(),
        ]);
    }
}
