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
            'destiny_account_id' => 'integer'
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

    public function updateTransaction(Request $request)
    {
        $this->validate($request, [
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);
        try {
            $response = TransactionService::updateTransaction($request);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'message' => 'Conta não localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Transação atualizada com sucesso'
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao atualizar transação: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function deleteTransaction(Request $request)
    {
        try {
            $response = TransactionService::deleteTransaction($request->id);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'message' => 'Transação não localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Transação deletada com sucesso',
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao deletar transação: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}