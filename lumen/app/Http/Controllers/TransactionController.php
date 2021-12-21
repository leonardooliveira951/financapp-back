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
            'description' => 'string',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'installments' => 'integer',
            'category_id' => 'required|integer',
            'origin_account_id' => 'required|integer',
            'destiny_account_id' => 'integer|nullable'
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
            $message = "Erro ao inserir transação: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

    public function updateTransaction(Request $request)
    {
        $data = $this->validate($request, [
            'payment_id' => 'required|integer',
            'description' => 'string',
            'category_id' => 'integer',
            'amount' => 'numeric',
            'date' => 'date'
        ]);

        try {
            $response = TransactionService::updateTransaction($data, $request->id);
            if ($response == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Conta não localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Transação atualizada com sucesso',
                'transaction' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao atualizar transação: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

    public function deleteTransaction(Request $request)
    {
        try {
            $response = TransactionService::deleteTransaction($request->id);
            if ($response == null) {
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
            $message = "Erro ao deletar transação: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

    public function getTransactionByDate(Request $request)
    {
        try {
            $response = TransactionService::getTransactionByDate($request);
            if ($response == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nenhuma transação localizada.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Transações listadas com sucesso',
                'transactions' => $response,
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao listar transações: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

    public function getDashboard(Request $request)
    {
        try {
            $response = TransactionService::getDashboard($request->period, $request->user()['id']);
            if ($response == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Dados não localizados'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Dashboard carregado com sucesso',
                'dashboard' => $response,
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao carregar dashboard: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

}
