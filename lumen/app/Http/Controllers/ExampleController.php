<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function getCategories(Request $request)
    {
        try {
            $response = CategoryService::getCategories();
            return $response;
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
