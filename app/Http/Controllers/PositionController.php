<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function getPositions()
    {
        $positions = Position::all();

        if ($positions->count()) {
            return response()->json([
                'success' => true,
                'positions' => $positions
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Positions not found'
            ],422);
        }
    }
}
