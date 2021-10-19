<?php


namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public static function insertCategory($data)
    {
        if(Category::where('name',$data['name'])->exists())
        {
            return null;
        }
        $category = new Category;
        $category->user_id = $data->user()['id'];
        $category->name = $data['name'];
        $category->type = $data['type'];
        $category->color_id = $data['color'];
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
            'color' => $request->all()['color'],
            'active' => $request->all()['active']
        ]);
        return true;
    }

    public static function getCategories()
    {
        return Category::all();
    }

}
