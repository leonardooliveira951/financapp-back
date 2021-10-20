<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Exception;
use Illuminate\Http\Request;


class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertAccount(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'type' => 'required|string',
            'color_id' => 'required|integer',
        ]);
        try {
            $response = AccountService::insertAccount($request);
            return response()->json([
                'status' => true,
                'message' => 'Conta criada com sucesso.',
                'account' => $response
            ], 201
            );
        } catch (Exception $e) {
            $message = "Erro ao buscar categorias: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}
