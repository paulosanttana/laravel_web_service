<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    // Propriedade category usando no construct
    private $category;
    
    // Criado construct para injetar Category automaticamente
    public function __construct(Category $category)
    {   //propriedade $category recebe objeto Category
        $this->category = $category;
    }

    public function index(Category $category, Request $request)
    {
        $categories = $this->category->getResults($request->name);

        return response()->json($categories, 200);
    }

    public function store(Request $request)
    {
        $category = $this->category->create($request->all());

        return response()->json($category, 201);
    }
}
