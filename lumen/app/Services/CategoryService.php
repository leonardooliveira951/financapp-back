<?php


namespace App\Services;

use App\Models\Category;
use App\Models\Color;


class CategoryService
{
    const CREDIT_CARD_PAYMENT_CATEGORY = 1;

    public static function insertCategory($request)
    {
        if(Category::where([
            'name' => $request['name'],
            'type' => $request['type'],
            'user_id' => $request->user()['id']
        ])->exists())
        {
            return null;
        }
        $category = new Category;
        $category->user_id = $request->user()['id'];
        $category->name = $request['name'];
        $category->type = $request['type'];
        $category->color_id = $request['color_id'];
        $category->save();
        return $category;
    }

    public static function deleteCategory($id)
    {
        if(!Category::where('id',$id)->exists())
        {
            return null;
        }
        Category::where('id',$id)->update([
            'active' => false
        ]);
        return true;
    }

    public static function updateCategory($data, $category_id)
    {
        if(!Category::where('id',$category_id)->exists())
        {
            return null;
        }

        $category = Category::where('id', $category_id)->first();

        $category->update(
            $data
        );

        return $category;
    }

    public static function getCategories($user_id)
    {
        $response = [];
        $categories = Category::where('user_id', $user_id)->get();
        foreach ($categories as $category)
        {
            $response_array['id'] = $category['id'];
            $response_array['name'] = $category['name'];
            $response_array['type'] = $category['type'];
            $response_array['color'] = Color::where('id', $category['color_id'])->get()->first();
            $response_array['active'] = ($category['active'] == 1) ? (true) : (false);

            array_push($response, $response_array);
        }
        return $response;
    }

}
