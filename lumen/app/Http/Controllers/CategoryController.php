<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
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
            $response = CategoryService::getCategories($request->user()['id']);
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
            'color_id' => 'required|integer',
        ]);

        try {
            $response = CategoryService::insertCategory($request);
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'category' => 'Nome de categoria já utilizado.'
                ], 409
                );
            }
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
            if ($response == null){
                return response()->json([
                    'status' => false,
                    'category' => 'Categoria não localizada'
                ], 404
                );
            }
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
        $data = $this->validate($request, [
            'name' => 'string',
            'color_id' => 'integer',
            'active' => 'string'
        ]);

        try {
            $response = CategoryService::updateCategory($data, $request->id);
            if (is_null($response)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Categoria não encontrada'
                ], 404
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Categoria alterada com sucesso',
                'category' => $response
            ],200
            );
        } catch (Exception $e) {
            $message = "Erro ao atualizar categoria: ". $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $message
            ],500
            );
        }


    }
}
