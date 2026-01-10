<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::with('category')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Assuming translatable question field or just 'question' column
                $q->where('question', 'like', "%{$search}%");
            });
        }

        $questions = $query->paginate(20)->withQueryString();
        $categories = Category::all();

        return view('admin.questions.index', compact('questions', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.questions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:text,boolean,rating', // Adjust based on actual types
            'category_id' => 'required|exists:categories,id',
        ]);

        Question::create($request->all());

        return redirect()->route('admin.questions.index')->with('swal_success', __('admin.messages.created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {
        $categories = Category::all();
        return view('admin.questions.edit', compact('question', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:text,boolean,rating',
            'category_id' => 'required|exists:categories,id',
        ]);

        $question->update($request->all());

        return redirect()->route('admin.questions.index')->with('swal_success', __('admin.messages.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->back()->with('swal_success', __('admin.messages.deleted_successfully'));
    }
}
