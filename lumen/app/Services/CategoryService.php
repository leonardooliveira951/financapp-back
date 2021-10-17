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
        $category->color = $data['color'];
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

    public static function updateCategory($data)
    {
        if(!Category::where('id',$data['id'])->exists())
        {
            return null;
        }
        Category::where('id',$data['id'])->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'color' => $data['color'],
            'active' => $data['active']
        ]);
        return true;
    }

    public static function getCategories()
    {
        return Category::all();
    }

}
