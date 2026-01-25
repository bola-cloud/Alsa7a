<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

use App\Models\ParentCategory;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $parentCategories = ParentCategory::with([
            'categories' => function ($q) {
                $q->orderBy('name_en'); // or just orderBy('name') if using trait
            }
        ])->get();

        return response()->json(['parent_categories' => $parentCategories]);
    }
}
