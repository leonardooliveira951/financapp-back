<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CategoryService;
use App\Services\UserService;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $params = $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        try {
            $response = UserService::createUser($params);
            return response()->json([
                'status' => true,
                'message' => 'Usuário criado com sucesso!',
                'user' => $response
            ], 201
            );
        } catch (Exception $e) {
            $message = "Erro ao criar usuário: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function login(Request $request)
    {
        $params = $this->validate($request, [
        'email' => 'required|string',
        'password' => 'required|string'
        ]);
        try {
            $response = UserService::loginUser($params);
            return response()->json([
                'status' => true,
                'message' => 'Login realizado com sucesso!',
                'token' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao realizar login: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function changeName(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string'
        ]);
        try {
            $response = UserService::changeName($request);
            return response()->json([
                'status' => true,
                'message' => 'Nome alterado com sucesso.'
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao alterar nome: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }


}
