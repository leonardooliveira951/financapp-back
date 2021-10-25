<?php


namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public static function insertCategory($request)
    {
        if(Category::where('name',$request['name'])->exists())
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
            'color_id' => $request->all()['color_id']
        ]);
        return true;
    }

    public static function getCategories()
    {
        return Category::all();
    }

}
