<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request)
    {
        try {
            $response = CategoryService::getCategories();
            return response()->json([
                'status' => true,
                'message' => 'Categorias carregadas com sucesso',
                'categories' => $response
            ], 200
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

    public function insertCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'type' => 'required|string',
            'color' => 'required|string',
        ]);

        try {
            $response = CategoryService::insertCategory($request);
            return response()->json([
                'status' => true,
                'message' => 'Categoria criada com sucesso',
                'category' => $response
            ], 201
            );
        } catch (Exception $e) {
            $message = "Erro ao criar categoria: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function deleteCategory(Request $request)
    {

        try {
            $response = CategoryService::deleteCategory($request->id);
            return response()->json([
                'status' => true,
                'message' => 'Categoria deletada com sucesso',
                'category' => $response
            ], 200
            );
        } catch (Exception $e) {
            $message = "Erro ao deletar categoria: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }
    }

    public function updateCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'string',
            'type' => 'string',
            'color' => 'string',
            'active' => 'boolean'
        ]);

        $response = CategoryService::updateCategory($request);
        if (is_null($response)) {
            return response()->json([
                'status' => false,
                'message' => 'Categoria não encontrada.'
            ], 400
            );
        }
        return response()->json([
            'status' => true,
            'message' => 'Categoria alterada com sucesso!'
        ],200
        );

    }
}
