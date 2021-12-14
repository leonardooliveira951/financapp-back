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
    public function createUser(Request $request)
    {
        $params = $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        try {
            $response = UserService::createUser($params);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'user' => 'email já cadastrado'
                ], 409
                );
            }
            return response()->json([
                'status' => true,
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

            $user = [
                'id' => $response['user']->id,
                'name' => $response['user']->name,
                'email' => $response['user']->email
            ];
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'message' => 'Usuário ou senha inválidos'
                ], 401
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Login realizado com sucesso!',
                'user' => $user,
                'token' => $response['token']
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

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|string',
            'new_password' => 'required|string'
        ]);
        try {
            $response = UserService::changePassword($request);

            if ($response == null){
                return response()->json([
                    'status' => false,
                    'message' => 'Senha atual inválida'
                ], 401
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Senha alterada com sucesso',
                'token' => $response['token']
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao alterar senha: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function getUser(Request $request)
    {
        try {
            $response = UserService::getUser($request);
            $user = [
                'id' => $response->id,
                'name' => $response->name,
                'email' => $response->email
            ];
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao buscar usuário: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}
