<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Services\UserService;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
