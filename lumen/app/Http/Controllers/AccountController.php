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
            'balance' => 'required|numeric',
            'limit' => 'required|numeric',
            'invoice_closing_date' => 'required|integer',
            'invoice_due_date' => 'required|integer',
            'color_id' => 'required|integer',
        ]);
        try {
            $response = AccountService::insertAccount($request);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'account' => 'Nome de conta já utilizado.'
                ], 409
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Conta criada com sucesso.',
                'account' => $response
            ], 201
            );
        } catch (Exception $e) {
            $message = "Erro ao inserir conta: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function updateAccount(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'type' => 'required|string',
            'invoice_closing_date' => 'required|integer',
            'invoice_due_date' => 'required|integer',
            'color_id' => 'required|integer'
        ]);
        try {
            $response = AccountService::updateAccount($request);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'account' => 'Conta não localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Conta atualizada com sucesso',
                'category' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao atualizar conta: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $response = AccountService::deleteAccount($request->id);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'account' => 'Conta não localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Conta deletada com sucesso',
                'category' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao deletar conta: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}
