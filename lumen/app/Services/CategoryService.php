<?php


namespace App\Services;

use App\Models\Category;
use App\Models\Color;

class CategoryService
{
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

    public static function updateCategory($request)
    {
        if(!Category::where('id',$request->id)->exists())
        {
            return null;
        }
        Category::where('id',$request->id)->update([
            'name' => $request->all()['name'],
            'type' => $request->all()['type'],
            'color_id' => $request->all()['color_id'],
            'active' => $request->all()['active']
        ]);
        return true;
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
