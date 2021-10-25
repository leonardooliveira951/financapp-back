<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Exception;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getColors(Request $request)
    {
        try {
            $response = Color::all('name', 'hex_code');

            return response()->json([
                'status' => true,
                'message' => 'Cores carregadas com sucesso',
                'colors' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao listar cores: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}
