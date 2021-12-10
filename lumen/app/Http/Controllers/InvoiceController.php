<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use Exception;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoice(Request $request)
    {
        try {
            $response = InvoiceService::getInvoice($request);
            return response()->json([
                'status' => true,
                'invoice' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Fatura não localizada ou não existente: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

}
