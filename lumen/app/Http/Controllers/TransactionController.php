<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;


class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertTransaction(Request $request)
    {
        $this->validate($request, [
            'description' => 'required|string',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'installment' => 'required|integer',
            'category_id' => 'required|integer',
            'origin_account_id' => 'required|integer',
            'destiny_account_id' => 'required|integer'
        ]);
        try {
            $response = TransactionService::insertTransaction($request);
            return response()->json([
                'status' => true,
                'message' => 'Transação criada com sucesso',
                'account' => $response
            ], 201
            );
        } catch (Exception $e) {
            $message = "Erro ao inserir transação: ". $e->getMessage();
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
