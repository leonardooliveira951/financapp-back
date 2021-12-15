<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummaryByDate(Request $request)
    {
        try {
            $response = PaymentService::getSummaryByDate($request);
            if ($response == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nenhum pagamento localizado.'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Resumo de pagamentos listado com sucesso',
                'payment_summary' => $response,
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao listar resumo de pagamentos: " . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ], 500
            );
        }
    }

}
